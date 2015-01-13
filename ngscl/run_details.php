<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
require_once('run_functions.php');
require_once 'user_view.php';
$dbconn = database_connect();
$export_lane_header = "Flowcell Lane,Total Reads per Lane,Project," .
                      "Primary Investigator,Contact,Sample Type," .
                      "Species,Sample,Barcode_Index,Barcode," .
                      "Batch Group,Insert Size,Load Concentration," .
                      "Sample Lane Comment";
$sample_sheet_header = "FCID,Lane,SampleID,SampleRef,Index,Description," .
                       "Control,Recipe,Operator,Sample Project";
$sample_sheet_array = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
if (isset($_POST['download_run_details']))
{
  header("Pragma: public");
  header("Expires: Sunday June 10th 2011"); // set expiration time
  header("Content-Type: application/octet-stream");
  header('Content-Disposition: attachment; filename="run_details.csv"');
  header("Connection: Close");
  if ($run_uid > 0)
  {
    echo export_run_info ($dbconn, $run_uid, $export_comment_symbol);
    // Put the lane content in the file.
    if (isset($_SESSION['lane_content']))
    {
      $lane_content = $_SESSION['lane_content'];
      echo ($lane_content);
    }  // if (isset($_SESSION['lane_content']))
  }  // if ($run_uid > 0)
  exit();
}  // if (isset($_POST['download_run_details']))
if (isset($_POST['download_sample_sheets']))
{
  // Create zip file for the eight sample sheets.
  $zip_SampleSheets = new ZipArchive ();
  $SampleSheets_filename = $upload_dir . "/SampleSheets.zip";
  if ($zip_SampleSheets->open (
       $SampleSheets_filename, ZIPARCHIVE::CREATE)!== TRUE)
  {
      exit ("cannot open <$SampleSheets_filename>\n");
  }  // if ($zip_SampleSheets->open (
  // Create a sample sheet file for each lane.
  for ($i=0; $i < $num_run_lanes; $i++)
  {
    $lane_number = $i + 1;
    $sample_sheet_content = $sample_sheet_header . "\r\n";
    $sample_sheet_filename = "SampleSheet_" . $lane_number . ".csv";
    if (isset($_SESSION['sample_sheet_content'][$i]))
    {
      $sample_sheet_content .= $_SESSION['sample_sheet_content'][$i];
    }  // if (isset($_SESSION['sample_sheet_content'][$i]))
  $zip_SampleSheets->addFromString (
   $sample_sheet_filename, $sample_sheet_content);
  }  // for ($i=0; $i < $num_run_lanes; $i++)
  $zip_SampleSheets->close ();
  header("Pragma: public");
  header("Expires: Sunday June 10th 2011"); // set expiration time
  header('Content-Type: application/zip');
  header('Content-Disposition: attachment; filename="SampleSheets.zip"');
  header('Content-Length: ' . filesize($SampleSheets_filename));
  readfile ($SampleSheets_filename);
  unlink ($SampleSheets_filename);
  exit();
}  // if (isset($_POST['download_sample_sheets']))
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_manage_lane_samples']))
    {
      header("location: manage_lane_active_samples.php");
      exit;
    } elseif (isset($_POST['submit_update_lane_info'])) {
      header("location: update_lane_info.php");
      exit;
    } elseif (isset($_POST['submit_qa'])) {
      if(isset($_SESSION['missing_response']))
        unset($_SESSION['missing_response']);
      header("location: post_run_qa_data.php");
      exit;
    } elseif (isset($_POST['submit_notification'])) {
        $_SESSION['calling_page'] = 'run_details.php';
        header("location: run_notification.php");
        exit;
    } elseif (isset($_POST['submit_billing'])) {
      header("location: billing.php");
      exit;
    }  // if (isset($_POST['submit_manage_lane_samples']))
  }  // if ($_POST['process'] == 1)
} else {
  unset($_SESSION['lane_content']);
  unset($_SESSION['sample_sheet_content']);
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Run Details, ',$abbreviated_app_name,'</title>';
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
       'Run Details - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
?>
  <div id="mainContent">
  <iframe src="run_info.php" width="550px;" 
   style="border: 2px solid blue; height: 360px;" >
   <p>Your browser does not support iframes.</p>
  </iframe><br /><br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_lane_samples" >
<input type="hidden" name="process" value="1" />
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // Check for samples in the run with the wrong run type.
    $mismatch_string = run_sample_type_mismatch ($dbconn, $run_uid);
    if (strlen (trim ($mismatch_string)) > 0);
    {
      echo '<span class="errortext">',$mismatch_string,'</span><br />';
    }  // if (strlen (trim ($mismatch_string)) > 0);
    echo '<h2 class="headertext">Manage Run</h2>';
    echo '<input type="submit" name="submit_update_lane_info"',
         'value="Update Lane Info"',
         'title="Update lane information."',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_manage_lane_samples"',
         'value="Manage Lane Samples"',
         'title="Add or remove samples from lanes." class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<input type="submit" name="submit_qa"',
       'value="QA Data"',
       'title="Post-run QA data for samples and undetermined indices." ',
       'class="buttontext" />';
  if ($_SESSION['app_role'] != 'pi_user' ||
      $_SESSION['admin_role'] == 'pi_admin')
  {
    echo '<input type="submit" name="submit_billing"',
         'value="Billing" title="Display billing information."',
         'class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user' ||...
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<h2 class="headertext">Reports</h2>';
    echo '<input type="submit" name="download_run_details"',
         'value="Run Details"',
         'title="Downloads run details to a comma-separated variable file." />';
    echo '<input type="submit" name="download_sample_sheets"',
         'value="Sample Sheets"',
         'title="Downloads a zip file containing the sample sheets." />';
    echo '<input type="submit" name="submit_notification"',
         'value="Notification" ',
         'title="Notify those users on the run mailing list."',
         'class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
?>
<br /><br />
<table id="sample_table" border="1" width="100%" class="nosort">
<thead>
  <tr>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Flowcell Lane</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    # of Samples</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Total Load Concentration</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="50" >
    Total Reads</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Project</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Primary Investigator</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Contact</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Sample Type</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Species</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Sample</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Barcode</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Barcode Index</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Batch Group</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Insert Size</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Load Concentration</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Lane Comment</th>
  </tr>
</thead>
<tbody>
<?php
$lane_content = "";
if ($run_uid > 0)
{
  // Set the lane header for the run_details export file.
  $lane_content = $export_lane_header . "\r\n";
  // Loop through all the run lanes.
  for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
  {
    // Initialize the sample sheet data variable for this lane.
    $sample_sheet_index = $lane_number - 1;
    $sample_sheet_array[$sample_sheet_index] = "";
    // ****
    // Select for the sample lane information using a UNION.
    // The select returns:
    //  1) Samples with fields in all the tables of the FROM clause.
    //  2) Samples belonging to a project that does not have a contact.
    //  3) A row if the lane has no samples.
    // ****
    $result_lane = pg_query ($dbconn, "
      SELECT $run_lane_view.run_lane_uid,
             $run_lane_view.total_reads,
             $sample_view.batch_group,
             $run_lane_sample_view.insert_size,
             $run_lane_sample_view.load_concentration,
             $project_view.project_uid,
             $project_view.project_name,
             $primary_investigator_view.primary_investigator_uid,
             $primary_investigator_view.name AS pi_name,
             $contact_view.contact_uid,
             $contact_view.name AS contact_name,
             $sample_view.sample_uid,
             $sample_view.sample_type,
             $sample_view.species,
             $sample_view.sample_name,
             $sample_view.barcode_index,
             $sample_view.barcode,
             $run_lane_sample_view.comments
        FROM $run_view,
             $run_lane_view,
             $run_lane_sample_view,
             $sample_view,
             $project_view,
             $primary_investigator_view,
             $contact_view
       WHERE lane_number = $lane_number AND
             $run_lane_view.run_uid = $run_uid AND
             $run_view.run_uid = $run_lane_view.run_uid AND
             $run_lane_view.run_lane_uid =
              $run_lane_sample_view.run_lane_uid AND
             $run_lane_sample_view.sample_uid = $sample_view.sample_uid AND
             $sample_view.project_uid = $project_view.project_uid AND
             $project_view.primary_investigator_uid =
              $primary_investigator_view.primary_investigator_uid AND
             $project_view.contact_uid = $contact_view.contact_uid
       UNION
      SELECT $run_lane_view.run_lane_uid,
             $run_lane_view.total_reads,
             $sample_view.batch_group,
             $run_lane_sample_view.insert_size,
             $run_lane_sample_view.load_concentration,
             $project_view.project_uid,
             $project_view.project_name,
             $primary_investigator_view.primary_investigator_uid,
             $primary_investigator_view.name AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             $sample_view.sample_uid,
             $sample_view.sample_type,
             $sample_view.species,
             $sample_view.sample_name,
             $sample_view.barcode_index,
             $sample_view.barcode,
             $run_lane_sample_view.comments
        FROM $run_view,
             $run_lane_view,
             $run_lane_sample_view,
             $sample_view,
             $project_view,
             $primary_investigator_view
       WHERE lane_number = $lane_number AND
             $run_lane_view.run_uid = $run_uid AND
             $run_view.run_uid = $run_lane_view.run_uid AND
             $run_lane_view.run_lane_uid =
              $run_lane_sample_view.run_lane_uid AND
             $run_lane_sample_view.sample_uid = $sample_view.sample_uid AND
             $sample_view.project_uid = $project_view.project_uid AND
             $project_view.primary_investigator_uid =
              $primary_investigator_view.primary_investigator_uid AND
             $project_view.contact_uid IS NULL
       UNION
      SELECT $run_lane_view.run_lane_uid,
             $run_lane_view.total_reads,
             ' ' AS batch_group,
             ' ' AS insert_size,
             NULL AS load_concentration,
             0   AS project_uid,
             ' ' AS project_name,
             0   AS primary_investigator_uid,
             ' ' AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             0   AS sample_uid,
             ' ' AS sample_type,
             ' ' AS species,
             ' ' AS sample_name,
             ' ' AS barcode_index,
             ' ' AS barcode,
             ' ' AS comments
        FROM $run_lane_view
       WHERE lane_number = $lane_number AND
             $run_lane_view.run_uid = $run_uid AND
             run_lane_uid 
      NOT IN (SELECT run_lane_uid
                FROM $run_lane_sample_view
               WHERE run_uid = $run_uid)
    ORDER BY project_name, sample_name");
    if (!$result_lane)
    {
      echo '<tr><td>',pg_last_error ($dbconn),'</td></tr>';
    } else {
      $rowspan = pg_num_rows ($result_lane);
      // Create a row if the lane has database values.
      if ($rowspan > 0)
      {
        for ($i=0; $i < $rowspan; $i++)
        {
          // Fetch the query result.
          $row_lane = pg_fetch_assoc ($result_lane);
          // Set the next lane row for the run_details export file.
          $lane_content .= "\"" . $lane_number . $csv_separator .
                           $row_lane['total_reads'] . $csv_separator .
                           convert_to_alphanum_plus_underscore_only (
                            trim ($row_lane['project_name'])) . $csv_separator .
                           $row_lane['pi_name'] . $csv_separator .
                           $row_lane['contact_name'] . $csv_separator .
                           $row_lane['sample_type'] . $csv_separator .
                           str_replace (' ', '_', trim ($row_lane['species'])) .
                            $csv_separator .
                           $row_lane['sample_name'] . $csv_separator .
                           $row_lane['barcode_index'] . $csv_separator .
                           $row_lane['barcode'] . $csv_separator .
                           $row_lane['batch_group'] . $csv_separator .
                           $row_lane['insert_size'] . $csv_separator .
                           $row_lane['load_concentration'] . $csv_separator .
                           $row_lane['comments'] . "\"\r\n";
          // Set the next row of the sample sheet for this lane.
          if (strlen (trim ($row_lane['sample_name'])) > 0)
          {
            $sample_sheet_array[$sample_sheet_index] .=
             "\"" . $csv_separator .
             $lane_number . $csv_separator .
             $row_lane['sample_name'] . $csv_separator .
             str_replace (' ', '_', trim ($row_lane['species'])) .
             $csv_separator .
             $row_lane['barcode_index'] . $csv_separator .
             $row_lane['barcode'] . $csv_separator .
             $sample_sheet_control . $csv_separator .
             $sample_sheet_recipe . $csv_separator .
             $sample_sheet_operator . $csv_separator .
             convert_to_alphanum_plus_underscore_only (
              trim ($row_lane['project_name'])) .
             "\"\r\n";
          }  // if (strlen (trim ($row_lane['sample_name'])) > 0)
          $run_lane_uid = $row_lane['run_lane_uid'];
          echo '<tr>';
          // Lane information only if this is the first sample.
          if ($i == 0)
          {
            // Calculate number of samples.
            if (strlen (trim ($row_lane['pi_name'])) > 0)
            {
              $num_lane_samples = $rowspan;
            } else {
              $num_lane_samples = 0;
            }  // if (strlen (trim ($row_lane['pi_name'])) > 0)
            // Calculate the total load concentration for the lane.
            $result_concentration = pg_query ($dbconn, "
             SELECT coalesce (sum (load_concentration), 0)
               FROM $run_lane_sample_view
              WHERE run_lane_uid = $run_lane_uid");
            if (!$result_concentration)
            {
              $total_concentration = pg_last_error ($dbconn);
            } else {
              $total_concentration = pg_fetch_result ($result_concentration,
                                                      0, 0);
            }  // if (!$result_concentration)
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 $lane_number,'</td>';
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 td_ready($num_lane_samples),'</td>';
            if ($total_concentration > 0)
            {
              echo '<td class="tdBlueBorder" style="text-align:center" ',
                   'rowspan="',
                   $rowspan,'">',
                   $total_concentration,'</td>';
            } else {
              echo '<td class="tdBlueBorder" style="text-align:center" ',
                   'rowspan="',
                   $rowspan,'">',
                   '&nbsp;</td>';
            }  // if ($total_concentration > 0)
            $div_height = $rowspan * 30;
            echo '<td class="tdBlueBorder" style="text-align:left" rowspan="',
                 $rowspan,'">',
                 '<div style="width: 150px; height: ',
                 $div_height,
                 'px; overflow: auto; padding: 5px;"><font face="sylfaen">',
                 td_ready($row_lane['total_reads']),
                 '</font></div></td>';
          }  // if ($i == 0)
          if (strlen (trim ($row_lane['project_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="projectWindow(\'',
                 $row_lane['project_uid'],'\');" ',
                 'title="Display information on project ',
                 $row_lane['project_name'],'.">',
                 td_ready($row_lane['project_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['project_name'])) > 0)
          if (strlen (trim ($row_lane['pi_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="primary_investigatorWindow(\'',
                 $row_lane['primary_investigator_uid'],'\');" ',
                 'title="Display information on primary investigator ',
                 $row_lane['pi_name'],'.">',
                 td_ready($row_lane['pi_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['pi_name'])) > 0)
          if (strlen (trim ($row_lane['contact_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="contactWindow(\'',
                 $row_lane['contact_uid'],'\');" ',
                 'title="Display information on contact ',
                 $row_lane['contact_name'],'.">',
                 td_ready($row_lane['contact_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['contact_name'])) > 0)
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['sample_type']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['species']),'</td>';
          if (strlen (trim ($row_lane['sample_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '<a href="javascript:void(0)" onclick="sampleWindow(\'',
                 $row_lane['sample_uid'],'\');" ',
                 'title="Display information on sample ',
                 $row_lane['sample_name'],'.">',
                 td_ready($row_lane['sample_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['sample_name'])) > 0)
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['barcode']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['barcode_index']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['batch_group']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['insert_size']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['load_concentration']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:left">',
               '<div style="width: 150px; height: 30px; ',
               'overflow: auto; padding: 5px;"><font face="sylfaen">',
               td_ready($row_lane['comments']),
               '</font></div></td>';
          echo '</tr>';
        }  // for ($i=0; $i < $rowspan; $i++)
      } else {
        // Set the next lane row for the run_details export file.
        $lane_content .= "\"" . $lane_number . "\"\r\n";
        // Create a row where the lane has no database values.
        echo '<tr>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             $lane_number,'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">0</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '</tr>';
      }  // if ($rowspan > 0)
    }  // if (!$result_lane)
  }  // for ($lane_number = 1; $lane_number <= num_run_lanes; $lane_number++)
}  // if ($run_uid > 0)
echo '</tbody>';
echo '</table></form><br />';
$_SESSION['lane_content'] = $lane_content;
for ($i=0; $i < $num_run_lanes; $i++)
{
  $_SESSION['sample_sheet_content'][$i] = (isset ($sample_sheet_array[$i]) ?
   $sample_sheet_array[$i] : "");
}  // for ($i=0; $i < $num_run_lanes; $i++)
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
