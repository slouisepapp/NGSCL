<?php
require_once('db_fns.php');
require_once('project_functions.php');
require_once('constants.php');
// Read insecure SID from file.
$array_error = array();
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_project_log")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
$label_class = 'optionaltext';
unset ($_SESSION['entry_method']);
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_update_info']))
    {
      header("location: update_project_log.php");
      exit;
    } elseif (isset($_POST['submit_update_samples'])) {
      header("location: update_log_samples_mode1.php");
      exit;
    } elseif (isset($_POST['submit_delete'])) {
      // Make a project log object.
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      // Check for errors.
      if (trimmed_string_not_empty ($my_log->db_error))
      {
        $array_error[] = $my_log->db_error;
      } else {
        // Make a project log run lane object.
        $project_log_uid_array = $my_log->project_log_uid;
        $my_log_run_lane = new ProjectLogRunLane (
         $dbconn,  $_SESSION['project_log_run_lane'],
         $project_log_uid_array['value']);
        // Delete the project log from the database.
        $delete_error = $my_log->delete_from_database (
         $dbconn, $my_log_run_lane);
        // Check for errors.
        if (count ($delete_error) > 0)
        {
          $array_error[] = $delete_error;
        } else {
          header("location: project.php");
          exit();
        }  // if (count ($delete_error) > 0)
      }  // if (trimmed_string_not_empty ($my_log->db_error))
    } elseif (isset($_POST['submit_download_log'])) {
      header("Pragma: public");
      header("Expires: 0"); // set expiration time
      header("Content-Type: application/octet-stream");
      header('Content-Disposition: attachment; filename="project_log.csv"');
      header("Connection: Close");
      // Make a project log object.
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      // Check for errors.
      if (trimmed_string_not_empty ($my_log->db_error))
      {
        $array_error[] = $my_log->db_error;
      } else {
        $project_log_uid_array = $my_log->project_log_uid;
        // Make a project log run lane object.
        $my_log_run_lane = new ProjectLogRunLane (
         $dbconn,  $_SESSION['project_log_run_lane'],
         $project_log_uid_array['value']);
        // Write the log data to the report.
        echo $my_log->report ($my_log_run_lane);
      }  // if (trimmed_string_not_empty ($my_log->db_error))
      exit();
    } elseif (isset($_POST['submit_samples_std']) &&
              $_SESSION['app_role'] == 'dac_grants') {
      header("Pragma: public");
      header("Expires: 0"); // set expiration time
      header("Content-Type: application/octet-stream");
      header('Content-Disposition: attachment; filename="project_log_std_samples.txt"');
      header("Connection: Close");
      // Make a project log object.
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      $project_log_uid_array = $my_log->project_log_uid;
      $my_log_run_lane = new ProjectLogRunLane (
       $dbconn,  $_SESSION['project_log_run_lane'], 
       $project_log_uid_array['value']);
      echo $my_log_run_lane->report_std_barcode ();
      exit();
    } elseif (isset($_POST['submit_samples_custom']) &&
              $_SESSION['app_role'] == 'dac_grants') {
      header("Pragma: public");
      header("Expires: 0"); // set expiration time
      header("Content-Type: application/octet-stream");
      header('Content-Disposition: attachment; filename="project_log_custom_samples.txt"');
      header("Connection: Close");
      // Make a project log object.
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      $project_log_uid_array = $my_log->project_log_uid;
      $my_log_run_lane = new ProjectLogRunLane (
       $dbconn,  $_SESSION['project_log_run_lane'], 
       $project_log_uid_array['value']);
      echo $my_log_run_lane->report_custom_barcode ();
      exit();
    } elseif (isset($_POST['submit_project_details'])) {
      header("location: project_details.php");
      exit;
    } else {
      // Make a project log object.
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      // Check for errors.
      if (trimmed_string_not_empty ($my_log->db_error))
      {
        $array_error[] = $my_log->db_error;
      } else {
        $project_log_uid_array = $my_log->project_log_uid;
        // Make a project log run lane object.
        $my_log_run_lane = new ProjectLogRunLane (
         $dbconn,  $_SESSION['project_log_run_lane'],
         $project_log_uid_array['value']);
      }  // if (trimmed_string_not_empty ($my_log->db_error))
    } // if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] == 1)
} else {
  // Make a project log object.
  $my_log = new ProjectLog (
   $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
  // Check for errors.
  if (trimmed_string_not_empty ($my_log->db_error))
  {
    $array_error[] = $my_log->db_error;
  } else {
    // Make a project log run lane object.
    $project_log_uid_array = $my_log->project_log_uid;
    $my_log_run_lane = new ProjectLogRunLane (
     $dbconn,  $_SESSION['project_log_run_lane'],
     $project_log_uid_array['value']);
       // Add the project log values to the session variables.
      $my_log->populate_session ($dbconn);
  }  // if (trimmed_string_not_empty ($my_log->db_error))
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Project Log, ',$abbreviated_app_name,'</title>';
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
  <script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script src="library/calendar.js"
 language="javascript"
 type="text/javascript"></script>
<?php
  readfile("text_styles.css");
?>
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Project Log - ',
       $app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<br />';
  // Get the project name for the project.
  $project_name = "";
  if ($project_uid > 0)
  {
    $project_table = $_SESSION['project'];
    $result_puid = pg_query ($dbconn, "
     SELECT project_name
       FROM $project_table
      WHERE project_uid = $project_uid");
    if (!$result_puid)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $project_name = pg_fetch_result ($result_puid, 0, 0);
    }  // if (!$result_puid)
  }  // if ($project_uid > 0)
  // Get the sidebar appropriate to the application role of the user.
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
?>
 <div id="mainContent">
  <?php
    // Print out any errors.
    foreach ($array_error as $error_value)
    {
       echo '<span class="errortext">'.$error_value.'</span><br />';
    }  // if (count ($array_error) > 0)
    // Functions.
    echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
         'name="form_project_log" >';
    echo '<input type="hidden" name="process" value="1" />';
    echo '<input type="hidden" name="project_log_uid" value="',
         $_SESSION['project_log_uid'],'" />';
    echo '<h2>Manage Project Log</h2>';
    echo '<input type="submit" name="submit_update_info" ',
         'value="Update Log" ',
         'title="Update all project log information ',
         'except samples." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_update_samples" ',
         'value="Add or Update Samples" ',
         'title="Add and/or update project log samples." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_delete" ',
         'value="Delete Log" ',
         'onclick="return confirm(\'Are you sure you want to delete the project log?\');" ',
         'title="Delete project log for this project." ',
         'class="buttontext" />';
    echo '<h2>Reports</h2>';
    echo '<input type="submit" name="submit_download_log" ',
         'value="Download Log" ',
         'title="Download log as a comma-separated variable file." ',
         'class="buttontext" />';
    if ($_SESSION['app_role'] == 'dac_grants')
    {
      echo '<input type="submit" name="submit_samples_std" ',
           'value="Samples File - Standard" ',
           'title="Downloads a file in a format suitable for adding ',
           'the samples to the project with standard barcodes." ',
           'class="buttontext" />';
      echo '<input type="submit" name="submit_samples_custom" ',
           'value="Samples File-Custom" ',
           'title="Downloads a file in a format suitable for adding ',
           'the samples to the project with custom barcodes." ',
           'class="buttontext" />';
    }  // if ($_SESSION['app_role'] == 'dac_grants')
    echo '<h2>Page Navigation</h2>';
    echo '<input type="submit" name="submit_project_details" ',
         'value="Project Details" ',
         'title="Move to Project Details page." ',
         'class="buttontext" />';
    echo '<div class="displaytext" style="padding: 5px; text-align:left;">';
    // Print out project information.
    echo $my_log->display ($my_log_run_lane);
    echo '</div>';
    echo '</form>';
  ?>
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
