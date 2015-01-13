<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_prep_note_select"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/**********************LIBRARY PREP NOTE NAME*****************************/
$libray_prep_note_name = "";
if(!isset($_SESSION['library_prep_note_name']) ||
   $_SESSION['library_prep_note_name']=='') {
  array_push($array_error,"You must enter a Library Prep Note Name.");
} else {
  $display_upper_library_prep_note_name = strtoupper (
   $_SESSION['library_prep_note_name']);
  $library_prep_note_name = ddl_ready ($_SESSION['library_prep_note_name']); 
  if (strlen ($library_prep_note_name) < 1)
  {
    array_push($array_error,"You must enter a Library Prep Note Name.");
  }  // if (strlen ($library_prep_note_name) < 1)
}  // if(!isset($_SESSION['library_prep_note_name']) ||
// Check if a library prep note with this name already exists.
$upper_library_prep_note_name = strtoupper($library_prep_note_name);
$result = pg_query ($dbconn, "
 SELECT COUNT(1) AS row_count
   FROM library_prep_note
  WHERE upper(library_prep_note_name) = '$upper_library_prep_note_name'");
if (!result)
{
  echo 'Error selecting from library_prep_note table: ';
  echo pg_last_error($dbconn);
  exit;
} elseif ($line = pg_fetch_assoc($result)) {
  if ($line['row_count'] > 0)
  {
  array_push ($array_error,
   "A library prep note named '$display_upper_library_prep_note_name' ".
   "already exists in the database."); 
  }  // if ($line['row_count'] > 0)
}  // if (!result)
/**********************CREATION_DATE*******************************************/
// Check to see if the format is valid.
$creation_date = "";
if(!isset($_SESSION['creation_date']) || $_SESSION['creation_date']=='')
{
   array_push($array_error,"You must enter a Creation Date");
} else {
  $creation_date = $_SESSION['creation_date'];
  $result = pg_query($dbconn,"
   SELECT CAST ('$creation_date' AS date)");
  if(!$result)
  {
    $error_string = 'Creation date not valid: '.pg_last_error($dbconn);
    array_push($array_error, $error_string);
  } // if(!$result)
}  // if(!isset($_SESSION['creation_date']) || $_SESSION['creation_date']=='')
/**********************COMMENTS*******************************************/
$comments = ddl_ready ($_SESSION['comments']);
//***********INSERT LIBRARY PREP NOTE***************/
if (count ($array_error) < 1)
{
  // Get new library_prep_note_uid from sequence.
  $result = pg_query ($dbconn, "
   SELECT nextval('library_prep_note_library_prep_note_uid_seq')");
  if (!$result)
  {
    echo pg_last_error($dbconn);
    exit;
  } // if (!$result)
  $library_prep_note_uid = pg_fetch_result ($result, 0, 0);
  $_SESSION['library_prep_note_uid'] = $library_prep_note_uid;
  // Determine whether contact_uid is NULL.
  $result = pg_query($dbconn,"
   INSERT INTO library_prep_note
    (library_prep_note_uid, library_prep_note_name,
     creation_date, comments)
      VALUES
       ($library_prep_note_uid, '$library_prep_note_name',
        '$creation_date', '$comments')");
  if(!$result)
  {
    echo 'Error adding library prep note: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
/***********************************************************************/
  clear_prep_note_vars();
  header("location: prep_note_details.php");
  exit;
} else {
  $_SESSION['errors'] = $array_error;
  header("location: new_prep_note.php");
  exit;
}  // if (count ($array_error) < 1)
?>
