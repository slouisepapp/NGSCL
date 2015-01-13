<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_contact_info")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_new_contact.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_POST['last_name']);
      unset($_POST['first_name']);
      unset($_POST['email_address']);
      unset($_POST['phone_number']);
      unset($_POST['comments']);
      clear_contact_vars();
    } elseif (isset($_POST['submit_contact'])) {
      clear_contact_vars();
      header("location: contact.php");
      exit;
    }  // if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] == 1)
} elseif (!isset($_SESSION['errors'])) {
  clear_contact_vars();
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
  echo '<title>New Contact, ',$abbreviated_app_name,'</title>';
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
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Create Contact - ',
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
  name="form_contact_info" >
 <input type="hidden" name="process" value="1" />
<?php
 echo '<input type="submit" name="submit_save" value="Save" ',
  'title="Save new contact." class="buttontext" />';
 echo '<input type="submit" name="submit_clear" value="Clear" ',
  'title="Clear fields." class="buttontext" />';
 echo '<input type="submit" name="submit_contact" value="Quit" ',
   'onclick="return confirm(\'New contact will not be created. Continue?\');" ',
   'title="Return to Contact page without saving." class="buttontext" />';
 echo '<br /><br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.htmlentities($error, ENT_NOQUOTES).
           '</span><br />';
  }  // if (isset($_SESSION['errors']))
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '* Required Fields</span></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '* Primary Investigator</span>&nbsp;';
 $select_pi_value = (isset ($_SESSION['choose_pi']) ?
  $_SESSION['choose_pi']: "");
 echo drop_down_table ($dbconn, "choose_pi", $select_pi_value,
                       "inputtext", "primary_investigator",
                       "primary_investigator_uid", "name",
                       "Choose primary investigator.",
                       " ", "None", -1); 
 echo '</p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '* Last Name</span>';
 $input_last_name = (isset ($_SESSION['last_name']) ?
  input_ready ($_SESSION['last_name']) : "");
 echo '<input type="text" name="last_name" size="50" class="inputtext" ',
      ' value="',$input_last_name,'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
      'First Name</span>';
 $input_first_name = (isset ($_SESSION['first_name']) ?
  input_ready ($_SESSION['first_name']) : "");
 echo '<input type="text" name="first_name" size="50" class="inputtext" ',
      ' value="',$input_first_name,'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
      'Email Address</span>';
 $input_email_address = (isset ($_SESSION['email_address']) ?
  input_ready ($_SESSION['email_address']) : "");
 echo '<input type="text" name="email_address" size="50" class="inputtext" ',
      ' value="',$input_email_address,'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
      'Phone Number</span>';
 $input_phone_number = (isset ($_SESSION['phone_number']) ?
  input_ready ($_SESSION['phone_number']) : "");
 echo '<input type="text" name="phone_number" size="50" class="inputtext" ',
      ' value="',$input_phone_number,'" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
      'Comments</span><br />';
 $input_comments = (isset ($_SESSION['comments']) ?
  input_ready ($_SESSION['comments']) : "");
 echo '<textarea name="comments" cols="50" rows="4" class="inputseriftext" >',
      $input_comments,'</textarea></p>';
 echo '</form>';
 echo '</div>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
?>
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
