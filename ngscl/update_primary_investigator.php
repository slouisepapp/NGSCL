<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
function populate_fields ($dbconn, $pi_uid)
{
  $result = pg_query ($dbconn, "
   SELECT name, status, email_address, phone_number, comments
     FROM primary_investigator
    WHERE primary_investigator_uid = $pi_uid");
  if (!$result)
  {
    return FALSE;
  } elseif (pg_num_rows ($result) > 0) {
    $row = pg_fetch_assoc ($result);
    $name_pieces = explode (",", $row['name'], 2);
    $last_name = trim ($name_pieces[0]);
    $first_name = trim ($name_pieces[1]);
    $_SESSION['last_name'] = $last_name;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['choose_primary_investigator_status'] = $row['status'];
    $_SESSION['email_address'] = $row['email_address'];
    $_SESSION['phone_number'] = $row['phone_number'];
    $_SESSION['comments'] = $row['comments'];
    return TRUE;
  } else {
    return FALSE;
  }  // if (!$result)
}  // function populate_fields
$dbconn = database_connect();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_pi_info")) {
    $_SESSION[$thislabel] = $thisvalue;
   }  // if (($thislabel != "PHPSESSID") &&...
}  // foreach ($_POST as $thislabel => $thisvalue)
$primary_investigator_uid = (isset ($_SESSION['primary_investigator_uid']) ?
 $_SESSION['primary_investigator_uid'] : 0);
if ($primary_investigator_uid == 0)
  $_SESSION['errors'][] = "No primary investigator selected.";
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_update_primary_investigator.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      if (!populate_fields ($dbconn, $primary_investigator_uid))
        $_SESSION['errors'][] = $pg_last_error ($dbconn);
    } elseif (isset($_POST['submit_pi'])) {
      clear_pi_vars();
      header("location: primary_investigator.php");
      exit;
    }  // if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] == 1)
}  elseif (!isset($_SESSION['errors'])) {
  if (!populate_fields ($dbconn, $primary_investigator_uid))
    $_SESSION['errors'][] = $pg_last_error ($dbconn);
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Primary Investigator, ',$abbreviated_app_name,'</title>';
?>
<script type="text/javascript" src="sortabletable.js"></script>
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
  echo '<h1 align="center"><span class="titletext">',
       'Update Primary Investigator - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<br /><br />';
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
  name="form_pi_info" >
 <input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_save" value="Save" ',
   'title="Save updates to primary investigator." class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
       'title="Restore to most recent saved changes." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_pi" value="Quit" ',
   'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
   'title="Return to Primary Investigator page without saving." ',
   'class="buttontext" />';
  echo '<br /><br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">',htmlentities($error, ENT_NOQUOTES),
           '</span><br />';
  }  // if (isset($_SESSION['errors']))
 if ($primary_investigator_uid > 0)
 {
   echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
        '* Required Fields</span></p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
        '* Last Name</span>';
   echo '<input type="text" name="last_name" size="50" class="inputtext" ',
        ' value="',htmlentities($_SESSION['last_name'], ENT_NOQUOTES),'" /></p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
        'First Name</span>';
   echo '<input type="text" name="first_name" size="50" class="inputtext" ',
        ' value="',htmlentities($_SESSION['first_name'], ENT_NOQUOTES),
        '" /></p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
        '* Status</span>&nbsp;';
   $select_value = $_SESSION['choose_primary_investigator_status'];
   echo drop_down_array ("choose_primary_investigator_status", $select_value,
                         "inputtext", $array_primary_investigator_status_values,
                         "Choose primary investigator status.",
                         "Active", "Active", 1);
   echo '</p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
        'Email Address</span>';
   echo '<input type="text" name="email_address" size="50" class="inputtext" ',
        ' value="',htmlentities($_SESSION['email_address'], ENT_NOQUOTES),
        '" /></p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
        'Phone Number</span>';
   echo '<input type="text" name="phone_number" size="50" class="inputtext" ',
        ' value="',htmlentities($_SESSION['phone_number'], ENT_NOQUOTES),
        '" /></p>';
   echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
        'Comments</span><br />';
   echo '<textarea name="comments" cols="50" rows="4" class="inputseriftext" >',
        htmlentities($_SESSION['comments'], ENT_NOQUOTES),'</textarea></p>';
 }  // if ($primary_investigator_uid > 0)
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
