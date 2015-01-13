<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_manage_addresses"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
$array_all_address = array();
// *******************************************************************
// This function returns an array of address data.
// The input variable $boolean_string indicates whether the addresses
// should be part of the notification to list or not.
// *******************************************************************
function array_of_address_data (
 $dbconn, $default_server, $boolean_string="ALL")
{
  $array_address_data = array();
  if ($boolean_string == "ALL")
  {
    $result = pg_query ($dbconn, "
     SELECT notification_address_uid,
            username,
            domain_name,
            name
       FROM notification_address
      ORDER BY username");
  } else {
    // Query the database for the address data.
    $boolean_string = standardize_boolean ($boolean_string);
    $result = pg_query ($dbconn, "
     SELECT notification_address_uid,
            username,
            domain_name,
            name
       FROM notification_address
      WHERE part_of_to_list = $boolean_string
      ORDER BY username");
  }  // if ($boolean_string == "ALL")
  if (!$result)
  {
    return pg_last_error ($dbconn);
  } else {
    // Loop through the address data.
    $line_number = 0;
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row = pg_fetch_assoc ($result);
      $array_address_data[$line_number]['notification_address_uid'] =
       $row['notification_address_uid'];
      $array_address_data[$line_number]['email_address'] =
         $row['username'] .  '@' . $row['domain_name'];
      if ($row['domain_name'] == $default_server)
      {
        $array_address_data[$line_number]['display_name'] = $row['username'];
      } else {
        $array_address_data[$line_number]['display_name'] =
         $row['username'] .  '@' . $row['domain_name'];
      }  // if ($row['domain_name'] == $mail_server)
      if (isset ($row['name']) && strlen(trim($row['name'])) > 0)
      {
          $array_address_data[$line_number]['display_name'] .=
           ' (' .$row['name'] . ')';
      }  // if (isset ($row['name']) && ...
      $line_number++;
    }  //for ($i=0; $i < pg_num_rows($result); $i++)
    return $array_address_data;
  }  // if (!$result)
}  // function array_of_address_data ($dbconn, $boolean_string)
// *******************************************************************
// This function returns a empty string if the username
// is acceptable and an error message otherwise.
// *******************************************************************
function validate_username ($username)
{
  // Check whether username is an empty string.
  if (strlen (trim ($username)) > 0)
  {
    // Check whether the username contains the at sign.
    $at_sign_pos = strpos ($username, "@");
    if ($at_sign_pos === false)
    {
      $error_message = "";
    } else {
      $error_message = "The username should not contain the @ character.";
    }  // if ($at_sign_pos === false)
  } else {
    $error_message = "You must enter a Username.";
  }  // if (strlen (trim ($username)) > 0)
  return $error_message;
}  // function validate_username ($username)
// *******************************************************************
// This function returns a empty string if the domain name.
// is acceptable and an error message otherwise.
// *******************************************************************
function validate_domain_name ($domain_name)
{
  // Check whether domain_name is an empty string.
  if (strlen (trim ($domain_name)) > 0)
  {
    // Check whether the domain_name contains the at sign.
    $at_sign_pos = strpos ($domain_name, "@");
    if ($at_sign_pos === false)
    {
      $error_message = "";
    } else {
      $error_message = "The domain_name should not contain the @ character.";
    }  // if ($at_sign_pos === false)
  } else {
    $error_message = "You must enter a Domain Name.";
  }  // if (strlen (trim ($domain_name)) > 0)
  return $error_message;
}  // function validate_domain_name ($domain_name)
// *******************************************************************
function clear_new_email_vars ($default_mail_server)
{
  if (isset($_SESSION['username']))
    unset ($_SESSION['username']);
  $_SESSION['domain_name'] = $default_mail_server;
  if (isset($_SESSION['name']))
    unset ($_SESSION['name']);
}  // function clear_new_email_vars ()
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_run_notification']))
    {
      // Clear the entered address.
      clear_new_email_vars ($mail_server);
      // Return to run notification page.
      header("location: run_notification.php?sticky_lane=" .
             $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_add'])) {
      $username = ddl_ready ($_POST['username']);
      $domain_name = ddl_ready ($_POST['domain_name']);
      $name = ddl_ready ($_POST['name']);
      // Check if the username and domain_name are valid.
      $temp_error = validate_username ($username);
      if (strlen (trim ($temp_error)) > 0)
      {
        $array_error[] = $temp_error;
      }  // if (strlen (trim ($temp_error)) > 0)
      $temp_error = validate_domain_name ($domain_name);
      if (strlen (trim ($temp_error)) > 0)
      {
        $array_error[] = $temp_error;
      }  // if (strlen (trim ($temp_error)) > 0)
      // Add an address to the database.
      if (count($array_error) < 1)
      {
        // Check if this user and domain name already exist.
        $result_count = pg_query ($dbconn, "
         SELECT COUNT(1)
           FROM notification_address
          WHERE username = '$username' AND
                domain_name = '$domain_name'");
        if (!$result_count)
        {
          $array_error[] = pg_last_error ($dbconn);
        } else {
          $address_count = pg_fetch_result ($result_count, 0, 0);
          if ($address_count < 1)
          {
            $name = rtrim (ltrim ($name, "("), ")");
            $result_add_address = pg_query ($dbconn, "
             INSERT INTO notification_address
              (username, domain_name, name)
             VALUES
              ('$username', '$domain_name', '$name')");
            if (!$result_add_address)
            {
              $array_error[] = pg_last_error ($dbconn);
            } else {
              // Clear the entered address.
              clear_new_email_vars ($mail_server);
            }  // if (!$result_add_address)
          } else {
            $array_error[] = $username . "@" . $domain_name .
                             " already exists.";
          }  // if ($address_count < 1)
        }  // if (!$result_count)
      }  // if (count($array_error) < 1)
    } elseif (isset($_POST['submit_clear'])) {
      // Clear the entered address.
      clear_new_email_vars ($mail_server);
    } elseif (isset($_POST['submit_remove_address'])) {
      // Remove selected addresses from the database.
      if (isset ($_POST['address']))
      {
        $remove_addresses = $_POST['address'];
        foreach ($remove_addresses as $addressnum => $addressvalue)
        {
          $result_remove = pg_query ($dbconn, "
           DELETE FROM notification_address
            WHERE notification_address_uid = $addressvalue");
          if (!$result_remove)
          {
            $array_error[] = pg_last_error ($dbconn);
          }  // if (!$result_remove)
        }  // foreach ($remove_addresses as $addressnum => $addressvalue)
      }  // if (isset ($_POST['address']))
    }  // if (isset($_POST['submit_run_notification']))
  }  // if ($_POST['process'] == 1)
} else {
  $_SESSION['domain_name'] = $mail_server;
  // ****
  // On first accessing this page, get the lane passed
  // from another of the set of pages that manages lanes
  // or set the lane to one.
  // ****
  if (isset($_GET) &&
      isset($_GET['sticky_lane']) &&
      trimmed_string_not_empty ($_GET['sticky_lane']))
  {
    $_SESSION['lane_number'] = $_GET['sticky_lane'];
  } else {
    $_SESSION['lane_number'] = 1;
  }  // if (isset($_GET) &&...
  $lane_number = $_SESSION['lane_number'];
}  // if (isset($_POST['process']))
// Build the list of all addresses.
$array_all_address = array_of_address_data ($dbconn, $mail_server, 'ALL');
foreach ($array_all_address as $listnum => $listvalue)
{
  // Build a list of display names for all addresses.
  $array_from_display[] = $listvalue['display_name'];
  // Build a list of uids for all addresses.
  $array_from_uid[] = $listvalue['notification_address_uid'];
}  // foreach ($array_to_list as $listnum => $listvalue)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Manage Addresses, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 align="center"><span class="titletext">Manage Addresses - ',
       $app_name,'</span></h1>';
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
  foreach ($array_error as $error)
  {
    if (strlen (trim ($error)) > 0)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // if (strlen (trim ($error)) > 0)
  }  // foreach ($array_error as $error)
?>
<br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_manage_addresses" >
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_run_notification" ',
       'value="Run Notification" ',
       'title="Return to run notification page." class="buttontext" />';
  echo '<hr />';
  echo '<input type="submit" name="submit_add" value="Add" ',
       'title="Add email address." class="buttontext" />';
  echo '<input type="submit" name="submit_clear" value="Clear" ',
       'title="Clear email address." class="buttontext" /><br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '* Required Fields</span></p>';
  // Username
  echo '<br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '* Email Username (address before @)</span>&nbsp;&nbsp;';
  $username = (isset ($_SESSION['username']) ?
   $_SESSION['username'] : "");
  echo '<input type="text" name="username" size="30" class="inputtext" ',
       ' value="',htmlentities($username, ENT_QUOTES),
       '" /></p>';
  // Domain Name
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '* Email Domain Name (address after @)</span>&nbsp;&nbsp;';
  $domain_name = (isset ($_SESSION['domain_name']) ?
   $_SESSION['domain_name'] : "");
  echo '<input type="text" name="domain_name" size="30" class="inputtext" ',
       ' value="',htmlentities($domain_name, ENT_QUOTES),
       '" /></p>';
  // Plain language name.
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
       'Name (last name, first name)</span>&nbsp;&nbsp;';
  $name = (isset ($_SESSION['name']) ?
   $_SESSION['name'] : "");
  echo '<input type="text" name="name" size="40" class="inputtext" ',
       ' value="',htmlentities($name, ENT_COMPAT),
       '" /></p><br />';
  echo '<hr />';
  echo '<table class="tableNoBorder"><tr>';
  // ****
  // To List
  // ****
  // Addresses on notification to list.
  $address_options = "";
  foreach ($array_all_address as $addressnum => $addressvalue)
  {
    $address_options .= '<option value="' .
                         $addressvalue['notification_address_uid'] .
                         '" >' .
                         $addressvalue['display_name'] .
                         '</option>';
  }  // foreach ($array_all_address as $addressnum => $addressvalue)
  echo '<td>';
  echo '<span class="optionaltext">Email List</span><br />';
  echo '<select id="address_box" name="address[]" multiple="multiple" ' .
       'class="inputtext" >' .
       $address_options .
       '</select>';
  echo '</td>';
  // Button to remove addresses.
  echo '<td>';
  echo '<input type="submit" name="submit_remove_address" value="Remove" ',
       'onclick="return confirm(\'Are you sure you want to remove these addresses?\');" ',
       'title="Remove selected addresses from the email list." ',
       'class="buttontext" />';
  echo '</td>';
  echo '</tr></table>';
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
