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
if (isset ($_SESSION['ref_barcode_uid']) &&
    trimmed_string_not_empty ($_SESSION['ref_barcode_uid']))
{
  $ref_barcode_uid = $_SESSION['ref_barcode_uid'];
} else {
  array_push($array_error,"Ref_barcode_uid is not set.");
}  // if (isset ($_SESSION['ref_barcode_uid']))
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
  header("location: update_barcode.php");
  exit;
}  // if (count ($array_error) > 0)
//***********UPDATE BARCODE***************/
if (isset($_SESSION['barcode_index']) &&
    trimmed_string_not_empty ($_SESSION['barcode_index']))
{
  $result = pg_query ($dbconn,"
   UPDATE ref_barcode
      SET barcode_index = '$barcode_index'
    WHERE ref_barcode_uid = $ref_barcode_uid");
  if(!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
    $_SESSION['errors'] = $array_error;
    header("location: update_barcode.php");
    exit;
  } // if(!$result)
}  // if (isset($_SESSION['barcode_index']) &&...
/***********************************************************************/
header("location: manage_barcodes.php");
?>
