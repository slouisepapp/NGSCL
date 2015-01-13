<?php
session_start();
require_once('db_fns.php');
require_once('project_functions.php');
require_once 'user_view.php';
function populate_fields ($dbconn, $project_uid)
{
  $result = pg_query($dbconn,"
   SELECT project_name,
          project.primary_investigator_uid,
          project.primary_investigator_uid,
          ref_run_type.ref_run_type_uid,
          ref_run_type.run_type,
          project.status,
          creation_date,
          project_description,
          analysis_notes,
          admin_comments,
          COALESCE (contact_uid, -1) AS contact_uid
     FROM project,
          primary_investigator,
          ref_run_type
    WHERE project_uid = $project_uid AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid AND
          project.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    return FALSE;
  } elseif (pg_num_rows ($result) > 0) {
    $row = pg_fetch_assoc ($result);
    $_SESSION['project_name'] = $row['project_name'];
    $_SESSION['choose_pi'] = $row['primary_investigator_uid'];
    $_SESSION['choose_run_type'] = $row['ref_run_type_uid'];
    $_SESSION['choose_project_status'] = $row['status'];
    $_SESSION['creation_date'] = $row['creation_date'];
    $_SESSION['choose_contact'] = $row['contact_uid'];
    $_SESSION['project_description'] = $row['project_description'];
    $_SESSION['analysis_notes'] = $row['analysis_notes'];
    $_SESSION['admin_comments'] = $row['admin_comments'];
    return TRUE;
  } else {
    return FALSE;
  }  // if (!$result)
}  // function populate_fields
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
if ($project_uid == 0)
  $_SESSION['errors'][] = "No project selected.";
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_update_project_info.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      if (!populate_fields ($dbconn, $project_uid))
        $_SESSION['errors'][] = pg_last_error ($dbconn);
    } elseif (isset($_POST['submit_project_info'])) {
      header("location: project_info.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
} elseif (!isset($_SESSION['errors'])) {
  if (!populate_fields ($dbconn, $project_uid))
    $_SESSION['errors'][] = pg_last_error ($dbconn);
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
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
<body>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_project_info">
<input type="hidden" name="process" value="1" />
<?php
 echo '<input type="hidden" name="project_uid" value="',
      $project_uid,'" />';
 echo '<input type="submit" name="submit_save" value="Save" ',
  'title="Save changes to project information." class="buttontext" />';
 echo '<input type="submit" name="submit_reset" value="Reset" ',
      'title="Restore to most recent saved changes." class="buttontext" />';
  echo '<input type="submit" name="submit_project_info" value="Quit" ',
   'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
   'title="Return to project info without saving." class="buttontext" />';
  echo '<br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
      if (!populate_fields ($dbconn, $_SESSION['project_uid']))
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  }  // if (isset($_SESSION['errors']))
if ($project_uid > 0)
{
  $run_list = run_list_for_project ($dbconn, $project_uid);
  if (strlen (trim ($run_list)) < 1)
  {
    $run_type_disabled = "";
    $run_type_title = 'Choose run type.';
  } else {
    $run_type_disabled = 'disabled="disabled"';
    $run_type_title = 'Run type cannot be changed as project ' .
                      'samples are part of a run.';
  }  // if (strlen (trim ($run_list)) < 1)
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" ><b><i>',
       '* Required Fields</i></b></span></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Primary Investigator</span>&nbsp;';
 $result = pg_query ($dbconn, "
  SELECT primary_investigator_uid, name
    FROM primary_investigator
   ORDER BY name");
 echo '<select name="choose_pi" onchange="this.form.submit();" ',
      'class="inputtext">';
 for ($i=0; $i < pg_num_rows($result); $i++)
 {
   $row_pi_list = pg_fetch_assoc ($result);
   $list_pi_uid = $row_pi_list['primary_investigator_uid'];
   $list_pi_name = $row_pi_list['name'];
   if ($list_pi_uid == $_SESSION['choose_pi'])
   {
     echo '<option value="',$list_pi_uid,'" class="inputtext" ',
          'selected="selected">',
          $list_pi_name,'</option>';
   } else {
     echo '<option value="',$list_pi_uid,'" class="inputtext">',
          $list_pi_name,'</option>';
   }  // if ($list_pi_uid ==$_SESSION['choose_pi'])
 }  // for ($i=0; $i < pg_num_rows($result); $i++)
 echo '</select></p>';
 echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Project Name:',
      '</span>';
  echo '<input type="text" name="project_name" size="60" class="inputtext" ',
       'value="',$_SESSION['project_name'],'" /></p>';
 echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Run Type</span>&nbsp;';
 $select_run_type_value = (isset ($_SESSION['choose_run_type']) ?
  $_SESSION['choose_run_type'] : "");
 echo drop_down_table ($dbconn, "choose_run_type", $select_run_type_value,
                       "inputtext", "ref_run_type",
                       "ref_run_type_uid", "run_type",
                       $run_type_title, " ",
                       "None",
                       -1, 0, 1, $run_type_disabled); 
 $active_selected = '';
 $completed_selected = '';
 $holding_selected = '';
 if ($_SESSION['choose_project_status'] == 'Active')
 {
   $active_selected = 'selected="selected"';
 } elseif ($_SESSION['choose_project_status'] == 'Completed')
 {
   $completed_selected = 'selected="selected"';
 } elseif ($_SESSION['choose_project_status'] == 'Holding')
 {
   $holding_selected = 'selected="selected"';
 }  // if ($_SESSION['choose_project_status'] == 'Active')
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Status</span>&nbsp;';
 echo '<select name="choose_project_status" class="inputtext">';
 echo '<option value="Active" ',$active_selected,'>Active</option>';
 echo '<option value="Completed" ',$completed_selected,'>Completed</option>';
 echo '<option value="Holding" ',$holding_selected,'>Holding</option>';
 echo '</select></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Creation Date:</span>';
 echo '<input type="text" name="creation_date" id="creation_date" ',
      'size="10" class="inputtext" ',
      'onclick="fPopCalendar(\'creation_date\')" ',
      'value="',$_SESSION['creation_date'],'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
      'Contact</span>&nbsp;';
 echo '<select name="choose_contact" class="inputtext">';
 if (isset ($_SESSION['choose_pi']))
 {
   $choose_pi = $_SESSION['choose_pi'];
   $result_con = pg_query ($dbconn, "
    SELECT contact_uid, name
      FROM contact
     WHERE primary_investigator_uid = $choose_pi
     ORDER BY name");
   if ($_SESSION['choose_contact'] > 0)
   {
     echo '<option value="-1" >None</option>';
   } else {
     echo '<option value="-1" selected="selected">None</option>';
   }  // if ($_SESSION['choose_contact'] > 0) {
   for ($i=0; $i < pg_num_rows($result_con); $i++)
   {
     $row_contact_list = pg_fetch_assoc ($result_con);
     $list_contact_uid = $row_contact_list['contact_uid'];
     $list_contact_name = $row_contact_list['name'];
     if ($list_contact_uid == $_SESSION['choose_contact'])
     {
       echo '<option value="',$list_contact_uid,'" selected="selected" >',
            $list_contact_name,'</option>';
     } else {
       echo '<option value="',$list_contact_uid,'" >',
            $list_contact_name,'</option>';
     }  // if ($list_contact_uid ==$_SESSION['choose_contact'])
   }  // for ($i=0; $i < pg_num_rows($result_con); $i++)
 }  // if (isset ($_SESSION['choose_pi']))
  echo '</select></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="grayed_out">',
      'Run List: ',
      $run_list,
      '</span></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Description:</span><br />';
  echo '<textarea name="project_description" cols="70" rows="2" ',
       'class="inputseriftext">',
       $_SESSION['project_description'],'</textarea></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Analysis Notes:</span><br />';
  echo '<textarea name="analysis_notes" cols="70" rows="2" ',
       'class="inputseriftext">',
       $_SESSION['analysis_notes'],'</textarea></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Administrator Comments:</span><br />';
  echo '<textarea name="admin_comments" cols="70" rows="2" ',
       'class="inputseriftext">',
       $_SESSION['admin_comments'],'</textarea></p>';
}  // if ($project_uid > 0)
 echo '</form>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
?>
</body>
</html>
