<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_project_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_project_details']))
    {
      // Return to project details page
      header("location: project_details.php");
      exit;
    } elseif (isset($_POST['submit_table'])) {
      // Process the deletes.
      header("location: process_delete_samples.php");
      exit;
    }  // if (isset($_POST['submit_project_details']))
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Delete Sample Records, ',$abbreviated_app_name,'</title>';
?>
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
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Delete Sample Records - ',
       $app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  $project_uid = (isset ($_SESSION['project_uid']) ?
   $_SESSION['project_uid'] : 0);
  $project_name = "";
  $run_type = "";
  if ($project_uid > 0)
  {
    $result_puid = pg_query ($dbconn, "
     SELECT project_name,
            run_type
       FROM project,
            ref_run_type
      WHERE project_uid = $project_uid AND
            project.ref_run_type_uid = ref_run_type.ref_run_type_uid");
    if (!$result_puid)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $project_name = pg_fetch_result ($result_puid, 0, 0);
      $run_type = pg_fetch_result ($result_puid, 0, 1);
    }  // if (!$result_puid)
  }  // if ($project_uid > 0)
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent"> ',
       '<script src="javascript_source.js" ',
       'language="javascript" ',
       'type="text/javascript"></script>';
  echo '<h3 class="grayed_out">Project: ',$project_name,'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$run_type,'</h3>';
  echo '<form method="post" action="" name="form_table">';
  echo '<input type="hidden" name="process" value="1"/>';
  echo '<input type="submit" name="submit_project_details" ',
       'value="Project Details" ',
       'title="Return to Project Details page." class="buttontext" />';
  echo '<input type="submit" name="submit_table" value="Delete Selected Records" ',
       'title="Delete selected sample records." class="buttontext" />';
  echo '<br /><br />';
?>
<table id="delete_table" name="delete_table"
 border="1" width="100%" class="sortable" >
<thead>
  <tr>
<?php
  echo '<th class="sorttable_nosort" scope="col" width="2%" ',
       'style="text-align:center;" > ',
       '<input type="checkbox" name="submit_check_all" ',
       'onclick="checkAllTable(\'delete_table\', this)" ',
       'title="Select all items." />',
       '</th>';
?>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Sample</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Sample Description</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Status</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Barcode</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Barcode Index</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Species</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Type</th>
    <th class="sorttable_alpha" scope="col" width="8%"
     style="text-align:center;" >
    Batch Group</th>
    <th class="sorttable_alpha" scope="col" width="50%"
     style="text-align:center;" >
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
if ($project_uid > 0)
{
  $result = pg_query($dbconn,"
   SELECT sample_uid,
          sample_name,
          sample_description,
          sa.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          comments
        FROM sample sa,
             project pr
       WHERE sa.project_uid = $project_uid AND
          sa.project_uid = pr.project_uid
       ORDER BY sample_name");
  for ($i=0; $i < pg_num_rows($result); $i++)
  {
    $row_sample = pg_fetch_assoc ($result);
    $sample_uid = $row_sample['sample_uid'];
    // Determine if user should be permitted to delete the sample.
    $checkbox_disabled = '';
    $checkbox_title = 'Select sample for deletion.';
    $result_run = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM run_lane_sample
      WHERE sample_uid = $sample_uid");
    if (!$result_run)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
      exit;
    } elseif ($line = pg_fetch_assoc($result_run)) {
      if ($line['row_count'] > 0)
      {
        $checkbox_disabled = 'disabled="disabled"';
        $checkbox_title = 'Delete disabled as sample is archived or '.
                          'part of a run.';
      } else {
        $result_archive = pg_query ($dbconn, "
         SELECT COUNT(1) AS row_count
           FROM archive
          WHERE sample_uid = $sample_uid");
        if (!$result_archive)
        {
          echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
        } elseif ($line = pg_fetch_assoc($result_archive)) {
          if ($line['row_count'] > 0)
          {
            $checkbox_disabled = 'disabled="disabled"';
            $checkbox_title = 'Delete disabled as sample is archived or '.
                              'part of a run.';
          } // if ($line['row_count'] > 0)
        } // if (!$result_archive)
      }  // if ($line['row_count'] > 0)
    }  // if (!result_run)
    echo '<tr>';
    echo '<td class="tdBlueBorder">',
         '<input type="checkbox" name="sample_uid_delete[]" value="',
         $sample_uid,
         '" ',$checkbox_disabled,
         ' title="',$checkbox_title,'" /></td>';
    echo '<td class="tdBlueBorder" style="text-align:center">',
         '<a href="javascript:void(0)" onclick="sampleWindow(\'',
         $row_sample['sample_uid'],'\');" ',
         'title="Display information on sample ',
         $row_sample['sample_name'],'.">',
         td_ready($row_sample['sample_name']),'</a></td>';
  echo '<td class="tdBlueBorder" style="text-align:left">',
   '<div style="width: 100px; height: 50px; overflow: auto; padding: 5px;">',
   '<font face="sylfaen">',
   td_ready($row_sample['sample_description']),
   '</font></div></td>';
  echo '<td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['status']).'</td>
   <td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['barcode']).'</td>
   <td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['barcode_index']).'</td>
   <td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['species']).'</td>
   <td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['sample_type']).'</td>
   <td class="tdBlueBorder" style="text-align:center">'.td_ready($row_sample['batch_group']).'</td>
   <td class="tdBlueBorder" style="text-align:left">
   <div style="width: 210px; height: 50px; overflow: auto; padding: 5px;">
   <font face="sylfaen">'.td_ready($row_sample['comments']).'</font></div></td>
   </tr>';
  }  // for ($i=0; $i < pg_num_rows($result); $i++)
}  // if ($project_uid > 0)
  echo '</tbody></table><br />';
?>
</form>
  <!-- end #mainContent -->
  </div>
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
