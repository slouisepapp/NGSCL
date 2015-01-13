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
/************************ALL SESSION VARIABLES****************************/
if (isset ($_SESSION['choose_prep_type']) &&
    trimmed_string_not_empty ($_SESSION['choose_prep_type']))
{
  $ref_prep_type_uid = $_SESSION['choose_prep_type'];
} else {
  array_push($array_error,"Ref_prep_type_uid is not set.");
}  // if (isset ($_SESSION['choose_prep_type']) &&...
$barcode_number = (isset ($_SESSION['barcode_number']) ?
 $_SESSION['barcode_number'] : 0);
if ($barcode_number < 1)
{
  array_push($array_error,"Barcode number is not set.");
}  // if (isset ($_SESSION['barcode_number']) &&...
/************************ALL POST VARIABLES****************************/
/*************************BARCODE INDEX*****************************/
$barcode_index = "";
if(!isset($_SESSION['barcode_index']) || $_SESSION['barcode_index']=='') {
   array_push($array_error,"You must enter a Barcode Index");
} else {
  $barcode_index = ddl_ready ($_SESSION['barcode_index']); 
}  // if(!isset($_SESSION['barcode_index']) || $_SESSION['barcode_index']=='') {
// Check if the barcode index consists only of DNA nucleotide characters.
$barcode_index = strtoupper($barcode_index);
if (! dba_nt_only ($barcode_index))
{
  $array_error[] = "Barcode index may only contain the characters " .
                   "A, C, G, and T.";
}  // if (! dba_nt_only ($barcode_index))
if (count ($array_error) > 0)
{
  $_SESSION['errors'] = $array_error;
  header("location: add_barcode.php");
  exit;
}  // if (count ($array_error) > 0)
//***********INSERT NEW BARCODE***************/
if (trimmed_string_not_empty ($barcode_index))
{
  $result = pg_query ($dbconn,"
   INSERT INTO ref_barcode
    (ref_prep_type_uid, barcode_number, barcode_index)
   VALUES
    ($ref_prep_type_uid, $barcode_number, '$barcode_index')");
  if(!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
    $_SESSION['errors'] = $array_error;
    header("location: add_barcode.php");
    exit;
  } // if(!$result)
}  // if (trimmed_string_not_empty ($barcode_index))
/***********************************************************************/
header("location: manage_barcodes.php");
?>
