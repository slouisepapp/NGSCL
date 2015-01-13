<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_contact_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
require_once 'user_view.php';
// Initialize variables.
$contact_list = "";
$contact_like_condition = "";
function contact_delete_allowed ($dbconn, $contact_uid)
{
  $result = pg_query ($dbconn, "
   SELECT COUNT(1) AS row_count
     FROM project
    WHERE contact_uid = $contact_uid");
  if (!$result)
  {
    return FALSE;
  } elseif ($line = pg_fetch_assoc ($result)) {
    if ($line['row_count'] > 0)
    {
      return FALSE;
    } else {
      return TRUE;
    }  // if ($line['row_count'] > 0)
  } else {
    return FALSE;
  }  // if (!$result)
}  // function contact_delete_allowed
$error = "";
$dbconn = database_connect();
if (!$dbconn)
  $error = "Could not connect.";
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_create']))
    {
      header("location: new_contact.php");
      exit;
    } elseif (isset($_POST['submit_update'])) {
      if (isset ($_POST['contact_uid']) &&
          trimmed_string_not_empty ($_POST['contact_uid']))
      {
        header("location: update_contact.php");
        exit;
      } else {
        $error = 'No contact selected.';
      }  // if (isset ($_POST['contact_uid']))
    } elseif (isset($_POST['submit_delete'])) {
      if (isset ($_POST['contact_uid']))
      {
        if (contact_delete_allowed ($dbconn, $_POST['contact_uid']))
        {
          $result = pg_query ($dbconn, "
           DELETE FROM contact
            WHERE contact_uid = ".
           $_POST['contact_uid']);
          if (!$result)
          {
            $error = pg_last_error ($dbconn);
          }  // if (!$result)
        } else {
          $error = 'Contact cannot be deleted as this contact '.
                   'has projects.';
        }  // if (contact_delete_allowed ($dbconn, $_POST['contact_uid'])
      } else {
        $error = 'No contact selected.';
      }  // if (isset ($_POST['contact_uid']))
    } elseif (isset($_POST['submit_search'])) {
      $contact_like_condition = strtolower ($_POST['contact_search']);
    }  // if (isset($_POST['submit_create']))
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
}  // if (isset($_POST['process']))
if (isset($_POST['process']) || isset($_POST['submit_delete']))
{
  // If session contact does not accord with the session pi, unset the contact.
  if (isset ($_SESSION['choose_pi_contact_select']) &&
      trimmed_string_not_empty ($_SESSION['choose_pi_contact_select']) &&
      strcasecmp ($_SESSION['choose_pi_contact_select'], "Show All") != 0 &&
      isset ($_SESSION['contact_uid']) &&
      trimmed_string_not_empty ($_SESSION['contact_uid']))
  {
    $primary_investigator_uid = $_SESSION['choose_pi_contact_select'];
    $contact_uid = $_SESSION['contact_uid'];
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM $contact_view
      WHERE contact_uid = $contact_uid AND
            primary_investigator_uid = $primary_investigator_uid");
    if (!$result)
    {
      if ($line = pg_fetch_assoc ($result))
      { 
        if ($line['row_count'] < 1)
        {
          unset ($_SESSION['contact_uid']);
        }  // if ($line['row_count'] < 1)
      }  // if ($line = pg_fetch_assoc ($result))
    }  // if (!$result)
  }  // if (isset ($_SESSION['primary_investigator_uid']) &&...
}  // if (isset($_POST['process']) || isset($_POST['submit_delete']))
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo '<title>Contact, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="library/actb.js">
  </script>
<script language="javascript" type="text/javascript" src="library/common.js">
  </script>
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
  echo '<h1 style="text-align:center"><span class="titletext">Contacts - ',
       $app_name,'</span></h1>';
?>
  <!-- end #header --></div>
<?php
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  if (strlen (trim ($error)) > 0)
  {
    echo '<span class="errortext">',$error,'</span><br />';
  }  // if (strlen (trim ($error)) > 0)
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_contact_select" >
<?php
  echo '<input type="hidden" name="process" value="1" />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_create" value="New Contact" ',
         'title="Create a new contact." class="buttontext" />';
    $update_title = 'title="Update information for selected contact."';
    $delete_title = 'title="Delete selected contact."';
    // Set disabled status for update and delete buttons.
    if (isset ($_SESSION['contact_uid']) &&
        trimmed_string_not_empty ($_SESSION['contact_uid']))
    {
      if (contact_delete_allowed ($dbconn, $_SESSION['contact_uid']))
      {
        $delete_disabled_tag = '';
      }  // if (contact_delete_allowed ($dbconn, $_SESSION['contact_uid'])
    }  // if (isset ($_SESSION['contact_uid']) &&
    echo '<input type="submit" name="submit_update" value="Update Contact" ',
         $update_title,' class="buttontext" />&nbsp;';
    echo '<input type="submit" name="submit_delete" value="Delete Contact" ',
         $delete_title,' class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  // ****
  // This is the Contact search.
  // ****
  echo '<p style="text-align:left; vertical-align:middle;" >',
       'Contact',
       '<input type="text" name="contact_search" id="contact_search" ',
       'size="40" autocomplete="off" class="inputtext" />',
       '<input type="submit" value="Search" name="submit_search" ',
       'title="Contacts containing search string (not case-sensitive)." ',
       'class="buttontext" />',
       '</p>';
  if ($_SESSION['app_role'] == 'pi_user')
  {
    $primary_investigator_uid_value = $_SESSION['search_pi_uid'];
  } else {
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_contact_select']) ?
     $_SESSION['choose_pi_contact_select'] : "");
  }  // if ($_SESSION['app_role'] != 'pi_user')
  $pi_condition = where_condition (
   "$contact_view.primary_investigator_uid",
   $primary_investigator_uid_value);
  // Get all the contacts of the listed primary investigator.
  // Build where condition for primary investigator select.
  $where_addendum = "";
  $where_clause = "";
  if (strlen (trim ($pi_condition)) > 0)
  {
    $where_addendum = $pi_condition;
  }  // if (strlen (trim ($pi_condition)) > 0)
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_clause = " WHERE " . $where_addendum;
  }  // if (strlen (trim ($where_addendum)) > 0)
  $result = pg_query($dbconn,"
   SELECT name
     FROM $contact_view ".
   $where_clause.
   " ORDER BY name");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $contact_name = htmlspecialchars (
                       pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $contact_list = $contact_list . ",'" . $contact_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from pi list and enclose list in double quotes.
    $contact_list = ltrim ($contact_list, ",");
  }  // if (!$result)
  // Now add the contact search to the where condition.
  if (strlen (trim ($contact_like_condition)) > 0)
  {
    if (strlen (trim ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum .
                        " AND lower ($contact_view.name) LIKE '%" .
                        $contact_like_condition . "%'";
    } else {
      $where_addendum = " lower (contact.name) LIKE '%" .
                        $contact_like_condition . "%'";
    }  // if (strlen (trim ($status_condition)) > 0)
  }  // if (strlen (trim ($contact_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $contact_list; ?>);
  var obj = actb(document.getElementById("contact_search"),customarray);
</script>
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // Make a table for the pull-down list.
    echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
    // ****
    // This is the pull-down for Primary Investigator.
    // ****
    echo '<td style="text-align: left; margin: 2px;" ',
         'class="smallertext"><b>',
         'Primary Investigator</b><br />';
    echo drop_down_table ($dbconn, 'choose_pi_contact_select',
                          $primary_investigator_uid_value,
                          'inputrow', $primary_investigator_view, 
                          'primary_investigator_uid', 'name',
                          'Query by primary investigator.');
    echo '</td>';
    echo '</tr></tbody></table>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<table id="contact_table" border="1" class="sortable">',
       '<thead>',
       '<tr>';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<th class="sorttable_nosort" scope="col" width="30" >&nbsp;',
         '</th>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
?>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Contact</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Primary Investigator</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Email</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Phone</th>
    <th class="sorttable_alpha" scope="col" width="500"
     style="text-align:center">
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
  if (strlen ( trim ($where_addendum)) > 0)
  {
    $where_clause = "AND ".$where_addendum;
  } else {
    $where_clause = "";
  }  // if (strlen ( trim ($pi_condition)) > 0)
  $result = pg_query($dbconn,"
   SELECT contact_uid,
          $contact_view.name AS contact_name,
          $contact_view.primary_investigator_uid,
          $primary_investigator_view.name AS primary_investigator_name,
          $contact_view.email_address,
          $contact_view.phone_number,
          $contact_view.comments
     FROM $contact_view,
          $primary_investigator_view
    WHERE $contact_view.primary_investigator_uid =
           $primary_investigator_view.primary_investigator_uid ".
          $where_clause.
  " ORDER BY $primary_investigator_view.name, $contact_view.name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error,'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // If the contact is set, check its radio.
      $checked_string = '';
      if (isset ($_SESSION['contact_uid']) &&
          trimmed_string_not_empty ($_SESSION['contact_uid']))
      {
        if ($_SESSION['contact_uid'] == $row['contact_uid'])
        {
          $checked_string = 'checked="checked"';
        }  // if ($_SESSION['contact_uid']))
      }  // if (isset ($_SESSION['contact_uid']) &&...
      echo '<tr>';
      if ($_SESSION['app_role'] != 'pi_user')
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             '<input type="radio" name="contact_uid" value="',
             $row['contact_uid'],
             '" title="Selects this contact" ',
             $checked_string,
             ' /></td>';
      }  //if ($_SESSION['app_role'] != 'pi_user')
      echo '<td class="tdBlueBorder" style="text-align:center">'.
       td_ready($row['contact_name']).'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row['primary_investigator_name'],'.">',
           td_ready($row['primary_investigator_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">'.
       td_ready($row['email_address']).'</td>
       <td class="tdBlueBorder" style="text-align:center">'.
       td_ready($row['phone_number']).'</td>
       <td class="tdBlueBorder" style="text-align:left">
       <div style="width: 250px; height: 40px; overflow: auto; padding: 5px;">
       <font face="sylfaen">'.htmlentities($row['comments'], ENT_NOQUOTES).
      '</font></div></td>
       </tr>';
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
