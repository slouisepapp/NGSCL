<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_prep_note_info"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/*************************LIBRARY PREP NOTE NAME*****************************/
$library_prep_note_name = "";
if(!isset($_SESSION['library_prep_note_name']) || $_SESSION['library_prep_note_name']=='') {
  array_push($array_error,"You must enter a Library Prep Note Name");
}
else
{
  $library_prep_note_name = trim(str_replace("'","''",$_SESSION['library_prep_note_name'])); 
  if (strlen (trim ($library_prep_note_name)) < 1)
  {
    array_push($array_error,"You must enter a Library Prep Note Name");
  }  // if (strlen (trim ($library_prep_note_name)) < 1)
}  // if(!isset($_SESSION['library_prep_note_name']) || $_SESSION['library_prep_note_name']=='')
$upper_library_prep_note_name = strtoupper($library_prep_note_name);
$result = pg_query($dbconn,"
 SELECT COUNT(1) AS row_count
   FROM library_prep_note
  WHERE upper(library_prep_note_name) = '$upper_library_prep_note_name' AND
        library_prep_note_uid != ".$_SESSION['library_prep_note_uid']);
if(!$result)
{
  echo '<span>Error selecting from library_prep_note table: ';
  echo pg_last_error($dbconn);
  echo '</span>';
  exit;
} elseif ($line = pg_fetch_assoc($result)) {
  if ($line['row_count'] > 0)
  {
    array_push ($array_error,
     "* A library prep note named '$upper_library_prep_note_name'".
     " already exists in the database");
  }  // if ($line['row_count'] > 0)
} // if(!$result)

/**********************CREATION_DATE*******************************************/
// Check to see if the format is valid.
$creation_date = "";
if(!isset($_SESSION['creation_date']) || $_SESSION['creation_date']=='')
{
   array_push($array_error,"* You must enter a Creation Date");
}
else
{
  $creation_date = $_SESSION['creation_date'];
  $result = pg_query($dbconn,"
   SELECT CAST ('$creation_date' AS date)");
  if(!$result)
  {
    $error_string = 'Creation date not valid.';
    array_push($array_error, $error_string);
  } // if(!$result)
}  // if(!isset($_SESSION['creation_date']) || $_SESSION['creation_date']=='')

/**********************COMMENTS*******************************************/
$comments = ddl_ready ($_SESSION['comments']);
/********Return to update_prep_note_info page if errors were found ********/
if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: update_prep_note_info.php");
  exit;
}
/***********UPDATE LIBRARY PREP NOTE***************/
if (isset($_SESSION['library_prep_note_name']) &&
    trimmed_string_not_empty ($_SESSION['library_prep_note_name']))
{
  $result = pg_query($dbconn,"
   UPDATE library_prep_note
      SET library_prep_note_name = '$library_prep_note_name',
          creation_date = '$creation_date',
          comments = '$comments'
    WHERE library_prep_note_uid = ".$_SESSION['library_prep_note_uid']);
  if(!$result)
  {
    echo 'Error updating library prep note: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
}  // if(isset($_SESSION['library_prep_note_name']) &&
    
/***********************************************************************/
header("location: prep_note_info.php");
?>
