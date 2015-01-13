<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_manage_prep_note"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
$library_prep_note_uid = (isset($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
// *****************************************************************
// This function adds the input samples to the input
// library prep note.  Any insert errors are returned.
// *****************************************************************
function add_to_prep_note ($dbconn, $library_prep_note_uid, $sample_uid_array)
{
  // Create an array to hold errors.
  $array_error = array();
  // Add each input sample to the input library prep note.
  foreach ($sample_uid_array as $rowkey => $sample_uid)
  {
    $result_insert = pg_query ($dbconn, "
     INSERT INTO library_prep_note_sample
      (library_prep_note_uid, sample_uid)
     VALUES
      ($library_prep_note_uid, $sample_uid)");
    if (!$result_insert)
    {
      $array_error[] = pg_last_error ($dbconn);
    }  // if (!$result_insert)
  }  // foreach ($sample_uid_array as $rowkey => $sample_uid)
  return $array_error;
}  // function add_to_prep_note
// *****************************************************************
// This function removes the input samples from the input
// library prep note.  Any delete errors are returned.
// *****************************************************************
function remove_from_prep_note ($dbconn,
 $library_prep_note_uid, $sample_uid_array)
{
  // Create an array to hold errors.
  $array_error = array();
  // Delete each input sample from the input library prep note.
  foreach ($sample_uid_array as $rowkey => $sample_uid)
  {
    $result_insert = pg_query ($dbconn, "
     DELETE FROM library_prep_note_sample
      WHERE library_prep_note_uid = $library_prep_note_uid AND
            sample_uid = $sample_uid");
    if (!$result_insert)
    {
      $array_error[] = pg_last_error ($dbconn);
    }  // if (!$result_insert)
  }  // foreach ($sample_uid_array as $rowkey => $sample_uid)
  return $array_error;
}  // function remove_from_prep_note
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_return_to_details']))
    {
      // Return to library prep note details page.
      header ("location: prep_note_details.php");
      exit;
    } elseif (isset($_POST['submit_add_samples'])) {
      // Add the sample_uid_open samples to the prep note.
      $array_error = add_to_prep_note ($dbconn,
       $_SESSION['library_prep_note_uid'], $_SESSION['sample_uid_open']);
      unset($_POST['submit_add_samples']);
    } elseif (isset($_POST['submit_remove_samples'])) {
      // Delete the sample_uid_note samples from the prep note.
      $array_error = remove_from_prep_note ($dbconn,
       $_SESSION['library_prep_note_uid'], $_SESSION['sample_uid_note']);
      unset($_POST['submit_remove_samples']);
    }  // if (isset($_POST['submit_return_to_details']))
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
  // If this is the first time through, get the library prep note name.
  if ($library_prep_note_uid > 0)
  {
    $result_name = pg_query ($dbconn, "
     SELECT library_prep_note_name
       FROM library_prep_note
      WHERE library_prep_note_uid = ".$library_prep_note_uid);
    if (!$result_name)
    {
      $array_error[] = pg_last_error ($dbconn);
      $_SESSION['library_prep_note_name'] = "";
    } else {
      $_SESSION['library_prep_note_name'] = pg_fetch_result($result_name, 0, 0);
    }  // if (!$result_name)
  } else {
    $array_error[] = "No library prep note.";
  }  // if ($library_prep_note_uid > 0)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Manage Prep Note Samples, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<link href="DAC_LIMS_styles.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.twoColElsLtHdr #sidebar1 { padding-top: 30px; }
.twoColElsLtHdr #mainContent { zoom: 1; padding-top: 15px; }
/* the above proprietary zoom property gives IE the hasLayout it needs to avoid several bugs */
</style>
<![endif]-->
<style type="text/css">
<!--
.style1 {font-family: Arial, Helvetica, sans-serif}
.style2 {color: #999999;}
a:link {
  color: #0000FF;
}
a:visited {
  color: #000080;
}
-->
</style>
<style type="text/css">
<!--
.warningtext {
   font-family: Arial, Helvetica, sans-serif;
   font-size: 125%; color:#FF00FF; font-weight: bold;
  }
-->
</style>
<?php
  readfile("text_styles.css");
?>
<script src="library/sorttable.js" 
  language="javascript" 
    type="text/javascript"></script>
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center"><span class="titletext">Manage Prep Note Samples - ',
       $app_name,'</span></h1>';
?>
  <!-- end #header --></div>
  <br /><br />
<?php
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  $library_prep_note_name = (isset ($_SESSION['library_prep_note_name']) ?
   $_SESSION['library_prep_note_name'] : "");
  echo '<h3 class="grayed_out">Library Prep Note: ',
       $library_prep_note_name,'</h3>';
  foreach ($array_error as $error)
  {
    if (strlen (trim ($error)) > 0)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // if (strlen (trim ($error)) > 0)
  }  // foreach ($array_error as $error)
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_manage_prep_note" >';
  echo '<input type="hidden" name="process" value="1" />';
  // The button to return to Prep Note Details
  // should have slightly lareger text.
  echo '<input type="submit" name="submit_return_to_details" ',
       'value="Prep Note Details" class="buttontext" ',
       'title="Return to Library Prep Note Details page." />';
  echo '<hr />';
  // Make a table for the submit buttons.
  echo '<input type="submit" name="submit_remove_samples" class="buttontext" ',
       'value="Remove from Prep Note" ',
       'title="Remove selected samples from library prep note." />&nbsp;';
  // Make a table for the population inputs.
?>
<table id="sample_table" name="sample_table"
 border="1" width="100%" class="sortable" >
<thead>
  <tr>
<?php
  echo '<th class="sorttable_nosort" scope="col" width="2%" ',
       'style="text-align:center;" > ',
       '<input type="checkbox" name="submit_check_all" ',
       'onclick="checkAllTable(\'sample_table\',this)" ',
       'title="Select all items." />',
       '</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center" >Sample</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center" >Project</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center" >PI</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Sample Status</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Barcode</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Barcode Index</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Species</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Type</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Batch Group</th>';
   echo '<th class="sorttable_alpha" scope="col" ',
        'style="text-align:center">Comments</th>';
?>
  </tr>
</thead>
<tbody>
<?php
  if ($library_prep_note_uid > 0)
  {
    $result_td = pg_query ($dbconn, "
     SELECT library_prep_note_sample.sample_uid AS sample_uid_note,
            sample.project_uid,
            project.primary_investigator_uid,
            sample_name,
            project_name,
            name AS primary_investigator_name,
            sample.status,
            barcode,
            barcode_index,
            species,
            sample_type,
            batch_group,
            sample.comments
       FROM library_prep_note_sample,
            sample,
            project,
            primary_investigator
      WHERE library_prep_note_uid = $library_prep_note_uid AND
            library_prep_note_sample.sample_uid = sample.sample_uid AND
            sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid
      ORDER BY primary_investigator_name, project_name, sample_name");
    if (!$result_td)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
        for ($i=0;$i<pg_num_rows($result_td);$i++)
        {
          $row = pg_fetch_assoc ($result_td);
          echo '<tr>';
          $checked_string = "";
          // Determine whether the sample checkbox should be checked.
          if (isset($_POST['process'])) {
            if ($_POST['process'] == 1) {
              $sample_uid_note = $row['sample_uid_note'];
              // If sample was previously selected, mark checkbox as checked.
              if (isset($_SESSION['sample_uid_note']))
              {
                foreach ($_SESSION['sample_uid_note'] as $checkbox_sample_uid)
                {
                  if ($sample_uid_note == $checkbox_sample_uid)
                  {
                    $checked_string = "checked";
                  }  // if ($sample_uid_note == $sample) {
                }  // foreach ($_SESSION['sample_uid_note'] as...
              }  // if (isset($_SESSION['sample_uid_note'])) {
            }  // if ($_POST['process'] == 1) {
          }  // if (isset($_POST['process'])) {
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<input type="checkbox" name="sample_uid_note[]" ',
               $checked_string,
               ' value="',
               $row['sample_uid_note'],
               '" title="Select sample ',$row['sample_name'],'."/></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<a href="javascript:void(0)" onclick="sampleWindow(\'',
               $row['sample_uid_note'],
               '\');" title="Display information on sample ',
               $row['sample_name'],'.">',
               td_ready($row['sample_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<a href="javascript:void(0)" onclick="projectWindow(\'',
               $row['project_uid'],'\');" ',
                 'title="Display information on project ',
               $row['project_uid'],'.">',
               td_ready($row['project_name']),'</a></td>';
          echo '<td class="tdBlueBorder" style="text-align:center"><a ',
               'href="javascript:void(0)" ',
               'onclick="primary_investigatorWindow(\'',
               $row['primary_investigator_uid'],'\');" ',
               'title="Display information on primary investigator ',
               $row['primary_investigator_name'],'.">',
               td_ready($row['primary_investigator_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['status']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['barcode']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['barcode_index']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['species']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['sample_type']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['batch_group']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:left">',
               '<div style="width: 150px; height: 30px; overflow: ',
               'auto; padding: 5px;"><font face="sylfaen">',
               td_ready($row['comments']),
               '</font></div></td>';
          echo '</tr>';
        }  // for ($i=0;$i<pg_num_rows($result_td);$i++)
    }  // if (!$result_td)
  }  // if ($library_prep_note_uid > 0)
  echo '</tbody></table>';
  echo '<hr />';
  echo '<input type="submit" name="submit_add_samples" ',
       'value="Add to Prep Note" class="buttontext" ',
       'title="Add selected samples to library prep note."/>&nbsp;';
  // Make a table for the pull-down lists.
  echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
  // ****
  // This is the pull-down for Sample Status.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext" ><b>',
       'Status</b><br />';
  $status_value = (isset ($_SESSION['choose_sample_status']) ?
   $_SESSION['choose_sample_status']: "");
  echo drop_down_array ('choose_sample_status', $status_value,
                        'inputrow', $array_sample_status_values,
                        'Query by sample status.');
  echo '</td>';
  $status_condition = where_condition ("sample.status", $status_value, 1);
  // ****
  // This is the pull-down for Primary Investigator.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Primary Investigator</b><br />';
  $primary_investigator_uid_value = (isset ($_SESSION['choose_pi']) ?
   $_SESSION['choose_pi'] : "");
  echo drop_down_table ($dbconn, 'choose_pi', $primary_investigator_uid_value,
                        'inputrow', 'primary_investigator', 
                        'primary_investigator_uid', 'name',
                        'Query by primary investigator.');
  echo '</td>';
  $pi_condition = where_condition (
   "primary_investigator.primary_investigator_uid",
   $primary_investigator_uid_value);
  // ****
  // This is the pull-down for Project.
  // ****
  $project_uid_value = (isset ($_SESSION['choose_project']) ?
   $_SESSION['choose_project'] : "");
  // Limit the projects to the selected pi.
  if (strlen (trim ($pi_condition)) > 0)
  {
    // Strip off the primary investigator table name.
    $pi_array = explode (".", $pi_condition);
    $where_clause = " WHERE ".$pi_array[1];
    // See if the project value is not Show All.
    if (isset($project_uid_value) && strlen (trim ($project_uid_value)) > 0)
    {
      if ((strcasecmp (trim ($project_uid_value), trim ("Show All")) != 0) &&
          (strlen (trim ($project_uid_value)) > 0))
      {
      // If the project does not match the pi, set the project value to blank.
      $result_count = pg_query ($dbconn, "
       SELECT COUNT(1) AS row_count
         FROM project ".
       $where_clause.
       " AND project_uid = ".
       $project_uid_value);
      if (!$result_count)
      {
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        if ($line = pg_fetch_assoc ($result_count))
        {
           if ($line['row_count'] < 1)
           {
             $project_uid_value = "Show All";
           }  // if ($line['row_count'] < 1)
        } else {
          echo '<span class="errortext">',pg_last_error($dbconn),
               '</span><br />';
          }  // if ($line = pg_fetch_assoc ($result_count))
        }  // if ((strcasecmp (trim ($project_uid_value), trim ("Show All"))...
      }  // if (!$result_count)
    }  // if (isset($project_uid_value) &&...
  } else {
    $where_clause = "";
  }  // if (strlen (trim ($pi_condition)) > 0)
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Project</b><br />';
  echo drop_down_table ($dbconn, 'choose_project', $project_uid_value,
                        'inputrow', 'project', 
                        'project_uid', 'project_name',
                        'Query by project.', $where_clause);
  echo '</td>';
  $project_condition = where_condition (
   "project.project_uid",
   $project_uid_value);
  echo '</tr>';
  echo '</tbody></table>';
  echo '<table id="add_sample_table" name="add_sample_table" ',
       'border="1" width="100%" class="sortable">';
  echo '<thead><tr>';
  echo '<th class="sorttable_nosort" scope="col" width="2%" ',
       'style="text-align:center;" > ',
       '<input type="checkbox" name="submit_check_all" ',
       'onclick="checkAllTable(\'add_sample_table\',this)" ',
       'title="Select all items." />',
       '</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center" >Sample</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center" >Project</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center" >PI</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Sample Status</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode Index</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Species</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Type</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Batch Group</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Comments</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // Make a clause to query by sample status, pi, and project, as required.
  $where_addendum = "";
  if (strlen (trim ($status_condition)) > 0)
  {
    $where_addendum = $where_addendum." AND ".$status_condition;
  }  // if (strlen (trim ($status_condition)) > 0)
  if (strlen (trim ($pi_condition)) > 0)
  {
    $where_addendum = $where_addendum." AND ".$pi_condition;
  }  // if (strlen (trim ($pi_condition)) > 0)
  if (strlen (trim ($project_condition)) > 0)
  {
    $where_addendum = $where_addendum." AND ".$project_condition;
  }  // if (strlen (trim ($project_condition)) > 0)
  if ($library_prep_note_uid > 0)
  {
    $result_td = pg_query ($dbconn, "
     SELECT sample.sample_uid AS sample_uid_open,
            sample.project_uid,
            project.primary_investigator_uid,
            sample_name,
            project_name,
            name AS primary_investigator_name,
            sample.status,
            barcode,
            barcode_index,
            species,
            sample_type,
            batch_group,
            sample.comments
       FROM sample,
            project,
            primary_investigator
      WHERE sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid ".
            $where_addendum." AND 
            sample.sample_uid NOT IN
            (SELECT library_prep_note_sample.sample_uid
               FROM library_prep_note_sample
              WHERE library_prep_note_uid = $library_prep_note_uid)
      ORDER BY primary_investigator_name, project_name, sample_name");
    if (!$result_td)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
        for ($i=0;$i<pg_num_rows($result_td);$i++)
        {
          $row = pg_fetch_assoc ($result_td);
          echo '<tr>';
          $checked_string = "";
          // Determine whether the sample checkbox should be checked.
          if (isset($_POST['process'])) {
            if ($_POST['process'] == 1) {
              $sample_uid_open = $row['sample_uid_open'];
              // If sample was previously selected, mark checkbox as checked.
              if (isset($_SESSION['sample_uid_open'])) {
                foreach ($_SESSION['sample_uid_open'] as $checkbox_sample_uid)
                {
                  if ($sample_uid_open == $checkbox_sample_uid)
                  {
                    $checked_string = "checked";
                  }  // if ($sample_uid_open == $sample) {
                }  // foreach ($_SESSION['sample_uid_open'] as...
              }  // if (isset($_SESSION['sample_uid_open'])) {
            }  // if ($_POST['process'] == 1) {
          }  // if (isset($_POST['process'])) {
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<input type="checkbox" name="sample_uid_open[]" ',
               $checked_string,
               ' value="',
               $row['sample_uid_open'],
               '" title="Select sample ',
               $row['sample_name'],'." /></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<a href="javascript:void(0)" onclick="sampleWindow(\'',
               $row['sample_uid_open'],
               '\');" title="Display information on sample ',
               $row['sample_name'],'.">',
               td_ready($row['sample_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<a href="javascript:void(0)" onclick="projectWindow(\'',
               $row['project_uid'],'\');" ',
                 'title="Display information on project ',
               $row['project_uid'],'.">',
               td_ready($row['project_name']),'</a></td>';
          echo '<td class="tdBlueBorder" style="text-align:center"><a ',
               'href="javascript:void(0)" ',
               'onclick="primary_investigatorWindow(\'',
               $row['primary_investigator_uid'],'\');" ',
               'title="Display information on primary investigator ',
               $row['primary_investigator_name'],'.">',
               td_ready($row['primary_investigator_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['status']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['barcode']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['barcode_index']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['species']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['sample_type']),'</td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               td_ready($row['batch_group']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:left">',
               '<div style="width: 150px; height: 30px; overflow: ',
               'auto; padding: 5px;"><font face="sylfaen">',
               td_ready($row['comments']),
               '</font></div></td>';
          echo '</tr>';
        }  // for ($i=0;$i<pg_num_rows($result_td);$i++)
      unset($_SESSION['sample_uid_open']);
    }  // if (!$result_td)
  }  // if ($library_prep_note_uid > 0)
  echo '</tbody></table>';
  echo '</form>';
?>
  </div>
  <!-- end #mainContent -->
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
