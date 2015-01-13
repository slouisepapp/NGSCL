<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_work_schedule")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_table']))
    {
      // Put posted table values into sample line array.
      $line_number = 1;
      foreach ($_SESSION['project_uid'] as $project_row => $project_uid)
      {
        $prepped_and_ready = standardize_boolean (
         $_SESSION['prepped_and_ready'][$project_row]);
        $project_prep_comments = ddl_ready (
         $_SESSION['project_prep_comments'][$project_row]);
        $assigned_to = ddl_ready ($_SESSION['assigned_to'][$project_row]);
        // Update project prep comments for this project.
        $result_update = pg_query ($dbconn, "
         UPDATE project
            SET prepped_and_ready = $prepped_and_ready,
                project_prep_comments = '$project_prep_comments',
                assigned_to = '$assigned_to'
          WHERE project_uid = $project_uid");
        if (!$result_update)
        {
          $array_error[] = "Row ".$line_number.": ".pg_last_error ($dbconn);
        }  // if (!$result_update)
        $line_number = $line_number + 1;
      }  // foreach ($_SESSION['project_uid'] as $project_row => $project_uid)
     if (count($array_error) < 1)
     {
      // If the inserts were successful, return to work schedule page.
      header("location: work_schedule.php");
      exit;
     }  // if (count($array_error < 1)
    } elseif (isset($_POST['submit_work_schedule'])) {
      header("location: work_schedule.php");
      exit;
    }  // if (isset($_POST['submit_table']))
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Work Schedule, ',$abbreviated_app_name,'</title>';
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
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center"><span class="titletext">',
       'Update Work Schedule - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  foreach ($array_error as $error)
  {
    if (strlen (trim ($error)) > 0)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // if (strlen (trim ($error)) > 0)
  }  // foreach ($array_error as $error)
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
   'name="form_work_schedule">';
  echo '<input type="hidden" name="process" value="1" />';
  echo '<input type="submit" name="submit_table" value="Save" ',
   'title="Save project prep comments." class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
       'title="Restore to most recent saved changes." class="buttontext" />';
  echo '<input type="submit" name="submit_work_schedule" value="Quit" ',
   'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
  'title="Return to Work Schedule page without saving." class="buttontext" />';
  echo '<br />';
?>
<p class="updateabletext"><b><i>* Updateable Fields</i></b></p>
<table id="work_schedule_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_nosort_green" scope="col" width="200"
     style="text-align:center" >
    Prepped and Ready</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Run Type</th>
    <th class="sorttable_nosort_green" scope="col" width="200"
     style="text-align:center" >
    Assigned To</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    PI</th>
    <th class="sorttable_alpha" scope="col" width="200" style="text-align:center" >
    Project</th>
    <th class="sorttable_numeric" scope="col" width="200" style="text-align:center" >
    Samples</th>
    <th class="sorttable_nosort_green" scope="col" width="200"
     style="text-align:center">
    Project Prep Comments</th>
  </tr>
</thead>
<tbody>
<?php
  $result = pg_query($dbconn,"
   SELECT CASE WHEN prepped_and_ready THEN 'TRUE'
               ELSE 'FALSE'
          END AS prepped_and_ready_string,
          project.project_uid,
          ref_run_type.ref_run_type_uid,
          ref_run_type.run_type,
          project.assigned_to,
          primary_investigator.primary_investigator_uid,
          name,
          project_name,
          COUNT(1) AS sample_count,
          project_prep_comments
     FROM primary_investigator,
          project,
          sample,
          ref_run_type
    WHERE UPPER (project.status) = 'ACTIVE' AND
          UPPER (sample.status) = 'ACTIVE' AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid AND
          project.project_uid = sample.project_uid AND
          project.ref_run_type_uid =
           ref_run_type.ref_run_type_uid
    GROUP BY prepped_and_ready,
          project.project_uid,
          ref_run_type.ref_run_type_uid,
          run_type,
          project.assigned_to,
          project_name,
          primary_investigator.primary_investigator_uid,
          name,
          project_prep_comments
    ORDER BY name, project_name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // Prepare drop-down box for prepped_and_ready value.
      if ($row['prepped_and_ready_string'] == 'FALSE')
      {
        $true_selected = '';
        $false_selected = 'selected="selected"';
      } else {
        $true_selected = 'selected="selected"';
        $false_selected = '';
      }  // if ($row['prepped_and_ready_string'] == 'FALSE')
      echo '<td class="tdBlueBorder" style="text-align:center">',
           drop_down_boolean ("prepped_and_ready[]", $true_selected,
                              $false_selected, "inputrow"),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['run_type']),
           '</td>';
      echo '<td width="5%"><input name="assigned_to[]" type="text" ',
           'title="Only alphanumeric characters, ',
           'space, dot, and underscore allowed." ',
           'class="inputrow" size="12" value="',
           htmlentities ($row['assigned_to']),
           '" /></td>';
      $_SESSION['project_prep_comments'] = $row['project_prep_comments'];
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row['name'],'.">',
           td_ready($row['name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
       '<input type="hidden" name="project_uid[]" value="',
       $row['project_uid'],'" />',
       '<a href="javascript:void(0)" onclick="projectWindow(\'',
       $row['project_uid'],'\');" ',
       'title="Display information on project ',
       $row['project_name'],'.">',
       td_ready($row['project_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
       '<a href="javascript:void(0)" onclick="activeSamplesWindow(\'',
       $row['project_uid'],'\');" ',
       'title="Active samples for project.">',
       $row['sample_count'],'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
       '<textarea name="project_prep_comments[]" cols="40" rows="2" ',
       'class="inputseriftext">',
       htmlentities ($_SESSION['project_prep_comments'], ENT_NOQUOTES),
       '</textarea></td>';
      echo '</tr>';
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
  }  // if (!$result)
?>
</tbody>
</table>
</form>
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
