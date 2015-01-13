<?php
session_start();
require_once('db_fns.php');
require_once('run_functions.php');
require_once('constants.php');
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_run_info"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_copy']))
    {
      header("location: process_copy_run.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_SESSION['new_run_name']);
    } elseif (isset($_POST['submit_run'])) {
      unset($_POST['new_run_number']);
      unset($_POST['new_run_name']);
      header("location: run.php");
      exit;
    }  //if (isset($_POST['submit_copy']))
  } else {
    unset($_SESSION['new_run_name']);
  }  // if ($_POST['process'] ==1)
} else {
  unset($_SESSION['new_run_name']);
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Copy Run Info, ',$abbreviated_app_name,'</title>';
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
 onload="document.form_run_info.new_run_name.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center">',
       '<span class="titletext">Copy Run - ',
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
 name="form_run_info">
<input type="hidden" name="process" value="1" />
<?php
 // Find the run number and name of the original run.
 $old_run_uid = $_SESSION['run_uid'];
 $_SESSION['old_run_uid'] = $old_run_uid;
 $result_run_info = pg_query ($dbconn, "
  SELECT run_number,
         run_name,
         run_type,
         run.ref_run_type_uid
    FROM run,
         ref_run_type
   WHERE run_uid = $old_run_uid AND
         run.ref_run_type_uid = ref_run_type.ref_run_type_uid");
 if (!$result_run_info)
 {
   echo pg_last_error ($dbconn);
 } else {
   $run_info = pg_fetch_assoc ($result_run_info);
   $old_run_number = $run_info['run_number'];
   $old_run_name = $run_info['run_name'];
   $run_type = $run_info['run_type'];
   $ref_run_type_uid = $run_info['ref_run_type_uid'];
 }  // if (!$result_run_info)
 echo '<input type="hidden" name="ref_run_type_uid" value="',
      $ref_run_type_uid,'" />';
 echo '<input type="hidden" name="run_type" value="',$run_type,'" />';
 echo '<h3 class="grayed_out">Original Run Number: ',
      $old_run_number,'</h3>';
 echo '<h3 class="grayed_out">Original Run Name: ',
      $old_run_name,'</h3>';
 echo '<h3 class="grayed_out">Run Type: ',
      $run_type,'</h3>';
 echo '<input type="submit" name="submit_copy" value="Copy" ',
  'title="Copy run." class="buttontext" />';
 echo '<input type="submit" name="submit_clear" value="Clear Name" ',
  'title="Clear run name." class="buttontext" />';
 echo '<input type="submit" name="submit_run" value="Quit" ',
  'onclick="return confirm(\'Run will not be copied. Continue?\');" ',
  'title="Return to Run page without copying run." class="buttontext" />';
  echo '<br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    clear_run_vars();
    $_SESSION['new_run_number'] = next_run_number (
     $dbconn, $ref_run_type_uid);
  }  // if (isset($_SESSION['errors']))
  $_SESSION['new_run_number'] = (isset ($_SESSION['new_run_number']) ?
   $_SESSION['new_run_number'] : 0);
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '<b><i>* Required Fields</i></b></span></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
       '* Run Number</span>';
  echo '<input type="text" name="new_run_number" size="4" class="inputtext" ',
       'value="',
       $_SESSION['new_run_number'],
       '" onblur="testPosIntField(this);" ',
       'title="Run number must be a positive integer" ',
       '/>&nbsp;&nbsp;';
  echo '<span class="optionaltext">Run Name</span>';
  $new_run_name = (isset ($_SESSION['new_run_name']) ?
   $_SESSION['new_run_name'] : "");
  echo '<input type="text" name="new_run_name" size="40" class="inputtext" ',
       ' value="',$new_run_name,'" /></p>';
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
