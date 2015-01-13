<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_contact_info"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/*************************PRIMARY INVESTIGATOR*****************************/
$contact_name = "";
if (!isset($_SESSION['choose_pi']) ||
    $_SESSION['choose_pi'] == '' ||
    $_SESSION['choose_pi'] < 0)
{
   $primary_investigator_uid= -1;
   array_push($array_error,"* You must enter a Primary Investigator");
} else {
  $primary_investigator_uid = $_SESSION['choose_pi'];
}  // if(!isset($_SESSION['choose_pi']) ||...
/*************************LAST NAME*****************************/
$contact_name = "";
if(!isset($_SESSION['last_name']) || $_SESSION['last_name']=='') {
   array_push($array_error,"* You must enter a Last Name");
} else {
  $contact_name = trim($_SESSION['last_name']);
}  // if(!isset($_SESSION['last_name']) || $_SESSION['last_name']=='')
// If there is no pi or no last name, we need not process further.
if(count($array_error) < 1)
{
/*************************FIRST NAME*****************************/
  if(isset($_SESSION['first_name']) &&
     trimmed_string_not_empty ($_SESSION['last_name']))
  {
    // If there is a first name, add it to the contact name.
    if (strlen(trim($_SESSION['first_name'])) > 0)
    {
    $contact_name = $contact_name.', '.trim($_SESSION['first_name']);
    }  // if (strlen(trim($_SESSION['first_name'])) > 0)
  }  // if(isset($_SESSION['first_name']) &&...
  $display_upper_contact_name = strtoupper($contact_name);
  $contact_name = ddl_ready ($contact_name);
  $upper_contact_name = strtoupper($contact_name);
  $result = pg_query($dbconn,"
   SELECT COUNT(1) AS row_count
     FROM contact
    WHERE upper(name) = '$upper_contact_name' AND
          primary_investigator_uid = $primary_investigator_uid");
  if(!$result)
  {
    echo 'Error selecting from contact table: ';
    echo pg_last_error($dbconn);
    exit;
  } elseif ($line = pg_fetch_assoc($result)) {
    if ($line['row_count'] > 0)
    {
      $result_pi = pg_query($dbconn,"
       SELECT name
         FROM primary_investigator
        WHERE primary_investigator_uid = $primary_investigator_uid");
      if(!$result_pi)
      {
        echo 'Error selecting from contact table: ';
        echo pg_last_error($dbconn);
        exit;
      } else {
        $pi_name = pg_fetch_result ($result_pi, 0, 0);
        array_push ($array_error,
         "* A contact named '$display_upper_contact_name'".
         " for '$pi_name'".
         " already exists in the database");
      }  // if(!$result_pi)
    }  // if ($line['row_count'] > 0)
  } // if(!$result)
  
/**********************EMAIL_ADDRESS*******************************************/
  $email_address = ddl_ready ($_SESSION['email_address']);
/**********************PHONE_NUMBER*******************************************/
  $phone_number = ddl_ready ($_SESSION['phone_number']);
  /**********************COMMENTS*******************************************/
  $_SESSION['comments'] = $_SESSION['comments'];
  $comments = ddl_ready ($_SESSION['comments']);
}  // if(count($array_error) < 1)

if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: new_contact.php");
  exit;
}

/***********INSERT CONTACT***************/
if ((isset($_SESSION['first_name']) &&
     trimmed_string_not_empty ($_SESSION['first_name'])) ||
     (isset($_SESSION['last_name'])) &&
     trimmed_string_not_empty ($_SESSION['last_name']))
{
  $result = pg_query ($dbconn, "
   SELECT nextval('contact_contact_uid_seq')");
  if(!$result)
  {
    echo 'Error adding contact: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
  $contact_uid = pg_fetch_result ($result, 0, 0);
  $_SESSION['contact_uid'] = $contact_uid;
  $_SESSION['primary_investigator_uid'] = $primary_investigator_uid;
  $first = trim(str_replace("'","''",$_SESSION['first_name']));
  $last = trim(str_replace("'","''",$_SESSION['last_name']));
  
  $result = pg_query($dbconn,"
    INSERT INTO contact
     (contact_uid, name, primary_investigator_uid,
      email_address, phone_number, comments)
    VALUES
     ($contact_uid, '$contact_name', $primary_investigator_uid,
      '$email_address', '$phone_number', '$comments')");
  if(!$result)
  {
    echo 'Error adding contact: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
}  // if(isset($_SESSION['first_name']) &&...
    
/***********************************************************************/
// Set drop down value to Show All.
$_SESSION['choose_pi_contact_select'] = 'Show All';
clear_contact_vars();
header("location: contact.php");
?>
