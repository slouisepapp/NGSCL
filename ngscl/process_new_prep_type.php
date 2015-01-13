<?php
session_start();
require_once('db_fns.php');
$dbconn = database_connect();
$array_error = array();
// Put POST values into SESSION.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel!= "PHPSESSID") &&
      ($thislabel != "form_project_select"))
    $_SESSION[$thislabel] = $thisvalue; 
  }  // foreach ($_POST as $thislabel => $thisvalue)
/************************ALL POST VARIABLES****************************/
/*************************PREP TYPE*****************************/
$prep_type = "";
if(!isset($_SESSION['prep_type']) || $_SESSION['prep_type']=='')
{
   array_push($array_error,"You must enter a Prep Type");
} else {
  $prep_type = ddl_ready ($_SESSION['prep_type']); 
  // Check that prep type contains only valid characters.
  if (!alphanum_plus_underscore_only ($prep_type))
  {
     array_push($array_error,"Only alphanumeric characters and " .
                "underscores are permitted.");
  } else {
    // Check if a prep type with this name already exists.
    $upper_prep_type = strtoupper($prep_type);
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM ref_prep_type
      WHERE upper(prep_type) = '$upper_prep_type'");
    if (!$result)
    {
      $array_error[] = pg_last_error ($dbconn);
    } elseif ($line = pg_fetch_assoc($result)) {
      if ($line['row_count'] > 0)
      {
        array_push ($array_error,
         'Prep type "'.$upper_prep_type.'" '.
         'already exists in the database.'); 
      }  // if ($line['row_count'] > 0)
    }  // if (!$result)
  }  // if (!alphanum_plus_underscores_only ($prep_type))
}  // if(!isset($_SESSION['prep_type']) || $_SESSION['prep_type']=='') {
if (count ($array_error) > 0)
{
  $_SESSION['errors'] = $array_error;
  header("location: add_prep_type.php");
  exit;
}  // if (count ($array_error) > 0)
//***********INSERT PREP TYPE***************/
if(isset($_SESSION['prep_type']) &&
   trimmed_string_not_empty ($_SESSION['prep_type']))
{
  // Get new ref_prep_type_uid from sequence.
  $result = pg_query ($dbconn, "
   SELECT nextval('ref_prep_type_ref_prep_type_uid_seq')");
  if(!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
    $_SESSION['errors'] = $array_error;
    header("location: add_prep_type.php");
    exit;
  } // if(!$result)
  $ref_prep_type_uid = pg_fetch_result ($result, 0, 0);
  $_SESSION['ref_prep_type_uid'] = $ref_prep_type_uid;
  $result = pg_query ($dbconn,"
   INSERT INTO ref_prep_type
    (ref_prep_type_uid, prep_type)
   VALUES
    ($ref_prep_type_uid, '$prep_type')");
  if(!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
    $_SESSION['errors'] = $array_error;
    header("location: add_prep_type.php");
    exit;
  } // if(!$result)
}  // if(isset($_SESSION['prep_type']) &&...
/***********************************************************************/
$_SESSION['choose_prep_type'] = $ref_prep_type_uid;
header("location: manage_barcodes.php");
?>
