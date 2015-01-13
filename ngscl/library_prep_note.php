<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_prep_note_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
// Initialize variables.
$prep_note_list = "";
$prep_note_like_condition = "";
$dbconn = database_connect();
$error = "";
$library_prep_note_uid = (isset ($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_view']))
    {
      if ($library_prep_note_uid > 0)
      {
        header("location: prep_note_details.php");
        exit;
      } else {
        $error = 'No library prep note selected.';
      }  // if ($library_prep_note_uid > 0)
    } elseif (isset($_POST['submit_new_prep_note'])) {
      header("location: new_prep_note.php");
      exit;
    } elseif (isset($_POST['submit_delete_prep_note'])) {
      if ($library_prep_note_uid > 0)
      {
        header("location: process_delete_prep_note.php");
        exit;
      } else {
        $error = 'No library prep note selected.';
      }  // if ($library_prep_note_uid > 0)
    } elseif (isset($_POST['submit_search'])) {
      $prep_note_like_condition = strtolower ($_POST['prep_note_search']);
    }  // if (isset($_POST['submit_view']))
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo '<title>Library Prep Note, ',$abbreviated_app_name,'</title>';
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
       'Library Prep Note - ',$app_name,'</span></h1>';
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
  }  // if (strlen (trim ($error)) > 0)
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_prep_note_select" >
<input type="hidden" name="process" value="1" />
<?php
// Set disabled status for view detail and update buttons.
$delete_title = 'title="Delete library prep note."';
$view_title = 'title="View library prep note detail, image, and samples."';
if ($library_prep_note_uid > 0)
{
  $view_disabled_tag = '';
  $delete_disabled_tag = '';
}  // if ($library_prep_note_uid > 0)
echo '<input type="submit" name="submit_view" value="Prep Note Details" ',
     $view_title,' class="buttontext" />';
echo '<input type="submit" name="submit_new_prep_note" ',
     'value="New Prep Note" ',
     'title="Create new library prep note." class="buttontext" />';
echo '<input type="submit" name="submit_delete_prep_note" value="Delete" ',
     'onclick="return confirm(\'Are you sure you want to delete the prep note?\');" ',
     $delete_title,' class="buttontext" /><br /><br />';
  // ****
  // This is the Library Prep Note search.
  // ****
  echo '<span style="text-align:left; vertical-align:middle;" >',
       'Library Prep Note',
       '<input type="text" name="prep_note_search" ',
       'id="prep_note_search" size="30" ',
       'autocomplete="off" class="inputtext" />',
       '<input type="submit" value="Search" name="submit_search" ',
       'title="Library Prep Notes containing search string ',
       '(not case-sensitive)." ',
       'class="buttontext" /></span>';
  $where_addendum = "";
  $result = pg_query($dbconn,"
   SELECT library_prep_note_name
     FROM library_prep_note
    ORDER BY library_prep_note_name");
  if (!$result)
  {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $library_prep_note_name = htmlspecialchars (
                                 pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $prep_note_list = $prep_note_list . ",'" . $library_prep_note_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from prep note list and enclose list in double quotes.
    $prep_note_list = ltrim ($prep_note_list, ",");
  }  // if (!$result)
  // Now create the where condition.
  if (strlen (trim ($prep_note_like_condition)) > 0)
  {
    $where_addendum = " WHERE lower (".
                      "library_prep_note.library_prep_note_name) LIKE '%" .
                      $prep_note_like_condition . "%'";
  }  // if (strlen (trim ($prep_note_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $prep_note_list; ?>);
  var obj = actb(document.getElementById("prep_note_search"),customarray);
</script>
<br /><br />
<table id="prep_note_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_nosort" scope="col" width="30" >&nbsp;
    </th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Library Prep Note</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Creation Date</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
  $result = pg_query($dbconn,"
   SELECT library_prep_note_uid,
          library_prep_note_name,
          creation_date,
          comments
     FROM library_prep_note".
    $where_addendum.
  " ORDER BY to_char (creation_date, 'YYYY-MM-DD'), library_prep_note_name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // If the library_prep_note is set, check its radio.
      $checked_string = '';
      if ($library_prep_note_uid > 0)
      {
        if ($library_prep_note_uid == $row['library_prep_note_uid'])
        {
          $checked_string = 'checked="checked"';
        }  // if ($library_prep_note_uid == $row['library_prep_note_uid'])
      }  // if ($library_prep_note_uid > 0)
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<input type="radio" name="library_prep_note_uid" value="',
           $row['library_prep_note_uid'],
           '" title="Selects this library prep note." ',
           $checked_string,
           ' /></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" ',
           'onclick="view_prep_noteWindow(\'',
           $row['library_prep_note_uid'],'\');" ',
           'title="View library prep note." >',
           td_ready($row['library_prep_note_name']),
           '</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['creation_date']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 200px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row['comments']),
           '</font></div></td>';
      echo '</tr>';
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
  echo '<p style="text-align:center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
