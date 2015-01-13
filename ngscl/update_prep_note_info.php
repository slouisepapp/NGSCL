<?php
session_start();
require_once('db_fns.php');
function populate_fields ($dbconn, $library_prep_note_uid)
{
  $result = pg_query($dbconn,"
   SELECT library_prep_note_uid,
          library_prep_note_name,
          creation_date,
          comments
     FROM library_prep_note
    WHERE library_prep_note_uid = $library_prep_note_uid");
  if (!$result)
  {
    return FALSE;
  } elseif (pg_num_rows ($result) > 0) {
    $row = pg_fetch_assoc ($result);
    $_SESSION['library_prep_note_name'] = $row['library_prep_note_name'];
    $_SESSION['creation_date'] = $row['creation_date'];
    $_SESSION['comments'] = $row['comments'];
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
$library_prep_note_uid = (isset ($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_update_prep_note_info.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      if (!populate_fields ($dbconn, $library_prep_note_uid))
        $_SESSION['errors'][] = pg_last_error ($dbconn);
    } elseif (isset($_POST['submit_prep_info'])) {
      header("location: prep_note_info.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
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
 name="form_prep_note_info">
<input type="hidden" name="process" value="1" />
<?php
 echo '<input type="hidden" name="library_prep_note_uid" value="',
      $library_prep_note_uid,'" />';
 echo '<input type="submit" name="submit_save" value="Save" ',
      'title="Save changes to library prep note information." ',
      'class="buttontext" />';
 echo '<input type="submit" name="submit_reset" value="Reset" ',
      'title="Restore to most recent saved changes." ',
      'class="buttontext" />';
 echo '<input type="submit" name="submit_prep_info" value="Quit" ',
      'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
      'title="Return to Library Prep Note info without saving." ',
      'class="buttontext" />';
  echo '<br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
      if (!populate_fields ($dbconn, $library_prep_note_uid))
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  }  // if (isset($_SESSION['errors']))
if ($library_prep_note_uid > 0)
{
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="requiredtext" ><b><i>',
       '* Required Fields</i></b></span></p>';
 echo '</p>';
 echo '<p style="text-align: left; margin: 2px;">',
      '<span class="requiredtext">* Library Prep Note Name:',
      '</span>';
  echo '<input type="text" name="library_prep_note_name" size="60" ',
       'class="inputtext" ',
       'value="',
       htmlentities($_SESSION['library_prep_note_name'], ENT_NOQUOTES),
       '" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Creation Date:</span>';
 echo '<input type="text" name="creation_date" id="creation_date" ',
      'size="10" class="inputtext" ',
      'onclick="fPopCalendar(\'creation_date\')" ',
       'value="',
       htmlentities($_SESSION['creation_date'], ENT_NOQUOTES),
       '" /></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
      'Comments:</span><br />';
 echo '<textarea name="comments" cols="80" rows="2" ',
      'class="inputseriftext">',
      htmlentities($_SESSION['comments'], ENT_NOQUOTES),'</textarea></p>';
}  // if ($library_prep_note_uid > 0)
 echo '</form>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
?>
</body>
</html>
