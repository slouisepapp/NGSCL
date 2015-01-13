<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue)
{
  if ($thislabel != "PHPSESSID")
  {
    $_SESSION[$thislabel] = $thisvalue;
  }  // if ($thislabel != "PHPSESSID")
}  // foreach ($_POST as $thislabel => $thisvalue)
$library_prep_note_uid = $_SESSION['library_prep_note_uid'];
//***********DELETE LIBRARY PREP NOTE***************/
// Delete all the images attached to this library prep note.
$result_delete_image = pg_query ($dbconn, "
 DELETE FROM library_prep_note_image
  WHERE library_prep_note_uid = $library_prep_note_uid");
if (!$result_delete_image)
{
  echo pg_last_error($dbconn);
  exit;
}  // if (!$result_delete_image)
// Delete all the samples attached to this library prep note.
$result_delete_sample = pg_query ($dbconn, "
 DELETE FROM library_prep_note_sample
  WHERE library_prep_note_uid = $library_prep_note_uid");
if (!$result_delete_sample)
{
  echo pg_last_error($dbconn);
  exit;
}  // if (!$result_delete_sample)
// Delete the library prep note.
$result_delete_prep_note = pg_query ($dbconn, "
 DELETE FROM library_prep_note
  WHERE library_prep_note_uid = $library_prep_note_uid");
if (!$result_delete_prep_note)
{
  echo pg_last_error($dbconn);
  exit;
}  // if (!$result_delete_prep_note)
/***********************************************************************/
unset ($_SESSION['library_prep_note_uid']);
header("location: library_prep_note.php");
?>
