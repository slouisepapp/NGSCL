<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_pi_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
// Read in php functions and constants.
require_once('db_fns.php');
require_once('constants.php');
require_once 'user_view.php';
// Initialize variables.
$pi_list = "";
$pi_like_condition = "";
$primary_investigator_uid = (isset ($_SESSION['primary_investigator_uid']) ?
 $_SESSION['primary_investigator_uid'] : 0);
function pi_delete_allowed ($dbconn, $primary_investigator_uid)
{
  $result = pg_query ($dbconn, "
   SELECT COUNT(1) AS row_count
     FROM project
    WHERE primary_investigator_uid = $primary_investigator_uid");
  if (!$result)
  {
    return FALSE;
  } elseif ($line = pg_fetch_assoc($result)) {
    if ($line['row_count'] > 0)
    {
      return FALSE;
    } else {
      return TRUE;
    }  // if ($line['row_count'] > 0)
  } else {
    return FALSE;
  }  // if (!$result)
}  // function pi_delete_allowed
$dbconn = database_connect();
$error = "";
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_create']))
    {
      header("location: new_primary_investigator.php");
      exit;
    } elseif (isset($_POST['submit_update'])) {
      if ($primary_investigator_uid > 0)
      {
        header("location: update_primary_investigator.php");
        exit;
      } else {
          $error = 'No primary investigator selected.';
      }  // if ($primary_investigator_uid > 0)
    } elseif (isset($_POST['submit_delete'])) {
      if ($primary_investigator_uid > 0)
      {
        if (pi_delete_allowed ($dbconn, $primary_investigator_uid))
        {
          // Set contact for this primary investigator to null in projects.
          $result_update = pg_query ($dbconn, "
           UPDATE project
              SET contact_uid = NULL
            WHERE contact_uid
               IN (SELECT contact_uid
                     FROM contact
                    WHERE primary_investigator_uid =
                           $primary_investigator_uid)");
          if (!$result_update)
          {
            $error = pg_last_error ($dbconn);
          } else {
            // First delete any contact info.
            $result = pg_query ($dbconn, "
             DELETE FROM contact 
              WHERE primary_investigator_uid = $primary_investigator_uid");
            if (!$result)
            {
               $error = pg_last_error ($dbconn);
            } else {
              // Now delete from primary_investigator table.
              $result = pg_query ($dbconn, "
               DELETE FROM primary_investigator
                WHERE primary_investigator_uid = ".
               $primary_investigator_uid);
              if (!$result)
              {
                $error = pg_last_error ($dbconn);
              }  // if (!$result)
            }  // if (!$result)
          }  // if (!$result_update)
        } else {
          $error = 'Primary investigator cannot be deleted '.
                   'as this PI has projects.';
        }  // if (pi_delete_allowed ($dbconn, $primary_investigator_uid))
      } else {
          $error = 'No primary investigator selected.';
      }  // if ($primary_investigator_uid > 0)
    } elseif (isset($_POST['submit_search'])) {
      $pi_like_condition = strtolower ($_POST['pi_search']);
    }  // if (isset($_POST['submit_create']))
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Primary Investigator, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 style="text-align:center"><span class="titletext">',
       'Primary Investigator - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
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
}  // if (strlen (trim $error)) > 0)
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_pi_select" >
<input type="hidden" name="process" value="1" />
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_create" value="New PI" ',
         'title="Create a new primary investigator." class="buttontext" />';
    $update_title = 'title="Update information for selected '.
                    'primary investigator."';
    $delete_title = 'title="Delete selected primary investigator."';
    echo '&nbsp;<input type="submit" name="submit_update" value="Update PI" ',
         $update_title,' class="buttontext" />';
    echo '&nbsp;<input type="submit" name="submit_delete" value="Delete PI" ',
         $delete_title,' class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  // Make a table for the pull-down lists.
  echo '<br /><br />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // ****
    // This is the Primary Investigator search.
    // ****
    echo '<span style="text-align:left; vertical-align:middle;" >',
         'Primary Investigator',
         '<input type="text" name="pi_search" id="pi_search" size="30" ',
         'autocomplete="off" class="inputtext" />',
         '<input type="submit" value="Search" name="submit_search" ',
         'class="buttontext" title="Primary investigators ',
         'containing search string (not case-sensitive)."/></span>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  $status_value = (isset (
   $_SESSION['choose_pi_status_primary_investigator_select']) ?
   $_SESSION['choose_pi_status_primary_investigator_select'] : "");
  $status_condition = where_condition ($primary_investigator_view . ".status",
                                       $status_value, 1);
  // Get all the primary investigators of the listed status.
  // Build where condition for primary investigator select.
  $where_addendum = "";
  if (strlen (trim ($status_condition)) > 0)
  {
    $where_addendum = " WHERE " . $status_condition;
  }  // if (strlen (trim ($status_condition)) > 0)
  $result = pg_query($dbconn,"
   SELECT name
     FROM $primary_investigator_view ".
   $where_addendum.
   " ORDER BY name");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $name = htmlspecialchars (
               pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $pi_list = $pi_list . ",'" . $name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from pi list and enclose list in double quotes.
    $pi_list = ltrim ($pi_list, ",");
  }  // if (!$result)
  // Now add the primary investigator search to the where condition.
  if (strlen (trim ($pi_like_condition)) > 0)
  {
    if (strlen (trim ($status_condition)) > 0)
    {
      $where_addendum = $where_addendum .
                        " AND lower ($primary_investigator_view.name) LIKE '%" .
                        $pi_like_condition . "%'";
    } else {
      $where_addendum = " WHERE lower ($primary_investigator_view.name) " .
                        "LIKE '%" .
                        $pi_like_condition . "%'";
    }  // if (strlen (trim ($status_condition)) > 0)
  }  // if (strlen (trim ($pi_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $pi_list; ?>);
  var obj = actb(document.getElementById("pi_search"),customarray);
</script>
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // ****
    // This is the pull-down for Sample Status.
    // ****
    echo '<table id="pull_down_table" class="tableNoBorder"><tbody>';
    echo '<tr><td style="text-align: left; margin: 2px;" ',
         'class="smallertext"><b>',
         'Status</b><br />';
    echo drop_down_array ('choose_pi_status_primary_investigator_select',
                          $status_value, 'inputrow',
                          $array_primary_investigator_status_values,
                          'Query by primary investigator status.');
    echo '</td></tr>';
    echo '</tbody></table>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<table id="primary_investigator_table" border="1" class="sortable">',
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
    Primary Investigator</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Status</th>
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
  $set_pi_in_query = 0;
  $result = pg_query($dbconn,"
   SELECT primary_investigator_uid, name, status,
          email_address, phone_number, comments
     FROM $primary_investigator_view ".
   $where_addendum.
   " ORDER BY name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // If the primary investigator is set, check its radio.
      $checked_string = '';
      if (isset ($_SESSION['primary_investigator_uid']))
      {
        if ($_SESSION['primary_investigator_uid'] == 
            $row['primary_investigator_uid'])
        {
          $checked_string = 'checked="checked"';
          $set_pi_in_query = 1;
        }  // if ($_POST['primary_investigator_uid']))
      }  // if (isset ($_POST['primary_investigator_uid']))
      echo '<tr>';
      if ($_SESSION['app_role'] != 'pi_user')
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             '<input type="radio" name="primary_investigator_uid" value="',
             $row['primary_investigator_uid'],
             '" title="Selects this primary investigator" ',
             $checked_string,
             ' /></td>';
      }  // if ($_SESSION['app_role'] != 'pi_user')
      echo '<td class="tdBlueBorder" style="text-align:center">'.
       td_ready($row['name']).'</td>
       <td class="tdBlueBorder" style="text-align:center">'.
       td_ready($row['status']).'</td>
       <td class="tdBlueBorder" style="text-align:center">'.
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
  // If the session primary_investigator is not in the table, unset it.
  if ($set_pi_in_query == 0)
  {
    unset ($_SESSION['primary_investigator_uid']);
  }  // if ($set_pi_in_query == 0)
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
