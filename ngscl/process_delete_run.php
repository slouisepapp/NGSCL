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
  $run_uid = $_SESSION['run_uid'];
  //***********DELETE RUN***************/
  // Delete from the run_lane_sample table.
  // Get the run_lanes for the run to be deleted.
  $result_run_lane = pg_query($dbconn,"
    SELECT run_lane_uid
      FROM run_lane
     WHERE run_uid = $run_uid");
  for ($i=0; $i < pg_num_rows($result_run_lane); $i++)
  {
    // Delete all the run lane samples for the lane.
    $run_lane_uid = pg_fetch_result ($result_run_lane, 0, 0);
    $result_delete = pg_query ($dbconn, "
     DELETE FROM run_lane_sample
      WHERE run_lane_uid = $run_lane_uid");
    if (!$result_delete)
    {
      echo pg_last_error($dbconn);
      exit;
    }  // if (!$result_delete)
  }  // for ($i=0; $i < pg_num_rows($result_run_lane); $i++)
  // Delete from the run_lane table.
  $result_delete = pg_query ($dbconn, "
   DELETE FROM run_lane
    WHERE run_uid = $run_uid");
  if (!$result_delete)
  {
    echo pg_last_error($dbconn);
    exit;
  }  // if (!$result_delete)
  // Delete from the run table.
  $result_delete = pg_query ($dbconn, "
   DELETE FROM run
    WHERE run_uid = $run_uid");
  if (!$result_delete)
  {
    echo pg_last_error($dbconn);
    exit;
  }  // if (!$result_delete)
  /***********************************************************************/
  header("location: run.php");
?>
