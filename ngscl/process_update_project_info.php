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
/*************************PRIMARY INVESTIGATOR*****************************/
if(!isset($_SESSION['choose_pi']) || $_SESSION['choose_pi']=='')
{
   array_push($array_error,"* You must enter a Primary Investigator");
}
else
{
  $primary_investigator_uid = $_SESSION['choose_pi'];
}  // if(!isset($_SESSION['choose_pi']) || $_SESSION['choose_pi']=='')
/*************************PROJECT NAME*****************************/
$project_name = "";
if(!isset($_SESSION['project_name']) || $_SESSION['project_name']=='') {
   array_push($array_error,"* You must enter a Project Name");
}
else
{
  $display_upper_project_name = strtoupper($_SESSION['project_name']);
  $project_name = ddl_ready ($_SESSION['project_name']);
}  // if(!isset($_SESSION['project_name']) || $_SESSION['project_name']=='')
$upper_project_name = strtoupper($project_name);
$result = pg_query($dbconn,"
 SELECT COUNT(1) AS row_count
   FROM project
  WHERE upper(project_name) = '$upper_project_name' AND
        project_uid != ".$_SESSION['project_uid']);
if(!$result)
{
  echo 'Error selecting from project table: ';
  echo pg_last_error($dbconn);
  exit;
} elseif ($line = pg_fetch_assoc($result)) {
  if ($line['row_count'] > 0)
  {
    array_push ($array_error,
     '* A project named "'.$upper_project_name.'" '.
     'already exists in the database');
  }  // if ($line['row_count'] > 0)
} // if(!$result)
/*************************RUN TYPE*****************************/
if(!isset($_SESSION['choose_run_type']) ||
   $_SESSION['choose_run_type']=='' ||
   $_SESSION['choose_run_type'] < 0)
{
   array_push($array_error,"* You must enter a Run Type");
}
else
{
  $ref_run_type_uid = $_SESSION['choose_run_type'];
}  // if(!isset($_SESSION['choose_run_type']) ||...

/**********************STATUS*******************************************/
$status = ddl_ready ($_SESSION['choose_project_status']);
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

/**********************PROJECT DESCRIPTION***********************************/
$project_description = ddl_ready ($_SESSION['project_description']);
/**********************ANALYSIS NOTES***********************************/
$analysis_notes = ddl_ready ($_SESSION['analysis_notes']);
/**********************ADMIN COMMENTS***********************************/
$admin_comments = ddl_ready ($_SESSION['admin_comments']);
/**********************CONTACT_UID*******************************************/
$contact_uid = $_SESSION['choose_contact'];

if(count($array_error) >= 1)
{
  $_SESSION['errors'] = $array_error;
  header("location: update_project_info.php");
  exit;
}

/***********UPDATE PROJECT***************/
if (isset($_SESSION['project_name']) &&
    trimmed_string_not_empty ($_SESSION['project_name']))
{
  // Determine whether contact_uid is NULL.
  if ($contact_uid > 0)
  {
    // Contact is not null.
    $result = pg_query($dbconn,"
      UPDATE project
         SET project_name = '$project_name',
             primary_investigator_uid = $primary_investigator_uid,
             ref_run_type_uid = $ref_run_type_uid,
             status = '$status',
             creation_date = '$creation_date',
             project_description = '$project_description',
             analysis_notes = '$analysis_notes',
             admin_comments = '$admin_comments',
             contact_uid = $contact_uid
       WHERE project_uid = ".$_SESSION['project_uid']);
  } else {
    // Contact is null.
    $result = pg_query($dbconn,"
      UPDATE project
         SET project_name = '$project_name',
             primary_investigator_uid = $primary_investigator_uid,
             ref_run_type_uid = $ref_run_type_uid,
             status = '$status',
             creation_date = '$creation_date',
             project_description = '$project_description',
             analysis_notes = '$analysis_notes',
             admin_comments = '$admin_comments',
             contact_uid = NULL
       WHERE project_uid = ".$_SESSION['project_uid']);
  }  // if ($_SESSION['contact_uid'] > 0)
  if(!$result)
  {
    echo 'Error updating project: ';
    echo pg_last_error($dbconn);
    exit;
  } // if(!$result)
}  // if (isset($_SESSION['project_name']) &&...
    
/***********************************************************************/
header("location: project_info.php");
?>
