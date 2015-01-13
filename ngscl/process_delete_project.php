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
$project_uid = $_SESSION['project_uid'];
//***********DELETE PROJECT***************/
// Delete all the samples for this project.
$result_samples = pg_query ($dbconn, "
 SELECT sample_uid
   FROM sample
  WHERE project_uid = $project_uid");
if(!$result_samples)
{
  echo '<span>Error selecting project samples: ';
  echo pg_last_error($dbconn);
  echo '</span>';
  exit;
} else {
  for ($i=0; $i < pg_num_rows($result_samples); $i++)
  {
    $sample_uid = pg_fetch_result ($result_samples, $i, 0);
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
  }  // for ($i=0; $i < pg_num_rows($result_samples); $i++)
} // if(!$result_samples)
// Determine if this project has a project log.
$result_log = pg_query ($dbconn, "
 SELECT project_log_uid
   FROM project_log
  WHERE project_uid = $project_uid");
if(!$result_log)
{
  echo pg_last_error($dbconn);
  exit;
} else {
  // Delete project log run lane data.
  if (pg_num_rows ($result_log) > 0)
  {
    $project_log_uid = pg_fetch_result ($result_log, 0, 0);
    $result_delete3 = pg_query ($dbconn, "
     DELETE FROM project_log_run_lane
      WHERE project_log_uid = $project_log_uid");
    // Delete project log data.
    if(!$result_delete3)
    {
      echo pg_last_error($dbconn);
      exit;
    } else {
      $result_delete4 = pg_query ($dbconn, "
       DELETE FROM project_log
        WHERE project_log_uid = $project_log_uid");
      if(!$result_delete4)
      {
        echo pg_last_error($dbconn);
        exit;
      }  // if(!$result_delete4)
    }  // if(!$result_delete3)
  }  // if (pg_num_rows ($result_log) > 0)
}  // if(!$result_log)
// Delete the project.
$result_delete5 = pg_query ($dbconn, "
 DELETE FROM project
  WHERE project_uid = $project_uid");
if (!$result_delete5)
{
  echo 'Error deleting from project table: ';
  echo pg_last_error($dbconn);
  exit;
}  // if (!$result_delete5)
/***********************************************************************/
header("location: project.php");
?>
