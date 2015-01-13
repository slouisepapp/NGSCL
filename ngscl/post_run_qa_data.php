<?php
session_start();
$order_last = "ZZZZZZZZZ";
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_return") &&
      ($thislabel != "form_file") &&
      ($thislabel != "form_drop_down"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once 'db_fns.php';
require_once('post_run_qa_functions.php');
require_once 'run_functions.php';
require_once 'constants.php';
require_once 'user_view.php';
$dbconn = database_connect();
$qa_report_header = "Lane,Primary Investigator,Project,Sample," .
 "Barcode,Barcode Index,Species,Yield (Mbases)," .
 "% PF,# Reads,% of raw clusters per lane," .
 "% of >= Q30 Bases (PF),Mean Quality Score (PF)";
$array_error = array();
$array_warning = array();
// Set run variables.
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
$_SESSION['run_number_name'] = "";
$_SESSION['run_type'] = "";
if ($run_uid > 0)
{
  // Set the header for the QA report.
  $qa_report = $qa_report_header . "\r\n";
  // Get run number and name.
  $result = pg_query ($dbconn, "
   SELECT run_number || '/' || run_name,
          $run_view.ref_run_type_uid,
          run_type
     FROM $run_view,
          ref_run_type
    WHERE run_uid = $run_uid AND
          $run_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
  } else {
    $_SESSION['run_number_name'] = pg_fetch_result ($result, 0, 0);
    $ref_run_type_uid = pg_fetch_result ($result, 0, 1);
    $_SESSION['run_type'] = pg_fetch_result ($result, 0, 2);
  }  // if (!$result)
} else {
  $array_error[] = "No run selected.";
}  // if ($run_uid > 0)
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  // Set response to QA samples missing from projects.
  $missing_response = (isset ($_SESSION['missing_response']) ?
   $_SESSION['missing_response'] : 'skip_samples');
  // Set response to QA samples missing from projects.
  if ($missing_response == 'add_to_project')
  {
    $add_checked = 'checked="checked"';
    $skip_checked = '';
    $add_samples_to_project = 1;
  } else {
    $add_checked = '';
    $skip_checked = 'checked="checked"';
    $add_samples_to_project = 0;
  }  // if ($missing_response == 'skip_samples')
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['download_qa_report']))
    {
      header("Pragma: public");
      header("Expires: 0"); // set expiration time
      header("Content-Type: application/octet-stream");
      header('Content-Disposition: attachment; filename="qa_data_report.csv"');
      header("Connection: Close");
      if ($run_uid > 0)
      {
        if (isset($_SESSION['qa_report']))
        {
          $qa_report = $_SESSION['qa_report'];
          echo ($qa_report);
        }  // if (isset($_SESSION['qa_report']))
      }  // if ($run_uid > 0)
      exit;
    } elseif (isset($_POST['submit_return_to_details'])) {
      header("location: run_details.php");
      exit;
    }  // if (isset($_POST['download_qa_report']))
    if ($run_uid > 0)
    {
      if (isset($_POST['submit_upload_file']))
      {
        // Upload the user file if it is not greater than the maximum file size.
        $message = upload_text_file($upload_dir, $_POST['MAX_FILE_SIZE']);
        if (isset($_FILES['userfile']))
        {
          if (($_FILES['userfile']['size'] > 0) &&
              ($_FILES['userfile']['error'] == UPLOAD_ERR_OK))
          {
            $sample_lines = file($upload_dir.'/'.$_FILES['userfile']['name']);
            // Check that all the required fields are present.
            $header_line = rtrim ($sample_lines[0], ",\r\n");
            $field_header = str_getcsv ($header_line);
            $array_fields_error = check_upload_qa_fields (
             $array_require_qa_fields, $field_header);
            if (count ($array_fields_error) > 0)
            {
              $array_error = array_merge (
               $array_error, $array_fields_error);
            }  // if (trimmed_string_not_empty ($error_msg))
            if (count ($array_error) < 1)
            {
              // Create an array of the run_lane_uid for each lane.
              $lane_uid_array = all_lane_uids_for_run (
               $dbconn, $run_uid, $num_run_lanes);
              // Create an array of the upload file data.
              $qa_array = array_from_qa_file (
               $dbconn, ",", $run_uid, $field_header,
               $array_require_qa_fields, $lane_uid_array,
               $sample_lines, $add_samples_to_project);
               // Check for duplicate samples to add to a project.
               if ($add_samples_to_project)
                 $array_error = array_merge ($array_error,
                  dup_add_samples_in_qa_file ($qa_array));
               if (count ($array_error) < 1)
               {
                 // Generate appropriate warnings for file sample lines.
                 $array_upload_warning = upload_stats_warnings (
                  $dbconn, $run_uid, $qa_array, ",");
                 if (count ($array_upload_warning) > 0)
                   $array_warning = array_merge (
                    $array_warning, $array_upload_warning);
                 // Upload QA data from file sample lines.
                 $array_upload_error = upload_stats_to_qa (
                  $dbconn, $run_uid, $lane_uid_array, $qa_array);
                 if (count ($array_upload_error) > 0)
                   $array_error = array_merge (
                    $array_error, $array_upload_error);
               }  // if (count ($array_error) < 1)
            }  // if (count ($array_error) < 1)
          }  //if (($_FILES['userfile']['size'] > 0)...
          // Delete the upload file.
          if ($_FILES['userfile']['size'] > 0)
            unlink($upload_dir.'/'.basename($_FILES['userfile']['name']));
        }  // if (isset($_FILES['userfile']))
      } elseif (isset($_POST['submit_clear_qa'])) {
        $error_msg = clear_lane_qa ($dbconn, $run_uid);
        if (strlen (trim ($error_msg)) >0)
          $array_error[] = $error_msg;
      }  // if (isset($_POST['submit_upload_file']))
    }  // if ($run_uid > 0)
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
  // Set response to QA samples missing from projects.
  $missing_response = 'skip_samples';
  $_SESSION['missing_response'] = $missing_response;
  $add_checked = '';
  $skip_checked = 'checked="checked"';
  $add_samples_to_project = 1;
}  // if (isset($_POST['process']))
// Set drop down restrictions.
// Primary investigator condition.
$primary_investigator_uid_value = (isset (
  $_SESSION['choose_pi']) ? $_SESSION['choose_pi'] : "");
$parameter1 = $primary_investigator_view . '.primary_investigator_uid';
$pi_condition = where_condition (
 $parameter1, $primary_investigator_uid_value);
// Project condition.
$project_uid_value = (isset (
 $_SESSION['choose_project']) ? $_SESSION['choose_project'] : "");
$parameter1 = $project_view . '.project_uid';
$project_condition = where_condition ($parameter1, $project_uid_value);
// Make a clause to query by pi and project, as required.
$where_addendum = "";
if (strlen (trim ($pi_condition)) > 0)
{
  $where_addendum .= " AND " . $pi_condition;
}  // if (strlen (trim ($pi_condition)) > 0)
if (strlen (trim ($project_condition)) > 0)
{
  $where_addendum .= " AND " . $project_condition;
}  // if (strlen (trim ($project_condition)) > 0)
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Post-Run QA Data, ',$abbreviated_app_name,'</title>';
?>
<script src="http://code.jquery.com/jquery-2.0.0.js"></script>
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
  echo '<h1 style="text-align:center"><span class="titletext">',
       'Post-Run QA Data - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  echo '<h3 class="grayed_out">Run: ',$_SESSION['run_number_name'],'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$_SESSION['run_type'],'</h3>';
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_return" >';
  if (count ($array_error) > 1)
  {
    echo '<span class="errortext">Errors:</span><br />';
  } elseif (count ($array_error) > 0) {
    echo '<span class="errortext">Error:</span><br />';
  }  // if (count ($array_error) > 1)
  foreach ($array_error as $error)
    if (strlen (trim ($error)) > 0)
      echo '<span class="errortext">'.$error.'</span><br />';
  if (count ($array_error) > 0)
    echo '<span class="errortext">Correct and resubmit.</span><br />';
  // Check for samples in the run with the wrong run type.
  $mismatch_string = run_sample_type_mismatch ($dbconn, $run_uid);
  if (strlen (trim ($mismatch_string)) > 0);
  {
    echo '<span class="errortext">',$mismatch_string,'</span><br />';
  }  // if (strlen (trim ($mismatch_string)) > 0);
  // Print appropriate warning messages.
  echo '<input type="hidden" name="process" value="1" />';
  if (count ($array_warning) > 1)
  {
    echo '<span class="cautiontext">Warnings:</span><br />';
  } elseif (count ($array_warning) > 0) {
    echo '<span class="cautiontext">Warning:</span><br />';
  }  // if (count ($array_warning) > 1)
  foreach ($array_warning as $caution)
    echo '<span class="cautiontext">',$caution,'</span><br />';
  if ($_SESSION['app_role'] == 'dac_grants')
    echo '<input type="submit" name="submit_clear_qa" ',
       'value="Clear QA" ',
       ' onclick="return confirm(\'Are you sure you want to remove all the QA data for this lane?\');" ',
       'title="Remove all QA data from this run." ',
       'class="buttontext" />';
  echo '<input type="submit" name="download_qa_report"',
       'value="QA Report"',
       'title="Download QA data to a comma-separated variable file." />';
  echo '<input type="submit" name="submit_return_to_details" ',
       'value="Run Details" class="buttontext" ',
       'title="Return to Run Details page." />';
  // ****
  // Determine what to do if there are QA samples
  // that are from the project.
  // ****
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    echo '<br /><br /><h3>What to do if samples in upload file ',
         'are unknown to the database.</h3>';
    echo '<input type="radio" name="missing_response" value="add_to_project" ',
         ' onclick="this.form.submit();" ',
         $add_checked,
         ' title="If samples in the upload file are not found ',
         'in the database then add them to the project and the run." ',
         '/>Add to database<br />';
    echo '<input type="radio" name="missing_response" value="skip_samples" ',
         ' onclick="this.form.submit();" ',
         $skip_checked,
         ' title="Ignore any samples in the upload file ',
         'that are not found in the database." ',
         '/>Ignore unknown samples<br /><br />';
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo '</form>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    echo '<form enctype="multipart/form-data" method="post" ',
         'style="width:700px;" action="',
         $_SERVER['PHP_SELF'],
         '" name="form_file">';
    echo '<input type="hidden" name="process" value="1" />';
    echo '<table class="tableNoBorder">';
    echo '<thead><tr><th class="thNoBorder"><b>Upload QA Data File</b></th>',
         '</tr></thead>';
    echo '<tbody><tr>';
    echo '<td><input type="hidden" name="MAX_FILE_SIZE" value="',
         $max_text_file_size,'" />';
    echo '<input name="userfile" type="file" ',
         'title="Choose a comma-separated file of QA data for the lane." ',
         ' class="inputtext" /></td>';
    echo '<td><input type="submit" value="Upload csv File" ',
         'name="submit_upload_file" ',
         ' title="Comma-separated file of QA data for the lane." /></td>';
    echo '<td><input type="button" value="See Demulitplex Fields" ',
         'title="Lists the required column headers for the ',
         'comma-separated file of QA data." ',
         ' onclick="javascript:sizable_example_pop_up(',
         '\'demultiplex_fields.php\',300,400)" ',
         '/></td>';
    echo '</tr></tbody>';
    echo '</table>';
    echo '</form>';
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_drop_down" >';
  echo '<input type="hidden" name="process" value="1" />';
  echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
  // ****
  // This is the pull-down for Primary Investigator.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Primary Investigator</b><br />';
  echo drop_down_run_pis ($dbconn, $run_uid, 'choose_pi',
                          $primary_investigator_uid_value,
                          'inputrow', $primary_investigator_view, 
                          'primary_investigator_uid',
                          'primary_investigator',
                          'Query for samples of this '.
                          'primary investigator.');
  echo '</td>';
  // ****
  // This is the pull-down for Project.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Project</b><br />';
  echo drop_down_run_projects ($dbconn, $run_uid, 'choose_project',
                               $project_uid_value, 'inputrow',
                               $project_view, 'project_uid',
                               'project_name',
                               'Query for samples of this project.');
  echo '</td>';
  echo '</tr></tbody></table>';
  echo '</form>';
  // Make a table for the lane samples.
  echo '<table id="sample_table" name="sample_table" ',
       'border="1" width="100%" class="sortable" >';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Lane</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Primary Investigator</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Project</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Sample</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode Index</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Species</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center">Yield (Mbases)</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center">% PF</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center"># Reads</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center">',
       '% of raw clusters per lane</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center">% of >= Q30 Bases (PF)</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center">Mean Quality Score (PF)</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // ************
  // Find the Post-Run QA data.
  // ************
  // Loop through all lanes.
  for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
  {
    $run_lane_uid = find_run_lane ($dbconn, $run_uid, $lane_number);
    $from_and_where = "FROM $run_lane_view,
           $run_lane_sample_view,
           $sample_view,
           $project_view,
           $primary_investigator_view
     WHERE run_uid = ". $run_uid. " AND
           lane_number = ". $lane_number. " AND
           $run_lane_view.run_lane_uid =
            $run_lane_sample_view.run_lane_uid AND
           $run_lane_sample_view.sample_uid = $sample_view.sample_uid AND
           $sample_view.project_uid = $project_view.project_uid AND
           $project_view.primary_investigator_uid =
            $primary_investigator_view.primary_investigator_uid ".
     $where_addendum;
    // ****
    // Query combines lane samples and lanes without any samples.
    // ****
    $result_lane = pg_query ($dbconn, "
       SELECT lane_number,
              0 AS is_undetermined_indices,
              $primary_investigator_view.primary_investigator_uid,
              $primary_investigator_view.name AS
               primary_investigator,
              $project_view.project_uid,
              project_name,
              $sample_view.sample_uid,
              sample_name,
              barcode,
              barcode_index,
              species,
              $run_lane_sample_view.yield,
              $run_lane_sample_view.percent_pf,
              $run_lane_sample_view.num_reads,
              $run_lane_sample_view.percent_raw_clusters,
              $run_lane_sample_view.percent_ge_q30_bases,
              $run_lane_sample_view.mean_quality_score " .
     $from_and_where .
     " UNION
       SELECT lane_number,
              1 AS is_undetermined_indices,
              0 AS primary_investigator_uid,'" .
              $order_last . "' AS primary_investigator,
              0 AS project_uid,'" .
              $order_last . "' AS project_name,
              0 AS sample_uid,
              'Undetermined Indices' AS sample_name,
              'Clusters with unmatched barcodes for lane  ' ||
              $lane_number AS barcode,
              'Undetermined' AS barcode_index,
              'Unknown' AS species,
              und_yield AS yield,
              und_percent_pf AS percent_pf,
              und_num_reads AS num_reads,
              und_percent_raw_clusters AS percent_raw_clusters,
              und_percent_ge_q30_bases AS percent_ge_q30_bases,
              und_mean_quality_score AS mean_quality_score " .
     $from_and_where .
      " ORDER BY lane_number,
                 project_name,
                 sample_name");
    if (!$result_lane)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
      for ($i=0;$i<pg_num_rows($result_lane);$i++)
      {
        $td_lane_class = 'class="tdBlueBorder"';
        echo '<tr >';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             $lane_number,'</td>';
        $row_lane = pg_fetch_assoc ($result_lane);
        if ($row_lane['is_undetermined_indices'])
        {
          $primary_investigator = "Undetermined Indices";
          $mod_project_name = "";
        } else {
          $primary_investigator = $row_lane[
           'primary_investigator'];
          $mod_project_name = convert_to_alphanum_plus_underscore_only (
           $row_lane['project_name']);
        }  // if ($is_undetermined_indices)
        if (trimmed_string_not_empty ($primary_investigator))
        { 
          $qa_report .= $row_lane['lane_number'] . ',"' .
            $primary_investigator . '","' .
            $mod_project_name . '","' .
            $row_lane['sample_name'] . '","' .
            $row_lane['barcode'] . '","' .
            $row_lane['barcode_index'] . '","' .
            $row_lane['species'] . '",' .
            $row_lane['yield'] . ',' .
            $row_lane['percent_pf'] . ',' .
            $row_lane['num_reads'] . ',' .
            $row_lane['percent_raw_clusters'] . ',' .
            $row_lane['percent_ge_q30_bases'] . ',' .
            $row_lane['mean_quality_score'] . ',' .
            "\r\n";
        }  // if (trimmed_string_not_empty ($primary_investigator))
        // ****
        // Check if the barcode for this row is one of the
        // duplicate barcodes for this lane.
        // ****
        if ($row_lane['primary_investigator_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" ',
               'onclick="primary_investigatorWindow(\'',
               $row_lane['primary_investigator_uid'],'\');" ',
               'title="Display information on primary investigator ',
               $primary_investigator,'." >',
               td_ready ($primary_investigator),'</a></td>';
        } elseif (trimmed_string_not_empty (
          $primary_investigator)) {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               $primary_investigator,'</td>';
        } else {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               '&nbsp;</td>';
        }  // if ($row_lane['primary_investigator_uid'] > 0)
        if ($row_lane['project_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" ',
               'onclick="projectWindow(\'',
               $row_lane['project_uid'],'\');" ',
               'title="Display information on project ',
               $mod_project_name,'." >',
               td_ready ($mod_project_name),'</a></td>';
        } else {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready ($mod_project_name),'</td>';
        }  // if ($row_lane['primary_investigator_uid'] > 0)
        if ($row_lane['sample_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" onclick="sampleWindow(\'',
               $row_lane['sample_uid'],'\');" ',
               'title="Display information on sample ',
               $row_lane['sample_name'],'." >',
               td_ready ($row_lane['sample_name']),'</a></td>';
        } elseif ($row_lane['is_undetermined_indices']) {
          echo '<td ',
               $td_lane_class,
               ' style="text-align:center">',
               td_ready ($row_lane['sample_name']),'</td>';
        } else {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               '&nbsp;</td>';
        }  // if ($row_lane['sample_uid'] > 0)
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['barcode']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['barcode_index']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['species']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (easy_int_format ($row_lane['yield'])),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (decimal2_format ($row_lane['percent_pf'])),
             '</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (easy_int_format ($row_lane['num_reads'])),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (decimal2_format ($row_lane['percent_raw_clusters'])),
             '</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (decimal2_format ($row_lane['percent_ge_q30_bases'])),
             '</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready (decimal2_format (
              $row_lane['mean_quality_score'])),
             '</td>';
        echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result_lane);$i++)
    }  //if (!$result_lane)
  }  // for ($lane_number=1; $lane_number <=...
  echo '</tbody>';
  echo '</table>';
  $_SESSION['qa_report'] = $qa_report;
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

