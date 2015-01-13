<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_pi_info"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/*************************LAST NAME*****************************/
$pi_name = "";
if(!isset($_SESSION['last_name']) || $_SESSION['last_name']=='') {
   array_push($array_error,"* You must enter a Last Name");
} else {
  $pi_name = trim($_SESSION['last_name']);
}  // if(!isset($_SESSION['last_name']) || $_SESSION['last_name']=='')
/*************************FIRST NAME*****************************/
if(isset($_SESSION['first_name']) &&
   trimmed_string_not_empty ($_SESSION['last_name']))
{
  // If there is a first name, add it to the primary investigator name.
  if (strlen(trim($_SESSION['first_name'])) > 0)
  {
    $pi_name = $pi_name.', '.trim($_SESSION['first_name']);
  }  // if (strlen(trim($_SESSION['first_name'])) > 0)
}  // if(isset($_SESSION['first_name']) &&...
$display_upper_pi_name = strtoupper($pi_name);
$pi_name = ddl_ready ($pi_name);
$upper_pi_name = strtoupper($pi_name);
$result = pg_query($dbconn,"
 SELECT COUNT(1) AS row_count
   FROM primary_investigator
  WHERE upper(name) = '$upper_pi_name'");
if(!$result)
{
  echo '<span>Error selecting from primary_investigator table: ';
  echo pg_last_error($dbconn);
  echo '</span>';
  exit;
} elseif ($line = pg_fetch_assoc($result)) {
  if ($line['row_count'] > 0)
  {
    array_push ($array_error,
     "* A primary investigator named '$display_upper_pi_name'".
     " already exists in the database");
  }  // if ($line['row_count'] > 0)
} // if(!$result)

/**********************STATUS*******************************************/
$status = ddl_ready ($_SESSION['choose_primary_investigator_status']);
/**********************EMAIL_ADDRESS*******************************************/
$email_address = ddl_ready ($_SESSION['email_address']);
/**********************PHONE_NUMBER*******************************************/
$phone_number = ddl_ready ($_SESSION['phone_number']);
/**********************COMMENTS*******************************************/
$comments = ddl_ready ($_SESSION['comments']);

if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: new_primary_investigator.php");
  exit;
}

/***********INSERT PRIMARY INVESTIGATOR***************/
if ((isset($_SESSION['first_name']) &&
    trimmed_string_not_empty($_SESSION['first_name'])) ||
    (isset($_SESSION['last_name']) &&
    trimmed_string_not_empty($_SESSION['last_name'])))
{
  $result = pg_query ($dbconn, "
   SELECT nextval('primary_investigator_primary_investigator_uid_seq')");
  if(!$result)
  {
    echo 'Error adding primary investigator';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
  $primary_investigator_uid = pg_fetch_result ($result, 0, 0);
  $_SESSION['primary_investigator_uid'] = $primary_investigator_uid;
  $first = trim(str_replace("'","''",$_SESSION['first_name']));
  $last = trim(str_replace("'","''",$_SESSION['last_name']));
  $result = pg_query($dbconn,"
    INSERT INTO primary_investigator
     (primary_investigator_uid, name, status,
      email_address, phone_number, comments)
    VALUES
     ($primary_investigator_uid, '$pi_name', '$status',
      '$email_address', '$phone_number', '$comments')");
  if(!$result)
  {
    echo 'Error adding primary investigator';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
}  // if ((isset($_SESSION['first_name']) &&...
    
/***********************************************************************/
// Set drop down query values for project to Show All.
$_SESSION['choose_pi_status_primary_investigator_select'] = 'Show All';
clear_pi_vars();
header("location: primary_investigator.php");
?>
