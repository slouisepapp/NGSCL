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
//***********DELETE SAMPLES***************/
// Delete all the samples for this project.
foreach ($_SESSION['sample_uid_delete'] as $sample_uid)
{
  $result_delete1 = pg_query ($dbconn, "
   DELETE FROM library_prep_note_sample
    WHERE sample_uid = $sample_uid");
  if (!$result_delete1)
  {
    echo 'Error deleting from library_prep_note_sample table: ';
    echo pg_last_error($dbconn);
    exit;
  } else {
    $result_delete2 = pg_query ($dbconn, "
     DELETE FROM sample
      WHERE sample_uid = $sample_uid");
    if (!$result_delete2)
    {
      echo 'Error deleting from sample table: ';
      echo pg_last_error($dbconn);
      exit;
    }  // if (!$result_delete2)
  }  // if (!$result_delete1)
}  // foreach ($_SESSION['sample_uid_delete'] as $sample_uid)
/***********************************************************************/
header("location: project_details.php");
?>
