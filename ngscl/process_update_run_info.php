<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_run_info"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/*************************RUN TYPE*****************************/
if(!isset($_SESSION['choose_run_type']) ||
   $_SESSION['choose_run_type']=='' ||
   $_SESSION['choose_run_type'] < 0)
{
   array_push($array_error,"* You must enter a Run Type");
   $ref_run_type_uid = 0;
} else {
  $ref_run_type_uid = $_SESSION['choose_run_type'];
  $result_run_type = pg_query ($dbconn, "
   SELECT run_type
     FROM ref_run_type
    WHERE ref_run_type_uid = $ref_run_type_uid");
  if (!$result_run_type)
  {
    echo pg_last_error($dbconn);
    exit;
  } else {
    if (pg_num_rows ($result_run_type) > 0)
    {
      $run_type = pg_fetch_result ($result_run_type, 0, 0);
    } else {
      $run_type = "No run type";
    }  // if (pg_num_rows ($result_run_type) > 0)
  }  // if (!$result_run_type)
}  // if(!isset($_SESSION['choose_run_type']) ||...
/*************************RUN NUMBER*****************************/
if(!isset($_SESSION['run_number']) ||
    $_SESSION['run_number']=='' ||
    $_SESSION['run_number'] < 0)
{
   array_push($array_error,"* You must enter a Run Number");
} else {
  $run_number = $_SESSION['run_number'];
  // Check if a run with this number already exists.
  $result = pg_query ($dbconn, "
   SELECT COUNT(1) AS row_count
     FROM run
    WHERE run_number = $run_number AND
          ref_run_type_uid = $ref_run_type_uid AND
          run_uid != ".$_SESSION['run_uid']);
  if (!$result)
  {
    echo pg_last_error($dbconn);
    exit;
  } elseif ($line = pg_fetch_assoc($result)) {
    if ($line['row_count'] > 0)
    {
    array_push ($array_error,
     '* A '.$run_type.' run number '.$run_number.' already exists in the database.'); 
    }  // if ($line['row_count'] > 0)
  }  // if (!$result)
}  // if(!isset($_SESSION['run_number']) ||...
/*************************RUN NAME*****************************/
$run_name = (isset($_SESSION['run_name']) ?
  ddl_ready ($_SESSION['run_name']) : "");
// Check if a run with this name already exists.
if (strlen (trim ($run_name)) > 0)
{
  $upper_run_name = strtoupper($run_name);
  $result = pg_query ($dbconn, "
   SELECT COUNT(1) AS row_count
     FROM run
    WHERE upper(run_name) = '$upper_run_name' AND
          ref_run_type_uid = $ref_run_type_uid AND
          run_uid != ".$_SESSION['run_uid']);
  if (!$result)
  {
    echo 'Error selecting from run table: ';
    echo pg_last_error($dbconn);
    exit;
  } elseif ($line = pg_fetch_assoc($result)) {
    if ($line['row_count'] > 0)
    {
    array_push ($array_error,
     '* A '.$run_type.' run named "'.$upper_run_name.'" '.
     'already exists in the database.'); 
    }  // if ($line['row_count'] > 0)
  }  // if (!$result)
}  // if (strlen (trim ($run_name)) > 0)
/*************************READ TYPE*****************************/
if(!isset($_SESSION['read_type']) || $_SESSION['read_type']=='')
{
   array_push($array_error,"* You must select a Read Type");
} else {
  $read_type = $_SESSION['read_type'];
}  // if(!isset($_SESSION['read_type']) || $_SESSION['read_type']=='')
/*************************READ 1 LENGTH*****************************/
if(!isset($_SESSION['read_1_length']) ||
    $_SESSION['read_1_length']=='' ||
    $_SESSION['read_1_length'] < 0)
{
   array_push($array_error,"* You must enter Read 1 Length");
} else {
  $read_1_length = $_SESSION['read_1_length'];
}  // if(!isset($_SESSION['read_1_length']) ||...
/*************************READ 2 LENGTH*****************************/
if($_SESSION['read_type'] == 'Paired End')
{
  if(!isset($_SESSION['read_2_length']) ||
      $_SESSION['read_2_length']=='' ||
      $_SESSION['read_2_length'] < 0)
  {
     array_push($array_error,"* You must enter Read 2 Length");
  } else {
    $read_2_length = $_SESSION['read_2_length'];
  }  // if(!isset($_SESSION['read_2_length']) ||...
} else {
    $read_2_length = 'NULL';
}  // if($_SESSION['read_type'] == 'Paired End')
/*************************READ LENGTH INDEXING*****************************/
if(!isset($_SESSION['read_length_indexing']) ||
    $_SESSION['read_length_indexing']=='' ||
    $_SESSION['read_length_indexing'] < 0)
{
   array_push($array_error,"* You must enter Indexing Read Length");
} else {
  $read_length_indexing = $_SESSION['read_length_indexing'];
}  // if(!isset($_SESSION['read_length_indexing']) ||...

/**********************HI SEQ SLOT*******************************************/
$hi_seq_slot = ddl_ready ($_SESSION['hi_seq_slot']);
/**********************CLUSTER GEN START DATE***************************/
// Check to see if the format is valid.
$cluster_gen_start_date =  (isset($_SESSION['cluster_gen_start_date']) ?
 $_SESSION['cluster_gen_start_date'] : "");
if (trimmed_string_not_empty ($_SESSION['cluster_gen_start_date']))
{
  $result = pg_query($dbconn,"
   SELECT CAST ('$cluster_gen_start_date' AS date)");
  if(!$result)
  {
    $error_string = 'Cluster Gen Start Date not valid: '.pg_last_error($dbconn);
    array_push($array_error, $error_string);
  } else {
    $cluster_gen_start_date = pg_fetch_result ($result, 0, 0);
  } // if(!$result)
}  // if (trimmed_string_not_empty ($_SESSION['cluster_gen_start_date']))
/**********************SEQUENCING START DATE***************************/
// Check to see if the format is valid.
$sequencing_start_date = (isset($_SESSION['sequencing_start_date']) ?
 $_SESSION['sequencing_start_date'] : "");
if (trimmed_string_not_empty ($_SESSION['sequencing_start_date']))
{
  $result = pg_query($dbconn,"
   SELECT CAST ('$sequencing_start_date' AS date)");
  if(!$result)
  {
    $error_string = 'Sequencing Start Date not valid: '.pg_last_error($dbconn);
    array_push($array_error, $error_string);
  } else {
    $sequencing_start_date = pg_fetch_result ($result, 0, 0);
  } // if(!$result)
}  // if (trimmed_string_not_empty ($_SESSION['sequencing_start_date']))
/**********************TRUSEQ CLUSTER GEN KIT****************************/
$truseq_cluster_gen_kit = ddl_ready ($_SESSION['truseq_cluster_gen_kit']);
/**********************FLOW CELL HS ID***********************************/
$flow_cell_hs_id = ddl_ready ($_SESSION['flow_cell_hs_id']);
/**********************SEQUENCING KITS**********************************/
$sequencing_kits = ddl_ready ($_SESSION['sequencing_kits']);
/**********************COMMENTS*******************************************/
$comments = ddl_ready ($_SESSION['comments']);

if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: update_run_info.php");
  exit;
}
//***********UPDATE RUN***************/
  // Set the date strings for the update statement.
  if (strlen (trim ($cluster_gen_start_date)) > 0)
  {
    $cluster_gen_start_date_string = "'" . $cluster_gen_start_date . "'";
  } else {
    $cluster_gen_start_date_string = "NULL";
  }  // if (strlen (trim ($cluster_gen_start_date)) > 0)
  if (strlen (trim ($sequencing_start_date)) > 0)
  {
    $sequencing_start_date_string = "'" . $sequencing_start_date . "'";
  } else {
    $sequencing_start_date_string = "NULL";
  }  // if (strlen (trim ($sequencing_start_date)) > 0)
  $date_fields_string = "cluster_gen_start_date = " .
                        $cluster_gen_start_date_string .
                        ", sequencing_start_date = " .
                        $sequencing_start_date_string . ", ";
if (isset($_SESSION['run_number']) &&
    trimmed_string_not_empty ($_SESSION['run_number']))
{
  $result = pg_query ($dbconn, "
    UPDATE run
       SET run_number = $run_number,
           run_name = '$run_name',
           ref_run_type_uid = $ref_run_type_uid,
           read_type = '$read_type',
           read_1_length = $read_1_length,
           read_2_length = $read_2_length,
           read_length_indexing = $read_length_indexing,
           hi_seq_slot = '$hi_seq_slot', " .
           $date_fields_string . "
           truseq_cluster_gen_kit = '$truseq_cluster_gen_kit',
           flow_cell_hs_id = '$flow_cell_hs_id',
           sequencing_kits = '$sequencing_kits',
           comments = '$comments'
    WHERE run_uid = ".$_SESSION['run_uid']);
  if(!$result)
  {
    echo 'Error updating run: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
}  // if(isset($_SESSION['run_name']) &&...
/***********************************************************************/
header("location: run_info.php");
?>
