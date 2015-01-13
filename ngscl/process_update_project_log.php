<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
/************************ALL POST VARIABLES****************************/
$project_log_uid = ddl_ready ($_SESSION['project_log_uid']);
$project_uid = ddl_ready ($_SESSION['project_uid']);
$run_number = (strlen (ddl_ready ($_SESSION['run_number'])) > 0 ?
 $_SESSION['run_number'] : 'NULL');
$experiment_type = ddl_ready ($_SESSION['experiment_type']);
$results_summary = ddl_ready ($_SESSION['results_summary']);
$account_number = ddl_ready ($_SESSION['account_number']);
$estimated_price = (strlen (ddl_ready ($_SESSION['estimated_price'])) > 0 ?
 $_SESSION['estimated_price'] : 'NULL');
$estimated_price_comments = ddl_ready ($_SESSION['estimated_price_comments']);
$name_of_experimenter = ddl_ready ($_SESSION['name_of_experimenter']);
$experiment_title = ddl_ready ($_SESSION['experiment_title']);
$lanes = ddl_ready ($_SESSION['lanes']);
$hypothesis = ddl_ready ($_SESSION['hypothesis']);
$cells_used = ddl_ready ($_SESSION['cells_used']);
$experimental_methods = ddl_ready ($_SESSION['experimental_methods']);
$time_course = ddl_ready ($_SESSION['time_course']);
$experimental_category = ddl_ready ($_SESSION['experimental_category']);
$conditions_comments = ddl_ready ($_SESSION['conditions_comments']);
$batching_instructions = ddl_ready ($_SESSION['batching_instructions']);
$sample_purification_method = ddl_ready ($_SESSION['sample_purification_method']);
$kits_used = ddl_ready ($_SESSION['kits_used']);
$amplification_method = ddl_ready ($_SESSION['amplification_method']);
$barcoding_used = ddl_ready ($_SESSION['barcoding_used']);
$new_method_or_comparison_study = ddl_ready ($_SESSION['new_method_or_comparison_study']);
$library_prep_comments = ddl_ready ($_SESSION['library_prep_comments']);
$read_length = ddl_ready ($_SESSION['read_length']);
$adapter_sequences = ddl_ready ($_SESSION['adapter_sequences']);
$barcode_sequences = ddl_ready ($_SESSION['barcode_sequences']);

/**********************CORE APPROVAL***************************/
$core_director_approval = (isset ($_SESSION['core_director_approval_box']) ?
 'TRUE' : 'FALSE');
/**********************PRIMARY INVESTIGATOR APPROVAL***********/
$primary_investigator_approval = (isset ($_SESSION['pi_approval_box']) ?
 'TRUE' : 'FALSE');
/**********************RUN NUMBER***************************/
// Check to see if the run number is a positive integer.
if (trimmed_string_not_empty ($run_number) && $run_number != 'NULL')
{
  if (ctype_digit ($run_number) && $run_number > 0)
  {
  } else {
    $error_string = 'Run Number must be a positive integer.';
    array_push($array_error, $error_string);
  }  // if (ctype_digit ($run_number) && $run_number > 0)
}  // if (trimmed_string_not_empty ($_SESSION['run_number']))
/**********************ESTIMATED PRICE***************************/
// Check to see if the estimated price is a non-negative real number.
if (trimmed_string_not_empty ($estimated_price) && $estimated_price != 'NULL')
{
  if (is_numeric ($estimated_price) && $estimated_price >= 0)
  {
  } else {
    $error_string = 'Estimated Price must be a non-negative number.';
    array_push($array_error, $error_string);
  }  // if (is_numeric ($estimated_price) && $estimated_price >= 0)
}  // if (trimmed_string_not_empty ($_SESSION['estimated_price']))
/**********************RUN DATE***************************/
// Check to see if the format is valid.
if (trimmed_string_not_empty ($_SESSION['run_date']))
{
  $run_date = $_SESSION['run_date'];
  $result = pg_query($dbconn,"
   SELECT CAST ('$run_date' AS date)");
  if(!$result)
  {
    $error_string = 'Run Date not valid: '.pg_last_error($dbconn);
    array_push($array_error, $error_string);
  } else {
    $run_date = "'".pg_fetch_result ($result, 0, 0)."'";
  } // if(!$result)
} else {
  $run_date = 'NULL';
}  // if (trimmed_string_not_empty ($_SESSION['run_date']))
/**********************CORE APPROVAL DATE***************************/
// Check to see if the format is valid.
if ($core_director_approval == 'TRUE')
{
  if (trimmed_string_not_empty ($_SESSION['core_director_approval_date']))
  {
    $core_director_approval_date = $_SESSION['core_director_approval_date'];
    $result = pg_query($dbconn,"
     SELECT CAST ('$core_director_approval_date' AS date)");
    if(!$result)
    {
      $error_string = 'CORE Approval Date not valid: '.pg_last_error($dbconn);
      array_push($array_error, $error_string);
    } else {
      $core_director_approval_date = "'".pg_fetch_result ($result, 0, 0)."'";
    } // if(!$result)
  } else {
    $core_director_approval_date = 'NULL';
  }  // if (trimmed_string_not_empty ($_SESSION['core_director_approval_date']))
} else {
  $core_director_approval_date = 'NULL';
}  // if ($core_director_approval == 'TRUE')
/**********************PRIMARY INVESTIGATOR APPROVAL DATE*********/
// Check to see if the format is valid.
if ($primary_investigator_approval == 'TRUE')
{
  if (trimmed_string_not_empty ($_SESSION['primary_investigator_approval_date']))
  {
    $primary_investigator_approval_date = $_SESSION['primary_investigator_approval_date'];
    $result = pg_query($dbconn,"
     SELECT CAST ('$primary_investigator_approval_date' AS date)");
    if(!$result)
    {
      $error_string = 'Primary Investigator Approval Date not valid: ' .
                      pg_last_error($dbconn);
      array_push($array_error, $error_string);
    } else {
      $primary_investigator_approval_date = "'".
       pg_fetch_result ($result, 0, 0)."'";
    } // if(!$result)
  } else {
    $primary_investigator_approval_date = 'NULL';
  }  // if (trimmed_string_not_empty ($_SESSION['primary_investig...
} else {
  $primary_investigator_approval_date = 'NULL';
}  // if ($primary_investigator_approval == 'TRUE')

if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: update_project_log.php");
  exit;
}
//***********UPDATE PROJECT LOG***************/
  $result = pg_query ($dbconn, "
    UPDATE " . $_SESSION['project_log'] . "
       SET project_uid = $project_uid,
           run_number = $run_number,
           run_date = $run_date,
           experiment_type = '$experiment_type',
           results_summary = '$results_summary',
           account_number = '$account_number',
           estimated_price = $estimated_price,
           estimated_price_comments = '$estimated_price_comments',
           name_of_experimenter = '$name_of_experimenter',
           experiment_title = '$experiment_title',
           lanes = '$lanes',
           hypothesis = '$hypothesis',
           cells_used = '$cells_used',
           experimental_methods = '$experimental_methods',
           time_course = '$time_course',
           experimental_category = '$experimental_category',
           conditions_comments = '$conditions_comments',
           batching_instructions = '$batching_instructions',
           sample_purification_method = '$sample_purification_method',
           kits_used = '$kits_used',
           amplification_method = '$amplification_method',
           barcoding_used = '$barcoding_used',
           new_method_or_comparison_study = '$new_method_or_comparison_study',
           library_prep_comments = '$library_prep_comments',
           read_length = '$read_length',
           adapter_sequences = '$adapter_sequences',
           barcode_sequences = '$barcode_sequences'
    WHERE project_log_uid = ".$_SESSION['project_log_uid']);
  if(!$result)
  {
    echo 'Error updating project log: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
// Update core approval and date.
  if (isset ($_SESSION['admin_role']) &&
      $_SESSION['admin_role'] == 'dac_grants')
  {
    $result = pg_query ($dbconn, "
      UPDATE " . $_SESSION['project_log'] . "
         SET core_director_approval = $core_director_approval,
             core_director_approval_date = $core_director_approval_date
      WHERE project_log_uid = ".$_SESSION['project_log_uid']);
    if(!$result)
    {
      echo pg_last_error($dbconn);
      exit;
    } // if(!$result)
// Update primary investigator approval and date.
  } elseif (isset ($_SESSION['admin_role']) &&
          $_SESSION['admin_role'] == 'pi_admin')
  {
    $result = pg_query ($dbconn, "
      UPDATE " . $_SESSION['project_log'] . "
         SET primary_investigator_approval = $primary_investigator_approval,
             primary_investigator_approval_date =
              $primary_investigator_approval_date
      WHERE project_log_uid = ".$_SESSION['project_log_uid']);
    if(!$result)
    {
      echo pg_last_error($dbconn);
      exit;
    } // if(!$result)
  }  // if (isset ($_SESSION['admin_role']) &&
header("location: project_log.php");
?>
