<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once 'db_fns.php';
require_once 'project_functions.php';
require_once 'constants.php';
require_once  'user_view.php';
$dbconn = database_connect();
if ($use_sample_bonus_columns)
{
  $project_report_header = "Sample,Sample Description,Status,Barcode," .
   "Barcode Index,Species,Type,Batch Group," .
   "Concentration,Volume,RIN,Comments,Run List";
} else {
  $project_report_header = "Sample,Sample Description,Status,Barcode," .
   "Barcode Index,Species,Type,Batch Group,Comments,Run List";
}  // if ($use_sample_bonus_columns)
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
function export_project_info (
 $dbconn, $project_uid, $project_view, $primary_investigator_view,
 $contact_view, $sample_view, $run_view, $run_lane_view,
 $run_lane_sample_view, $export_comment_symbol)
{
  $row_start = '"' . $export_comment_symbol;
  // Get the run information.
  $result = pg_query ($dbconn, "
   SELECT project_name,
          $project_view.primary_investigator_uid,
          $primary_investigator_view.name as pi_name,
          ref_run_type.run_type,
          $project_view.status,
          creation_date,
          project_description,
          analysis_notes,
          admin_comments,
          COALESCE (contact_uid, -1) AS contact_uid
     FROM $project_view,
          $primary_investigator_view,
          ref_run_type
    WHERE project_uid = $project_uid AND
          $project_view.primary_investigator_uid =
           $primary_investigator_view.primary_investigator_uid AND
          $project_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    return pg_last_error ($dbconn);
  } else {
    // Build the export project_info string from the query result.
    $row = pg_fetch_assoc ($result);
    // Get the contact.
    $contact_name = "";
    if ($row['contact_uid'] > 0)
    {
      $result_contact = pg_query($dbconn, "
       SELECT name
         FROM $contact_view
        WHERE contact_uid = ".$row['contact_uid']);
      if (!$result_contact)
      {
        return pg_last_error ($dbconn);
      } else {
        $contact_name = pg_fetch_result ($result_contact, 0, 0);
      }  // if (!$result_contact)
    }  // if ($row['contact_uid'] > 0)
    $run_list = run_list_for_project ($dbconn, $project_uid);
    $project_info_string = $row_start . "Project Name=" .
                       $row['project_name'] . "\"\r\n" .
                       $row_start . "Primary Investigator=" .
                       $row['pi_name'] . "\"\r\n" .
                       $row_start . "Run Type=" .
                       $row['run_type'] . "\"\r\n" .
                       $row_start . "Status=" .
                       $row['status'] . "\"\r\n" .
                       $row_start . "Contact=" .
                       $contact_name . "\"\r\n" .
                       $row_start . "Run List=" .
                       $run_list . "\"\r\n" .
                       $row_start . "Creation Date=" .
                       $row['creation_date'] . "\"\r\n" .
                       $row_start . "Project Description=" .
                       replace_eol ($row['project_description']) . "\"\r\n" .
                       $row_start . "Analysis Notes=" .
                       replace_eol ($row['analysis_notes']) . "\"\r\n" .
                       $row_start . "Admin Comments=" .
                       replace_eol ($row['admin_comments']) . "\"\r\n";
    return $project_info_string;
  }  // if (!$result)
}  // function export_project_info
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['download_project_details']))
    {
      header("Pragma: public");
      header("Expires: 0"); // set expiration time
      header("Content-Type: application/octet-stream");
      header('Content-Disposition: attachment; filename="project_details.csv"');
      header("Connection: Close");
      if ($project_uid > 0)
      {
        echo export_project_info (
         $dbconn, $project_uid, $project_view, $primary_investigator_view,
         $contact_view, $sample_view, $run_view, $run_lane_view,
         $run_lane_sample_view, $export_comment_symbol);
        // Put the sample content in the file.
        if (isset($_SESSION['sample_content']))
        {
          $sample_content = $_SESSION['sample_content'];
          echo ($sample_content);
        }  // if (isset($_SESSION['sample_content']))
      }  // if ($project_uid > 0)
      exit;
    } elseif (isset($_POST['submit_add_samples'])) {
      header("location: add_samples_mode1.php");
      exit;
    } elseif (isset($_POST['submit_update_samples'])) {
      header("location: update_samples_mode1.php");
      exit;
    } elseif (isset($_POST['submit_delete_samples'])) {
      header("location: delete_samples_from_project.php");
      exit;
    } elseif (isset($_POST['submit_project_log'])) {
      header("location: project_log.php");
      exit;
    }  // if (isset($_POST['submit_add_samples']))
  }  // if ($_POST['process'] == 1)
} else {
  unset ($_SESSION['sample_content']);
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Project Details, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<link href="DAC_LIMS_styles.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.twoColElsLtHdr #sidebar1 { padding-top: 30px; }
.twoColElsLtHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
<style type="text/css">
<!--
.style1 {font-family: Arial, Helvetica, sans-serif}
.style2 {color: #999999;}
a:link {
  color: #0000FF;
}
a:visited {
  color: #000080;
}
-->
</style>
<style type="text/css">
<!--
.warningtext {
   font-family: Arial, Helvetica, sans-serif;
   font-size: 125%; color:#FF00FF; font-weight: bold;
  }
-->
</style>
<?php
  readfile("text_styles.css");
?>

<script src="library/sorttable.js" 
  language="javascript" 
    type="text/javascript"></script>
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header">
<?php
  echo '<h1 style="text-align: center"><span class="titletext">',
       'Project Details - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  if ($project_uid < 1)
   echo '<span class="errortext">No project.</span><br />';
?>
  <iframe src="project_info.php" width="80%" 
   style="border: 2px solid blue; height: 325px; " >
   <p>Your browser does not support iframes.</p>
  </iframe>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_samples" >
<input type="hidden" name="process" value="1" />
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_add_samples" value="Add Samples"',
         ' title="Add new samples to project." class="buttontext" />';
    echo '<input type="submit" name="submit_update_samples" ',
         'value="Update Samples"',
         ' title="Update project samples." class="buttontext" />';
    echo '<input type="submit" name="submit_delete_samples" ',
         'value="Delete Samples"',
         ' title="Select project sample records for deletion." ',
         'class="buttontext" />';
    echo '<input type="submit" name="download_project_details"',
         'value="Download Project Details"',
         'title="Downloads project details to a comma-separated ',
         'variable file." />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<input type="submit" name="submit_project_log" value="Project Log"',
       ' title="Move to Project Log page." class="buttontext" />';
  echo '<br /><br />';
?>
<table id="sample_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample Description</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Status</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Barcode</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Barcode Index</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Species</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Type</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Batch Group</th>
  <?php
    if ($use_sample_bonus_columns)
    {
      echo '<th class="sorttable_numeric" scope="col" width="200"',
           'style="text-align:center">',
           'Concentration</th>';
      echo '<th class="sorttable_numeric" scope="col" width="200"',
           'style="text-align:center">',
           'Volume</th>';
      echo '<th class="sorttable_numeric" scope="col" width="200"',
           'style="text-align:center">',
           'RIN</th>';
    }  // if ($use_sample_bonus_columns)
  ?>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Comments</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Run List</th>
  </tr>
</thead>
<tbody>
<?php
$sample_content = "";
if ($project_uid > 0)
{
  // Set the sample header for the project details report file.
  $sample_content = $project_report_header . "\r\n";
  // Get the samples for the project.
  $result = pg_query($dbconn,"
   SELECT sample_uid,
          sample_name,
          sample_description,
          $sample_view.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          concentration,
          volume,
          rna_integrity_number,
          $sample_view.comments
        FROM $sample_view,
             $project_view
       WHERE $sample_view.project_uid = $project_uid AND
             $sample_view.project_uid = $project_view.project_uid
       ORDER BY sample_name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row_sample = pg_fetch_assoc ($result);
      $sample_uid = $row_sample['sample_uid'];
      $run_list = run_list_for_sample (
       $dbconn, $sample_uid, $run_view, $run_lane_view, $run_lane_sample_view);
      // Set the next sample row for the project details export file.
      if ($use_sample_bonus_columns)
      {
        $sample_bonus_columns = $row_sample['concentration'] .
         $csv_separator .
         $row_sample['volume'] . $csv_separator .
         $row_sample['rna_integrity_number'] . $csv_separator;
      } else {
        $sample_bonus_columns = "";
      }  // if ($use_sample_bonus_columns)
      $sample_content .= "\"" . 
                         $row_sample['sample_name'] . $csv_separator .
                         $row_sample['sample_description'] . $csv_separator .
                         $row_sample['status'] . $csv_separator .
                         $row_sample['barcode'] . $csv_separator .
                         $row_sample['barcode_index'] . $csv_separator .
                         $row_sample['species'] . $csv_separator .
                         $row_sample['sample_type'] . $csv_separator .
                         $row_sample['batch_group'] . $csv_separator .
                         $sample_bonus_columns .
                         $row_sample['comments'] . $csv_separator .
                         $run_list . "\"\r\n";
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="sampleWindow(\'',
           $row_sample['sample_uid'],'\');" ',
           'title="Display information on sample ',
           $row_sample['sample_name'],'.">',
           td_ready($row_sample['sample_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 100px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row_sample['sample_description']),
           '</font></div></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['status']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode_index']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['species']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['sample_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['batch_group']),'</td>';
      if ($use_sample_bonus_columns)
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['concentration']),'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['volume']),'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['rna_integrity_number']),'</td>';
      }  // if ($use_sample_bonus_columns)
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 150px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row_sample['comments']),
           '</font></div></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($run_list),'</td>';
      echo '</tr>';
    }  // for ($i=0; $i < pg_num_rows($result); $i++)
  }  // if (!$result)
}  // if ($project_uid > 0)
echo '</tbody>';
echo '</table></form><br />';
$_SESSION['sample_content'] = $sample_content;
?>
  </div>
  <!-- end #mainContent -->
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
