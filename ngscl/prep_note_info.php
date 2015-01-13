<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
$error_message = "";
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_update']))
    {
      // Put everything from post into session.
      foreach ($_POST as $thislabel => $thisvalue)
      {
        if (($thislabel != "PHPSESSID") &&
            ($thislabel != "form_prep_note_info"))
        {
          $_SESSION[$thislabel] = $thisvalue;
        }
      }  // foreach ($_POST as $thislabel => $thisvalue)
      header("location: update_prep_note_info.php");
      exit;
    }  //if (isset($_POST['submit_update']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
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

<body>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_prep_note_info">
<input type="hidden" name="process" value="1" />
<?php
$library_prep_note_uid = (isset ($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
if ($library_prep_note_uid > 0)
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
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    $row_meta = pg_fetch_assoc ($result);
    echo '<br /><br />';
    echo '<span align="center" class="displaytext">',
         '<b>Library Prep Note Name:</b> ',
         $row_meta['library_prep_note_name'],
         '</span><br />';
    echo '<span align="center" class="displaytext">',
         '<b>Creation Date:</b> ',
         $row_meta['creation_date'],
         '</span><br />';
    echo '<div align="center" style="font-family: times, serif; color:blue; ',
         'font-size:small; width: 450px; height: 40px; ',
         'overflow: auto; padding: 5px;">',
         '<b>Comments:</b> ',
         td_ready ($row_meta['comments']),
         '</div>';
  }  // if (!$result)
} else {
  echo '<span class="errortext">No library prep note.</span><br />';
}  // if ($library_prep_note_uid > 0)
 echo '<input type="submit" name="submit_update" value="Update" ',
  'title="Update values of library prep note information." class="buttontext"/>';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  }  // if (isset($_SESSION['errors']))
?>
</form>
</body>
</html>
