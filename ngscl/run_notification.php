<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_run_notification"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
$array_to_list = array();
$array_available_address = array();
$array_all_address = array();
$array_from_display = array();
$array_from_uid = array();
$add_addresses = array();
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
  if (! $result)
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
// This function updates the notification to list.
// The input addresses are added or removed from the list
// depending on the value of the input boolean.
// A string describing the errors is returned.
// If there are no errors, the string is empty.
// *******************************************************************
function update_to_list ($dbconn, $boolean_string, array &$array_address_uid)
{
  $error = "";
  $list_value = standardize_boolean ($boolean_string);
  foreach ($array_address_uid as $addressnum => $addressvalue)
  {
    // Update database to add address to notification to list.
    $result = pg_query ($dbconn, "
     UPDATE notification_address
        SET part_of_to_list = " . $list_value . "
      WHERE notification_address_uid = $addressvalue");
    if (!$result)
    {
      $error .= "  " . pg_last_error ($dbconn);
    } else {
      if (pg_affected_rows ($result) < 1)
      {
        if ($list_value == 'TRUE')
        {
          $problem = 'added to list.';
        } else {
          $problem = 'removed from list.';
        }  // if ($list_value == 'TRUE')
        $error .= "  notification_address_uid " .
                         $addressvalue .
                         " not " .
                         $problem;
      }  // if (pg_affected_rows ($result) < 1)
    }  // if (!$result)
  }  // foreach ($array_address_uid as $addressnum => $addressvalue)
  return $error;
}  // function update_to_list ($array_address_uid, $boolean_string)
// *******************************************************************
// This function sends emails out based on the input parameters.
// *******************************************************************
function send_notification ($to, $from, $subject, $message)
{
  // Create headers based on from variable.
  $message = wordwrap ($message, 70);
  $headers = 'From: ' . $from . "\r\n" .
             'Reply-To: ' . $from . "\r\n";
  return mail ($to, $subject, $message, $headers);
}  // function send_notification ($to, $from, $subject, $message)
// **************************************************************
// This function clears the session variables for the run notification.
// **************************************************************
function clear_notification_vars()
{
  if(isset($_SESSION['run_number_name']))
    unset($_SESSION['run_number_name']);
  if(isset($_SESSION['choose_from_address']))
    unset($_SESSION['choose_from_address']);
  if(isset($_SESSION['subject_line']))
    unset($_SESSION['subject_line']);
  if(isset($_SESSION['message']))
    unset($_SESSION['message']);
}  // function clear_notification_vars
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
if ($run_uid > 0)
{
  // Get run number and name.
  $result = pg_query ($dbconn, "
   SELECT run_number || '/' || run_name,
          run_type
     FROM run,
          ref_run_type
    WHERE run_uid = $run_uid AND
          run.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
  } elseif (pg_num_rows ($result) > 0) {
    $_SESSION['run_number_name'] = pg_fetch_result ($result, 0, 0);
    $_SESSION['run_type'] = pg_fetch_result ($result, 0, 1);
  }  // if (!$result)
} else {
  $array_error[] = 'No run selected.';
  $_SESSION['run_number_name'] = "";
  $_SESSION['run_type'] = "";
}  // if ($run_uid > 0)
// Build the list of all addresses.
$array_all_address = array_of_address_data ($dbconn, $mail_server, 'ALL');
foreach ($array_all_address as $listnum => $listvalue)
{
  // Build a list of display names for all addresses.
  $array_from_display[] = $listvalue['display_name'];
  // Build a list of uids for all addresses.
  $array_from_uid[] = $listvalue['notification_address_uid'];
}  // foreach ($array_to_list as $listnum => $listvalue)
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_send']))
    {
      // Get to list from database.
      $to = "";
      $result = pg_query ($dbconn, "
       SELECT username || '@' || domain_name
         FROM notification_address
        WHERE part_of_to_list = TRUE
        ORDER BY username");
      if (!result)
      {
        $array_error[] = pg_last_error ($dbconn);
      } else {
        // Loop through the address data.
        if (pg_num_rows($result) > 0)
        {
          for ($i=0; $i < pg_num_rows($result); $i++)
          {
            $to .= "," . pg_fetch_result ($result, $i, 0);
          }  //for ($i=0; $i < pg_num_rows($result); $i++)
          $to = trim ($to, ',');
          // Get from address from session.
          $choose_from_address = $_SESSION['choose_from_address'];
          if ($choose_from_address >= 0)
          {
            $from = $array_all_address[$choose_from_address]['email_address'];
            // Get subject and message from session.
            $subject = $_SESSION['subject_line'];
            $message = $_SESSION['message'];
            // Send notification out.
            if (send_notification ($to, $from, $subject, $message))
            {
              // If there are no errors, clear notification session values.
              clear_notification_vars();
              // Return to calling page.
              if (isset ($_SESSION['calling_page']) &&
                  trimmed_string_not_empty ($_SESSION['calling_page']))
              {
                header("location: " . $_SESSION['calling_page']);
                exit;
              }  // if (isset ($_SESSION['calling_page']))
            } else {
              $array_error[] = 'Mail could not be sent.';
            }  // if (send_notification ($to, $from, $subject, $message))
          } else {
            // If there is no from address add to error array.
            $array_error[] = 'You must choose a From address.';
          }  // if ($choose_from_address >= 0)
        } else {
          // If there are no addresses in to list add to error array.
          $array_error[] = 'You must have at least one address in the '.
                           'Send Notification To list.';
          if ($_SESSION['choose_from_address'] < 0)
          {
            // If there is no from address add to error array.
            $array_error[] = 'You must choose a From address.';
          }  // if ($_SESSION['choose_from_address'] >= 0)
        }  // if (pg_num_rows($result) > 0)
      }  // if (!result)
    } elseif (isset($_POST['submit_clear'])) {
      // Clear the from, subject, and message session values.
      if(isset($_SESSION['choose_from_address']))
        unset($_SESSION['choose_from_address']);
      if(isset($_SESSION['subject_line']))
        unset($_SESSION['subject_line']);
      if(isset($_SESSION['message']))
        unset($_SESSION['message']);
    } elseif (isset($_POST['submit_manage_addresses'])) {
      // Go to manage addresses page.
      header("location: manage_addresses.php?sticky_lane=" .
             $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_go_back'])) {
      // Return to calling page.
      if (isset ($_SESSION['calling_page']) &&
          trimmed_string_not_empty ($_SESSION['calling_page']))
      {
        $calling_page = $_SESSION['calling_page'];
        unset ($_SESSION['calling_page']);
        header("location: " . $calling_page . "?sticky_lane=" .
               $_SESSION['lane_number']);
        exit;
      }  // if (isset ($_SESSION['calling_page']) &&...
    } elseif (isset($_POST['submit_add_to_list'])) {
      // Loop through addresses chosen in available email list.
      if (isset($_POST['available_address']))
      {
        $add_addresses = $_POST['available_address'];
        $add_error = update_to_list ($dbconn, 'TRUE', $add_addresses);
        if (strlen (trim ($add_error)) > 0)
        {
          $array_error[] = $add_error;
        }  // if (strlen (trim ($error)) > 0)
      }  // if (isset($_POST['available_address']))
    } elseif (isset($_POST['submit_remove_from_list'])) {
      // Loop through addresses chosen in available email list.
      if (isset($_POST['to_address']))
      {
        $remove_addresses = $_POST['to_address'];
        $add_error = update_to_list ($dbconn, 'FALSE', $remove_addresses);
        if (strlen (trim ($add_error)) > 0)
        {
          $array_error[] = $add_error;
        }  // if (strlen (trim ($error)) > 0)
      }  // if (isset($_POST['to_address']))
    }  // if (isset($_POST['submit_send']))
  }  // if ($_POST['process'] == 1)
} else {
  $_SESSION['subject_line'] = (isset ($_SESSION['run_number_name']) ?
   $_SESSION['run_number_name'] : "");
  $_SESSION['subject_line'] .= (isset ($_SESSION['run_type']) ?
   " (" . $_SESSION['run_type'] . " run)" : "");
  $_SESSION['message'] = $_SESSION['subject_line'];
  // ****
  // On first accessing this page, get the lane passed
  // from another of the set of pages that manages lanes
  // or set the lane to one.
  // ****
  if (isset($_GET) &&
      isset($_GET['sticky_lane']) &&
      $_GET['sticky_lane'] != "")
  {
    $_SESSION['lane_number'] = $_GET['sticky_lane'];
  } else {
    $_SESSION['lane_number'] = 1;
  }  // if (isset($_GET) &&
  $lane_number = $_SESSION['lane_number'];
}  // if (isset($_POST['process']))
// Build the notification to list.
$array_to_list = array_of_address_data ($dbconn, $mail_server, 'TRUE');
// Build the available email list.
$array_available_address = array_of_address_data (
 $dbconn, $mail_server, 'FALSE');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Run Notification, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 align="center"><span class="titletext">Run Notification - ',
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
  echo '<h3 class="grayed_out">Run: ',$_SESSION['run_number_name'],'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$_SESSION['run_type'],'</h3>';
?>
<br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_run_notification" >
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_send" value="Send" ',
       'title="Send email notification." class="buttontext" />';
  echo '<input type="submit" name="submit_clear" value="Clear" ',
       'title="Clear from, subject, message." class="buttontext" />';
  echo '<input type="submit" name="submit_manage_addresses" ',
       'value="Manage Addresses" ',
       'title="Manage email addresses." class="buttontext" />';
  echo '<input type="submit" name="submit_go_back" value="Go Back" ',
       'title="Return to page that acessed run notification page." ',
       'class="buttontext" />';
  echo '<br /><br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '* Required Fields</span></p>';
  echo '<br /><br />';
  echo '<table class="tableNoBorder"><tr>';
  // ****
  // To List
  // ****
  // Addresses on notification to list.
  $to_list_options = "";
  foreach ($array_to_list as $listnum => $listvalue)
  {
    $to_list_options .= '<option value="' .
                         $listvalue['notification_address_uid'] .
                         '" >' .
                         $listvalue['display_name'] .
                         '</option>';
  }  // foreach ($array_to_list as $listnum => $listvalue)
  echo '<td>';
  echo '<span class="requiredtext">* Send Notification To</span><br />';
  echo '<select id="to_list_box" name="to_address[]" multiple="multiple" ' .
       'class="inputtext" >' .
       $to_list_options .
       '</select>';
  echo '</td>';
  // Buttons to add and subtract from notification to list.
  echo '<td>';
  echo '<input type="submit" name="submit_add_to_list" value="Add" ',
       'title="Add to addresses to receive this notification." ',
       'class="buttontext" /><br />';
  echo '<input type="submit" name="submit_remove_from_list" value="Remove" ',
       'title="Remove from addresses to receive this notification." ',
       'class="buttontext" />';
  echo '</td>';
  // Addresses not on notification to list.
  $available_list_options = "";
  foreach ($array_available_address as $listnum => $listvalue)
  {
    $available_list_options .= '<option value="' .
                               $listvalue['notification_address_uid'] .
                               '" >' .
                               $listvalue['display_name'] .
                               '</option>';
  }  // foreach ($array_available_list as $listnum => $listvalue)
  echo '<td>';
  echo '<span class="optionaltext">Email List</span><br />';
  echo '<select id="available_list_box" name="available_address[]" ',
       'multiple="multiple" class="inputtext" >' .
       $available_list_options .
       '</select>';
  echo '</td>';
  echo '</tr></table>';
  // ****
  // From Address
  // ****
  echo '<br /><br /><span class="requiredtext">* From</span><br />';
  // The pull-down of addresses.
  $select_array_num = (isset ($_SESSION['choose_from_address']) ?
   $_SESSION['choose_from_address'] : 0);
  echo drop_down_array ("choose_from_address", $select_array_num,
                        "inputtext", $array_from_display,
                        "Choose from email address.", "Select an address",
                        -1, 0, 1);
  
  // ****
  // Subject Line
  // ****
  // Write the saved subject line to the text input.
  echo '<br /><br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
       'Subject&nbsp;</span>';
  $subject_line = (isset ($_SESSION['subject_line']) ?
   $_SESSION['subject_line'] : "");
  echo '<input type="text" name="subject_line" size="50" class="inputtext" ',
       ' value="',htmlentities($subject_line, ENT_COMPAT),
       '" /></p>';
  // ****
  // Message
  // ****
  // Write the saved message to the text area.
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext" >',
       'Message',
       '</span><br />';
  $message = (isset ($_SESSION['message']) ? $_SESSION['message'] : "");
  echo '<textarea name="message" cols="50" rows="4" class="inputseriftext" >',
       htmlentities($message, ENT_NOQUOTES),'</textarea></p>';
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
