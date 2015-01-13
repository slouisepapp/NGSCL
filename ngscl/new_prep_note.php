<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_prep_note_info"))
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
      header("location: process_new_prep_note.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_POST['library_prep_note_name']);
      unset($_POST['creation_date']);
      unset($_POST['comments']);
      clear_prep_note_vars();
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>New Library Prep Note Info, ',$abbreviated_app_name,'</title>';
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
 onload="document.form_prep_note_info.library_prep_note_name.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center"><span class="titletext">',
       'New Library Prep Note - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<br /><br />';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  if (count($array_error) >= 1)
  {
    $error_exists = 0;
    foreach ($array_error as $error)
    {
      if (strlen(trim($error)) > 0)
      {
        $error_exists = 1;
        echo '<span class="errortext">'.$error.'</span><br />';
      }  // if (strlen(trim($error)) > 0)
    }  // foreach ($array_error as $error)
    if ($error_exists > 0) 
    {
      echo '<span class="errortext">Correct and resubmit.',
           '</span><br />';
    }  // if ($error_exists > 0) 
  }  // if (count($array_error) >= 1)
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_prep_note_info">
<input type="hidden" name="process" value="1" />
<?php
 echo '<input type="submit" name="submit_save" value="Save" ',
  'title="Save new library prep note." class="buttontext" />',
  '&nbsp;&nbsp;',
  '<input type="submit" name="submit_clear" value="Clear" ',
  'title="Clear fields." class="buttontext" /><br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    clear_prep_note_vars();
  }  // if (isset($_SESSION['errors']))
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '<b><i>* Required Fields</i></b></span></p><br />';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Library Prep Note Name:</span>';
 $input_library_prep_note_name = (isset ($_SESSION['library_prep_note_name']) ?
  input_ready ($_SESSION['library_prep_note_name']) : "");
 echo '<input type="text" name="library_prep_note_name" size="60" ',
      'class="inputtext" ',
      ' value="',$input_library_prep_note_name,'" /></p>';
  // If a creation date has already been entered, display it.
  //  Otherwise use current date.
  $creation_date = (isset ($_SESSION['creation_date']) ?
   $_SESSION['creation_date'] : date("Y-m-d"));
  echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Creation Date:</span>';
  echo '<input type="text" name="creation_date" id="creation_date" ',
       'size="10" class="inputtext" ',
       'onclick="fPopCalendar(\'creation_date\')" ',
       'value="',$creation_date,'" /></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Comments:</span><br />';
  $input_comments = (isset ($_SESSION['comments']) ?
   input_ready ($_SESSION['comments']) : "");
  echo '<textarea name="comments" cols="60" rows="2" ',
       'class="inputseriftext">',$input_comments,'</textarea></p>';
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
