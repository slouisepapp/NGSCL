<?php
// **************************************************************
// This function returns the next run number.
// **************************************************************
function next_run_number ($dbconn, $ref_run_type_uid = 0)
{
  $return_run_number = 0;
  if ($ref_run_type_uid > 0)
  {
    $result = pg_query ($dbconn, "
     SELECT COALESCE (max (run_number), 0) + 1
       FROM run
      WHERE ref_run_type_uid = $ref_run_type_uid");
    if (!$result)
    {
    } else {
      $return_run_number = pg_fetch_result ($result, 0, 0);
    }  // if (!$result)
  }  // if ($ref_run_type_uid > 0)
  return $return_run_number;
}  // function next_run_number 
// ************************************************************
// This function finds the barcodes that occur more than once
// in the input lane.  An array of the found barcodes and the
// count for the input lane is returned.
// ************************************************************
function dup_barcodes_in_lane ($dbconn, $run_lane_uid)
{
  // Create an array to hold the barcodes and counts.
  $barcode_array = array();
  // Query the barcodes that occur more than once in the lane.
  $result_barcode = pg_query ($dbconn, "
   SELECT barcode, COUNT(1) AS barcode_count
     FROM run_lane,
          run_lane_sample,
          sample
    WHERE run_lane.run_lane_uid = $run_lane_uid AND
          run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
          run_lane_sample.sample_uid = sample.sample_uid
    GROUP BY barcode
   HAVING COUNT(1) > 1
    ORDER BY barcode");
  for ($i=0; $i < pg_num_rows ($result_barcode); $i++)
  {
    $row = pg_fetch_assoc ($result_barcode);
    $barcode_array[$i]['barcode'] = $row['barcode'];
      $barcode_array[$i]['barcode_caution'] = "Caution: " .
                         $row['barcode_count'] .
                         " samples in this lane have barcode " .
                         $row['barcode'] .
                         ".";
  }  // for ($i=0; $i < pg_num_rows ($result_barcode; $i++)
  return $barcode_array;
}  // function dup_barcodes_in_lane
// *********************************************************
// Find the run_lane_uid for the input run and lane number.
// If no run_lane_uid exists, return a negative number.
// *********************************************************
function find_run_lane ($dbconn, $run_uid, $lane_number)
{
  require 'user_view.php';
  // See if there is a row in the run_lane table for this run and lane.
  $result_run_lane = pg_query ($dbconn, "
   SELECT $run_lane_view.run_lane_uid
     FROM $run_lane_view,
          $run_lane_sample_view
    WHERE run_uid = $run_uid AND
          lane_number = $lane_number AND
          $run_lane_view.run_lane_uid = $run_lane_sample_view.run_lane_uid");
  if (!$result_run_lane)
  {
    $run_lane_uid = -999;
  } elseif (pg_num_rows($result_run_lane) > 0) {
    $run_lane_uid = pg_fetch_result ($result_run_lane, 0, 0);
  } else {
    $run_lane_uid = -999;
  }  // if (!$result_run_lane)
  return $run_lane_uid;
}  // function find_run_lane
// ************************************************************
// This function adds a lane record for the input run and lane.
// ************************************************************
function add_lane_record ($dbconn, $run_uid, $lane_number)
{
  // Get the run_lane_uid that will be used for the lane record.
  $result_select = pg_query ($dbconn, "
   SELECT nextval ('run_lane_run_lane_uid_seq')");
  if (!$result_select)
  {
    return -999;
  } else {
    $run_lane_uid = pg_fetch_result ($result_select, 0, 0);
    // Add the lane record for the run.
    $result_insert = pg_query ($dbconn, "
     INSERT INTO run_lane
      (run_lane_uid, run_uid, lane_number)
     VALUES
      ($run_lane_uid, $run_uid, $lane_number)");
    if (!$result_insert)
    {
      return -999;
    } else {
      return $run_lane_uid;
    }  // if (!$result_insert)
  }  // if (!$result_select)
}  // function add_lane_record
// ************************************************************
// This function adds the input samples to
// the input run and lane.  Any insert errors are returned.
// ************************************************************
function add_to_lane (
 $dbconn, $run_uid, $lane_number, $run_lane_uid, $sample_uid_array)
{
  // Create an array to hold errors.
  $array_error = array();
  // Add a lane record, if necessary.
  if (!isset ($run_lane_uid) || $run_lane_uid < 1)
  {
     $run_lane_uid = add_lane_record ($dbconn, $run_uid, $lane_number);
     if ($run_lane_uid < 1)
     {
       $array_error[] = pg_last_error($dbconn);
       return $array_error;
     }  // if ($run_lane_uid < 1)
  }  // if (!isset ($run_lane_uid) || $run_lane_uid < 1)
  // Add each input sample to the input lane for the input run.
  foreach ($sample_uid_array as $rowkey => $sample_uid)
  {
    $result_insert = pg_query ($dbconn, "
     INSERT INTO run_lane_sample
      (run_lane_uid, sample_uid)
     VALUES
      ($run_lane_uid, $sample_uid)");
    if (!$result_insert)
      $array_error[] = pg_last_error ($dbconn);
  }  // foreach ($sample_uid_array as $rowkey => $sample_uid)
  return $array_error;
}  // function add_to_lane
// ************************************************************
// This function removes the input samples from
// the input run and lane.  Any delete errors are returned.
// ************************************************************
function remove_from_lane ($dbconn, $run_lane_uid, $sample_uid_array)
{
  // Create an array to hold errors.
  $array_error = array();
  // Check lane record exists.
  if (!isset ($run_lane_uid) || $run_lane_uid < 1)
  {
    $array_error[] = "Lane record does not exist.";
    return $array_error;
  }  // if (!isset ($run_lane_uid) || $run_lane_uid < 1)
  // Delete each input sample from the input lane.
  foreach ($sample_uid_array as $rowkey => $sample_uid)
  {
    $result_insert = pg_query ($dbconn, "
     DELETE FROM run_lane_sample
      WHERE run_lane_sample_uid = $sample_uid");
    if (!$result_insert)
      $array_error[] = pg_last_error ($dbconn);
  }  // foreach ($sample_uid_array as $rowkey => $sample_uid)
  return $array_error;
}  // function remove_from_lane
// ************************************************************
// This function returns a string of all the samples in the run
// that don't match the run type.
// ************************************************************
function run_sample_type_mismatch ($dbconn, $run_uid)
{
  require 'user_view.php';
  $mismatch_string = "";
  // Find all the samples in this run with the wrong run type.
  $result = pg_query ($dbconn, "
   SELECT lane_number,
          project_name,
          sample_name
     FROM $run_view,
          $project_view,
          $sample_view,
          $run_lane_view, 
          $run_lane_sample_view
    WHERE $run_view.run_uid = $run_uid AND
          $run_view.run_uid = $run_lane_view.run_uid AND
          $run_lane_view.run_lane_uid = $run_lane_sample_view.run_lane_uid AND
          $run_lane_sample_view.sample_uid = $sample_view.sample_uid AND
          $sample_view.project_uid = $project_view.project_uid AND
          $project_view.ref_run_type_uid != $run_view.ref_run_type_uid
    ORDER BY lane_number,
             sample_name");
  if (! $result)
  {
    $mismatch_string .= pg_last_error ($dbconn);
  } elseif (pg_num_rows ($result) > 0) {
    $mismatch_string .= 'Run type for project and run must match. ' .
         'Please remove these samples:' .
         '<br />';
    // Put the mismatched lanes and samples into an array.
    for ($i=0; $i < pg_num_rows ($result); $i++)
    {
      $row = pg_fetch_assoc ($result);
      $mismatch_string .= 'Lane: ' . $row['lane_number'] .
                           ', Project: ' . $row['project_name'] .
                           ', Sample: ' . $row['sample_name'] .
                           '<br />';
    }  // foreach ($mismatched_samples as $rowkey => $row)
  }  // if (! $mismatched_samples)
  return $mismatch_string;
}  // function run_sample_type_mismatch
// ************************************************************
// This function returns a string of all the runs that have
// samples from projects that don't match the run type.
// ************************************************************
function run_type_mismatch ($dbconn)
{
  $mismatch_string = "";
  // ****
  // Find all the runs that have samples from projects
  // with the wrong run type.
  // ****
  $result = pg_query ($dbconn, "
   SELECT DISTINCT run_number || '/' || run_name AS run_number_name
     FROM run,
          project,
          sample,
          run_lane, 
          run_lane_sample
    WHERE run.run_uid = run_lane.run_uid AND
          run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
          run_lane_sample.sample_uid = sample.sample_uid AND
          sample.project_uid = project.project_uid AND
          project.ref_run_type_uid != run.ref_run_type_uid
    ORDER BY run_number_name");
  if (! $result)
  {
    $mismatch_string .= pg_last_error ($dbconn);
  } elseif (pg_num_rows ($result) > 0) {
    $mismatch_string .= 'These runs include projects with a ' .
                        'mismatching run type:<br /> ';
    // Put the mismatched lanes and samples into an array.
    for ($i=0; $i < pg_num_rows ($result); $i++)
    {
      $run_number_name = pg_fetch_result ($result, $i, 0);
      $mismatch_string .= $run_number_name . '<br />';
    }  // foreach ($mismatched_samples as $rowkey => $row)
  }  // if (! $mismatched_samples)
  return $mismatch_string;
}  // function run_type_mismatch
// ************************************************************
// This function returns the general run information.
// It does not include the lane or sample level information.
// ************************************************************
function export_run_info ($dbconn, $run_uid, $export_comment_symbol)
{
  require 'user_view.php';
  $row_start = '"' . $export_comment_symbol;
  // Get the run information.
  $result = pg_query ($dbconn, "
   SELECT run_number,
          run_name,
          run_type,
          read_type,
          read_1_length,
          read_2_length,
          read_length_indexing,
          hi_seq_slot,
          cluster_gen_start_date,
          sequencing_start_date,
          truseq_cluster_gen_kit,
          flow_cell_hs_id,
          sequencing_kits,
          comments
     FROM $run_view,
          ref_run_type
    WHERE run_uid = $run_uid AND
          $run_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    return pg_last_error ($dbconn);
  } else {
    // Build the export run_info string from the query result.
    $row = pg_fetch_assoc ($result);
    $run_info_string = $row_start .  "Run Number=" .
                       $row['run_number'] . "\"\r\n" .
                       $row_start .  "Run Name=" .
                       $row['run_name'] . "\"\r\n" .
                       $row_start .  "Run Type=" .
                       $row['run_type'] . "\"\r\n" .
                       $row_start .  "Read Type=" .
                       $row['read_type'] . "\"\r\n" .
                       $row_start .  "Read 1 Length=" .
                       $row['read_1_length'] . "\"\r\n" .
                       $row_start .  "Read 2 Length=" .
                       $row['read_2_length'] . "\"\r\n" .
                       $row_start .  "Indexing Read Length=" .
                       $row['read_length_indexing'] . "\"\r\n" .
                       $row_start .  "Hi Seq Slot=" .
                       $row['hi_seq_slot'] . "\"\r\n" .
                       $row_start .  "Cluster Gen Start Date=" .
                       $row['cluster_gen_start_date'] . "\"\r\n" .
                       $row_start .  "Sequencing Start Date=" .
                       $row['sequencing_start_date'] . "\"\r\n" .
                       $row_start .  "TruSeq Cluster Gen Kit=" .
                       $row['truseq_cluster_gen_kit'] . "\"\r\n" .
                       $row_start .  "FlowCel HS ID=" .
                       $row['flow_cell_hs_id'] . "\"\r\n" .
                       $row_start .  "Sequencing Kits=" .
                       replace_eol ($row['sequencing_kits']) . "\"\r\n" .
                       $row_start .  "Comments=" .
                       replace_eol ($row['comments']) . "\"\r\n";
    return $run_info_string;
  }  // if (!$result)
}  // function export_run_info
?>
