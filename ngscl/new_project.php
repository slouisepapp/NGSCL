<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_project_info"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_new_project.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_POST['choose_pi']);
      unset($_POST['project_name']);
      unset($_POST['choose_project_status']);
      unset($_POST['choose_run_type']);
      unset($_POST['creation_date']);
      unset($_POST['project_description']);
      unset($_POST['analysis_notes']);
      unset($_POST['admin_comments']);
      unset($_POST['choose_contact']);
      clear_project_vars();
    } elseif (isset($_POST['submit_project'])) {
      clear_project_vars();
      header("location: project.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Create Project Info, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script type="text/javascript">
 var sundayFirst = true;
</script>
<script src="library/calendar.js"
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

</head>
<body class="twoColElsLtHdr"
 onload="document.form_project_info.project_name.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center">',
       '<span class="titletext">Create Project - ',
       $app_name,'</span></h1>';
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
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_project_info">
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_save" value="Save" ',
   'title="Save new project." class="buttontext" />';
  echo '<input type="submit" name="submit_clear" value="Clear" ',
   'title="Clear fields." class="buttontext" />';
  echo '<input type="submit" name="submit_project" value="Quit" ',
   'onclick="return confirm(\'New project will not be created. Continue?\');" ',
   'title="Return to Project page without saving." class="buttontext" />';
  echo '<br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    clear_project_vars();
  }  // if (isset($_SESSION['errors']))
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '<b><i>* Required Fields</i></b></span></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Project Name</span>';
 $input_project_name = (isset ($_SESSION['project_name']) ?
  input_ready ($_SESSION['project_name']) : "");
 echo '<input type="text" name="project_name" size="60" class="inputtext" ',
      ' value="',$input_project_name,'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Primary Investigator</span>&nbsp;';
 $select_pi_value = (isset ($_SESSION['choose_pi']) ?
  $_SESSION['choose_pi'] : "");
 echo drop_down_table ($dbconn, "choose_pi", $select_pi_value,
                       "inputtext", "primary_investigator",
                       "primary_investigator_uid", "name",
                       "Choose primary investigator.",
                       " ", "None", -1); 
 echo '</p>';
 echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Run Type</span>&nbsp;';
 $select_run_type_value = (isset ($_SESSION['choose_run_type']) ?
  $_SESSION['choose_run_type'] : "");
 echo drop_down_table ($dbconn, "choose_run_type", $select_run_type_value,
                       "inputtext", "ref_run_type",
                       "ref_run_type_uid", "run_type",
                       "Choose run type.", " ",
                       "None", -1);
 echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Status</span>&nbsp;';
 $select_value = (isset ($_SESSION['choose_project_status']) ?
  $_SESSION['choose_project_status'] : "");
 echo drop_down_array ("choose_project_status", $select_value,
                       "inputtext", $array_project_status_values,
                       "Choose project status.",
                       "Active", "Active", 1);
 echo '</p>';
  // If a creation date has already been entered, display it.
  //  Otherwise use current date.
  $creation_date = (isset ($_SESSION['creation_date']) ?
    $_SESSION['creation_date'] : date("Y-m-d"));
  echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Creation Date</span>';
  echo '<input type="text" name="creation_date" id="creation_date" ',
       'size="10" class="inputtext" value="',
       $creation_date,
       '" onclick="fPopCalendar(\'creation_date\')" ',
       ' /></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Contact</span>&nbsp;';
  $pi_condition = where_condition (
   "primary_investigator_uid", $select_pi_value, 0, "None");
  // ****
  // This is the pull-down for Contact.
  // ****
  $contact_uid_value = (isset ($_SESSION['choose_contact']) ?
   $_SESSION['choose_contact'] : "");
  // Limit the contacts to the selected pi.
  if (strlen (trim ($pi_condition)) > 0)
  {
    $where_clause = " WHERE ".$pi_condition;
  } else {
    $where_clause = " WHERE 1 = 2";
  }  // if (strlen (trim ($pi_condition))
  echo drop_down_table ($dbconn, 'choose_contact', $contact_uid_value,
                        'inputtext', 'contact',
                        'contact_uid', 'name',
                        'Choose contact.',
                        $where_clause, 'None', -1);
  echo '</p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Description:</span><br />';
  $input_project_description = (isset ($_SESSION['project_description']) ?
   input_ready ($_SESSION['project_description']) : "");
  echo '<textarea name="project_description" cols="60" rows="2" ',
       'class="inputseriftext">',$input_project_description,
       '</textarea></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Analysis Notes:</span><br />';
  $input_analysis_notes = (isset ($_SESSION['analysis_notes']) ?
   input_ready ($_SESSION['analysis_notes']) : "");
  echo '<textarea name="analysis_notes" cols="60" rows="2" ',
       'class="inputseriftext">',$input_analysis_notes,'</textarea></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Administrator Comments:</span><br />';
  $input_admin_comments = (isset ($_SESSION['admin_comments']) ?
   input_ready ($_SESSION['admin_comments']) : "");
  echo '<textarea name="admin_comments" cols="60" rows="2" ',
       'class="inputseriftext">',$input_admin_comments,'</textarea></p>';
 echo '</form>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
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
