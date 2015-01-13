<?php
  session_start();
  require_once('db_fns.php');
  $dbconn = database_connect();
  $array_error = array();
  $array_run_lane = array();
// ***************************************************************
// This function copies all the columns from the run table
// with the exception of:
//   run_uid - sequence generated
//   run_number - input value
//   run_name - input value
// ***************************************************************
function copy_run_row (
 $dbconn, $old_run_uid, $new_run_number, $new_run_name)
{
  $result_select = pg_query ($dbconn, "
   SELECT *
     FROM run
    WHERE run_uid = $old_run_uid");
  if (!$result_select)
  {
    return FALSE;
  } else {
    $num_cols = pg_num_fields ($result_select);
    $field_string = "";
    for ($i=0; $i < $num_cols; $i++)
    {
      // ****
      // Build a string of all the column names other than
      // run_uid, run_number, and run_name.
      // ****
      $field_name = pg_field_name ($result_select, $i);
      if (($field_name != "run_uid") &&
          ($field_name != "run_number") &&
          ($field_name != "run_name"))
      {
        $field_string .= ", " .pg_field_name ($result_select, $i);
      }  // if (($field_name != "run_uid") &&...
    }  // for ($i=0; $i < pg_num_rows($result_select); $i++)
    // Get the next uid from the sequence.
    $result_uid = pg_query ($dbconn, "
     SELECT nextval ('run_run_uid_seq')");
    if (!$result_uid)
    {
      return FALSE;
    } else {
      // Now insert the copied row to the run table.
      $new_run_uid = pg_fetch_result ($result_uid, 0, 0);
      $result_insert = pg_query ($dbconn, "
       INSERT INTO run
        (run_uid, run_number, run_name" . $field_string . ")
        SELECT $new_run_uid, $new_run_number, '$new_run_name'" .
               $field_string . "
          FROM run
         WHERE run_uid = $old_run_uid");
      if (!$result_insert)
      {
        return FALSE;
      } else {
        return $new_run_uid;
      }  // if (!$result_insert)
    }  // if (!$result_uid)
  }  // if (!result_select)
}  // function copy_run_row
// ***************************************************************
// This function copies all the columns from the run_lane table
// with the exception of:
//   run_lane_uid - sequence generated
//   run_uid - input value
// ***************************************************************
function copy_run_lane_row ($dbconn, $new_run_uid, $old_run_lane_uid)
{
  $result_select = pg_query ($dbconn, "
   SELECT *
     FROM run_lane
    WHERE run_lane_uid = $old_run_lane_uid");
  if (!$result_select)
  {
    return FALSE;
  } else {
    $num_cols = pg_num_fields ($result_select);
    for ($i=0; $i < $num_cols; $i++)
    {
      // ****
      // Build a string of all the column names other than
      // run_lane_uid and run_uid.
      // ****
      $field_name = pg_field_name ($result_select, $i);
      if (($field_name != "run_lane_uid") && ($field_name != "run_uid"))
      {
        $field_string = $field_string .
         ", " .pg_field_name ($result_select, $i);
      }  // if (($field_name != "run_lane_uid") && ($field_name != "run_uid"))
    }  // for ($i=0; $i < pg_num_rows($result_select); $i++)
    // Get the next uid from the sequence.
    $result_uid = pg_query ($dbconn, "
     SELECT nextval ('run_lane_run_lane_uid_seq')");
    if (!$result_uid)
    {
      return FALSE;
    } else {
      // Now insert the copied row to the run_lane table.
      $new_run_lane_uid = pg_fetch_result ($result_uid, 0, 0);
      $result_insert = pg_query ($dbconn, "
       INSERT INTO run_lane
        (run_lane_uid, run_uid " . $field_string . ")
        SELECT $new_run_lane_uid, $new_run_uid" .
               $field_string . "
          FROM run_lane
         WHERE run_lane_uid = $old_run_lane_uid");
      if (!$result_insert)
      {
        return FALSE;
      } else {
        return $new_run_lane_uid;
      }  // if (!$result_insert)
    }  // if (!$result_uid)
  }  // if (!result_select)
}  // function copy_run_lane_row
// ***************************************************************
// This function copies all the columns from the run_lane_sample table
// with the exception of:
//   run_lane_sample_uid - sequence generated
//   run_lane_uid - input value
// ***************************************************************
function copy_run_lane_samples ($dbconn, $new_run_lane_uid, $old_run_lane_uid)
{
  $result_select = pg_query ($dbconn, "
   SELECT *
     FROM run_lane_sample
    WHERE run_lane_uid = $old_run_lane_uid");
  if (!$result_select)
  {
    return FALSE;
  } else {
    $num_cols = pg_num_fields ($result_select);
    for ($i=0; $i < $num_cols; $i++)
    {
      // ****
      // Build a string of all the column names other than
      // run_lane_sample_uid and run_lane_uid.
      // ****
      $field_name = pg_field_name ($result_select, $i);
      if (($field_name != "run_lane_sample_uid") &&
          ($field_name != "run_lane_uid"))
      {
        $field_string = $field_string .
         ", " .pg_field_name ($result_select, $i);
      }  // if (($field_name != "run_lane_sample_uid") &&...
    }  // for ($i=0; $i < pg_num_rows($result_select); $i++)
    // Now insert the copied rows to the run_lane_sample table.
    $result_insert = pg_query ($dbconn, "
     INSERT INTO run_lane_sample
      (run_lane_uid" . $field_string . ")
      SELECT $new_run_lane_uid" .
             $field_string . "
        FROM run_lane_sample
       WHERE run_lane_uid = $old_run_lane_uid");
    if (!$result_insert)
    {
      return FALSE;
    } else {
      return TRUE;
    }  // if (!$result_insert)
  }  // if (!result_select)
}  // function copy_run_lane_samples
  // Put POST values into SESSION.
  foreach ($_POST as $thislabel => $thisvalue)
  {
    if ($thislabel != "PHPSESSID")
    {
      $_SESSION[$thislabel] = $thisvalue;
    }  // if ($thislabel != "PHPSESSID")
  }  // foreach ($_POST as $thislabel => $thisvalue)
  $old_run_uid = $_SESSION['run_uid'];
  $ref_run_type_uid = $_SESSION['ref_run_type_uid'];
  $run_type = $_SESSION['run_type'];
  /*************************RUN NUMBER*****************************/
  // Check that new run number exists.
  if(!isset($_SESSION['new_run_number']) ||
      $_SESSION['new_run_number']=='' ||
      $_SESSION['new_run_number'] < 0)
  {
     array_push($array_error,"* You must enter a Run Number");
  } else {
    $new_run_number = $_SESSION['new_run_number'];
    // Check that the new run number does not already exist.
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM run
      WHERE run_number = $new_run_number AND
            ref_run_type_uid = $ref_run_type_uid");
    if (!$result)
    {
      echo pg_last_error($dbconn);
      exit;
    } elseif ($line = pg_fetch_assoc($result)) {
      if ($line['row_count'] > 0)
      {
      array_push ($array_error,
       '* A '.$run_type.' run number '.$new_run_number.
       ' already exists in the database.'); 
      }  // if ($line['row_count'] > 0)
    }  // if (!result)
  }  // if(!isset($_SESSION['new_run_number']) ||...
  /*************************RUN NAME*****************************/
  $new_run_name = (isset($_SESSION['new_run_name']) ?
   ddl_ready ($_SESSION['new_run_name']) : "");
  // Check if a run with this name already exists.
  if (strlen (trim ($new_run_name)) > 0)
  {
    $upper_new_run_name = strtoupper($new_run_name);
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM run
      WHERE upper(run_name) = '$upper_new_run_name' AND
            ref_run_type_uid = $ref_run_type_uid");
    if (!$result)
    {
      echo pg_last_error($dbconn);
      exit;
    } elseif ($line = pg_fetch_assoc($result)) {
      if ($line['row_count'] > 0)
      {
      array_push ($array_error,
       '* A '.$run_type.' run named "'.$new_run_name.'" '.
       'already exists in the database.'); 
      }  // if ($line['row_count'] > 0)
    }  // if (!result)
  }  // if (strlen (trim ($new_run_name)) > 0)
  if(count($array_error) >= 1)
  {
    $_SESSION['errors'] = $array_error;
    header("location: copy_run.php");
    exit;
  }
  // Put all the lanes for the old run into an array.
  $result_run_lanes = pg_query ($dbconn, "
   SELECT run_lane_uid
     FROM run_lane
    WHERE run_uid = $old_run_uid");
  if (!$result_run_lanes)
  {
    echo pg_last_error ($dbconn);
    exit;
  } else {
    for ($i=0; $i < pg_num_rows($result_run_lanes); $i++)
    {
      $array_run_lane[] = pg_fetch_result ($result_run_lanes, $i, 0);
    }  // for ($i=0; $i < pg_num_rows($result_run_lanes); $i++)
    // Copy the run.
    $new_run_uid = copy_run_row ($dbconn, $old_run_uid,
                                 $new_run_number, $new_run_name);
    if (!$new_run_uid)
    {
      echo pg_last_error ($dbconn);
      exit;
    } else {
      // Loop through the array of run lanes for the old run.
      foreach ($array_run_lane as $old_run_lane_uid)
      {
        // Copy the run lane.
        $new_run_lane_uid = copy_run_lane_row ($dbconn,
         $new_run_uid, $old_run_lane_uid);
        if (!$new_run_lane_uid)
        {
          echo pg_last_error ($dbconn);
          exit;
        } else {
          // Copy all the samples from the run lane.
          $success = copy_run_lane_samples ($dbconn, $new_run_lane_uid,
                      $old_run_lane_uid);
          if (!$success)
          {
            echo pg_last_error ($dbconn);
            exit;
          }  // if (!$success)
        }  // if (!$new_run_uid)
      }  // foreach ($array_run_lane as $run_lane_uid)
    }  // if (!$new_run_lane_uid)
  }  // if (!$result_run_lanes)
  header("location: run.php");
?>
