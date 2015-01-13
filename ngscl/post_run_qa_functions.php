<?php
// **************************************************************
// This function checks that the required fields
// are part of the upload QA file.
// **************************************************************
function check_upload_qa_fields ($array_require_qa_fields, $fields)
{
  $array_error = array();
  foreach ($array_require_qa_fields as $value)
    $array_require_headers[] = $value['header'];
  $missing_from_upload = array_diff ($array_require_headers, $fields);
  foreach ($missing_from_upload as $missing_field)
    $array_error[] = "\"" .
                     $missing_field .
                     "\" is missing from upload file.";
  return $array_error;
}  // function check_upload_qa_fields
// *************************
// This function generates appropriate warnings for the
// upload QA file.
// *************************
function upload_stats_warnings ($dbconn, $run_uid, $qa_array, $delimiter)
{
  $array_warning = array();
  // ***
  // List any pre-existing warnings from input array.
  // ***
  foreach ($qa_array as $array_row => $qa_to_lane_row)
  {
    $data_row = $array_row + 1;
    if (trimmed_string_not_empty ($qa_to_lane_row['warning']))
    {
      $array_warning[] = "Data row " . $data_row .
       ": " . $qa_to_lane_row['warning'];
    }  // if (trimmed_string_not_empty ($qa_to_lane_row['warning']))
  }  // foreach ($qa_array as $qa_to_lane_row)
  return $array_warning;
}  // function upload_stats_warnings
// *************************
// This function uploads the Post-run QA data to the run.
// *************************
function upload_stats_to_qa (
 $dbconn, $run_uid, $lane_uid_array, $qa_array)
{
  // Initialize variables.
  $array_update = array();
  $array_error = array();
  // Loop through sample lines.
  foreach ($qa_array as $qa_key => $qa_to_lane_row)
  {
    $sample_error = "";
    $data_row = $qa_key + 1;
    if ($qa_to_lane_row['unmatchable'] < 1)
    {
      $lane_number = $qa_to_lane_row['lane_number'];
      $run_lane_uid = $lane_uid_array[$lane_number];
      // Set the QA statisticss based on the input string.
      $barcode_index = $qa_to_lane_row['barcode_index'];
      $yield = str_replace (",", "", $qa_to_lane_row['yield']);
      $percent_pf = $qa_to_lane_row['percent_pf'];
      $num_reads = str_replace (",", "", $qa_to_lane_row['num_reads']);
      $percent_raw_clusters = $qa_to_lane_row['percent_raw_clusters'];
      $percent_ge_q30_bases = $qa_to_lane_row['percent_ge_q30_bases'];
      $mean_quality_score =  str_replace (
       ",", "", $qa_to_lane_row['mean_quality_score']);
      // Determine if this is a sample row or the lane summary row.
      if ($barcode_index != "Undetermined")
      {
        // ***
        // Add this sample to the project and run
        // if this is indicated.
        // ***
        if ($qa_to_lane_row['add_to_run'] == 1)
        {
          if ($run_lane_uid < 0)
          {
            $sample_error .= "Data row " . $data_row .
             ": Unable to add sample " . $sample_name .
             " to run as lane " . $lane_number .
             " does not exist in database.";
          } else {
            $project_uid = $qa_to_lane_row['project_uid'];
            $sample_name = $qa_to_lane_row['sample_name'];
            $barcode = $qa_to_lane_row['barcode'];
            $species = $qa_to_lane_row['species'];
            $add_to_project = $qa_to_lane_row['add_to_project'];
            $sample_uid = add_qa_sample_to_database (
             $dbconn, $run_lane_uid, $project_uid,
             $sample_name, $barcode, $barcode_index,
             $species, $add_to_project);
            if ($sample_uid < 1)
              $sample_error .= "Data row " . $data_row .
               ": Unable to add sample " . $sample_name .
               " to database. Check that it does not already exist.";
          }  // if ($run_lane_uid < 0)
        } else {
          $sample_uid = $qa_to_lane_row['sample_uid'];
        }  // if ($qa_to_lane_row['add_to_project'] == 1)
        if (!trimmed_string_not_empty ($sample_error))
        {
          // Check if the QA values are within required limits.
          $sample_tag = "Sample " . $qa_to_lane_row['sample_name'];
          $sample_error = check_qa_limits (
           $sample_tag, $yield, $percent_pf, $num_reads,
           $percent_raw_clusters, $percent_ge_q30_bases,
           $mean_quality_score);
          if (!trimmed_string_not_empty ($sample_error))
          {
            // Update the qa stats for the run lane sample.
            $array_update[] = "
             UPDATE run_lane_sample
                SET yield = $yield,
                    percent_pf = $percent_pf,
                    num_reads = $num_reads,
                    percent_raw_clusters = $percent_raw_clusters,
                    percent_ge_q30_bases = $percent_ge_q30_bases,
                    mean_quality_score = $mean_quality_score
              WHERE sample_uid = $sample_uid AND
                    run_lane_uid = $run_lane_uid";
          } else {
            $sample_error = "Data row " . $data_row . ": " . $sample_error;
          }  // if (!trimmed_string_not_empty ($sample_error))
        }  // if (!trimmed_string_not_empty ($sample_error))
      } else {
        $sample_tag = "Undetermined Indices";
        $sample_error .= check_qa_limits (
         $sample_tag, $yield, $percent_pf, $num_reads,
         $percent_raw_clusters, $percent_ge_q30_bases,
         $mean_quality_score);
        if (!trimmed_string_not_empty ($sample_error))
        {
          // Update the qa stats for the run lane sample.
          $array_update[] = "UPDATE run_lane
              SET und_yield = $yield,
                  und_percent_pf = $percent_pf,
                  und_num_reads = $num_reads,
                  und_percent_raw_clusters = $percent_raw_clusters,
                  und_percent_ge_q30_bases = $percent_ge_q30_bases,
                  und_mean_quality_score = $mean_quality_score
            WHERE run_lane_uid = $run_lane_uid";
        } else {
          $sample_error = "Data row " . $data_row . ": " . $sample_error;
        }  // if (!trimmed_string_not_empty ($sample_error))
      }  // if ($barcode_index != "Undetermined")
    }  // if ($qa_to_lane_row['unmatchable'] < 1)
    if (trimmed_string_not_empty ($sample_error))
      $array_error[] = $sample_error;
  }  // foreach ($qa_array as $qa_key => $qa_to_lane_row)
  // Update the QA data if there are no errors.
  if (count ($array_error) < 1)
  {
    foreach ($array_update as $update_statement)
    {
      $result_update_qa_sample = pg_query ($dbconn, $update_statement);
      if (!$result_update_qa_sample)
      {
        $array_error[] = pg_last_error ($dbconn);
      }  // if (!$result_update_qa_sample)
    }  // foreach ($array_update as $update_statement)
  }  // if (count ($array_error) < 1)
  return $array_error;
}  // function upload_stats_to_qa
// *************************
// This function determines where the QA values
// are within the limits.
// *************************
function check_qa_limits (
 $sample_tag, $yield, $percent_pf, $num_reads,
 $percent_raw_clusters, $percent_ge_q30_bases,
 $mean_quality_score)
{
  // Initialize variables.
  $error_msg = "";
  if (filter_var ($yield, FILTER_VALIDATE_FLOAT) === false ||
        $yield < 0)
    $error_msg .= "Yield must be a number >= 0. ";
  if (filter_var ($percent_pf, FILTER_VALIDATE_FLOAT) === false ||
      $percent_pf < 0 || $percent_pf > 100)
    $error_msg .= "% PF must be a number between 0 and 100. ";
  if (filter_var ($num_reads, FILTER_VALIDATE_FLOAT) === false ||
        $num_reads < 0)
    $error_msg .= "# Reads must be a number >= 0. ";
  if (filter_var ($percent_raw_clusters, FILTER_VALIDATE_FLOAT) === false ||
      $percent_raw_clusters < 0 || $percent_raw_clusters > 100)
    $error_msg .= "% of raw clusters must be a number between 0 and 100. ";
  if (filter_var ($percent_ge_q30_bases, FILTER_VALIDATE_FLOAT) === false ||
      $percent_ge_q30_bases < 0 || $percent_ge_q30_bases > 100)
    $error_msg .= "% of >= Q30 Bases must be a number between 0 and 100. ";
  if (filter_var ($mean_quality_score, FILTER_VALIDATE_FLOAT) === false ||
      $mean_quality_score < 0)
    $error_msg .= "Mean Quality Score must be a number >= 0. ";
  if (trimmed_string_not_empty ($error_msg))
    $error_msg = $sample_tag . " errors: " . $error_msg;
  return $error_msg;
}  // function check_qa_limits (
// *************************
// This function clears all the QA data for the input run.
// *************************
function clear_lane_qa ($dbconn, $run_uid)
{
  $error_msg = "";
  // Clear run lane summary QA data.
  $result_lane = pg_query ($dbconn, "
   UPDATE run_lane
      SET und_yield = NULL,
          und_percent_pf = NULL,
          und_num_reads = NULL,
          und_percent_raw_clusters = NULL,
          und_percent_ge_q30_bases = NULL,
          und_mean_quality_score = NULL
    WHERE run_uid = $run_uid");
  if (!$result_lane)
  {
     $error_msg = pg_last_error ($dbconn);
  } else {
    // Clear QA data for all samples in run lane.
    $result_sample = pg_query ($dbconn, "
     UPDATE run_lane_sample
        SET yield = NULL,
            percent_pf = NULL,
            num_reads = NULL,
            percent_raw_clusters = NULL,
            percent_ge_q30_bases = NULL,
            mean_quality_score = NULL
      WHERE run_lane_uid IN
      (SELECT run_lane_uid
         FROM run_lane
        WHERE run_uid = $run_uid)");
    if (!$result_sample)
       $error_msg = pg_last_error ($dbconn);
  }  // if (!$result_lane)
  return $error_msg;
}  // function clear_lane_qa
// *******************************************************************
// This function takes an array of text lines from an
// uploaded comma-separated variable Post-Run QA file
// and returns an array of the fields required for processing.
// Also, the unique sample_uid in the run
// that matches the sample IDs in the uploaded QA file.
// If a unique sample_uid cannot be found then
// an appropriate warning is returned.
// *******************************************************************
function array_from_qa_file (
 $dbconn, $delimiter, $run_uid, $field_header,
 $array_require_qa_fields, $lane_uid_array,
 $sample_array, $add_samples_to_project)
{
  $qa_array = array();
  $new_samples_for_projects_array = array();
  $array_match = array();
  $project_sample_string_array = array();
  // ***
  // Determine the columns in the upload file
  // that have the required fields.
  // ***
  foreach ($array_require_qa_fields as
   $required_key => &$required_value)
  {
    foreach ($field_header as $file_key => $file_header)
    {
      if ($file_header == $required_value['header'])
      {
        $required_value['position'] = $file_key;
        break;
      }  // if ($file_header == $required_value['header'])
    }  // foreach ($field_header as $file_key => $file_header)
  }  // foreach ($array_require_qa_fields as...
  unset ($required_value);
  // Create an array of the required values from the upload file.
  // Loop through each data line.
  $return_counter = 0;
  for ($i=1; $i < count($sample_array); $i++)
  {
    // Divide string into fields
    $mask = $delimiter . "\r\n";
    $input_string = rtrim ($sample_array[$i], $mask);
    if (trimmed_string_not_empty ($input_string))
    {
      $string_fields = str_getcsv ($input_string, $delimiter);
      // Add string_fields if necessary.
      for ($j=count($string_fields);
           $j <= count($array_require_qa_fields); $j++)
        $string_fields[] = "";
      $qa_array[$return_counter]['sample_uid'] = -99;
      $qa_array[$return_counter]['project_uid'] = -99;
      $qa_array[$return_counter]['add_to_project'] = 0;
      $qa_array[$return_counter]['add_to_run'] = 0;
      $qa_array[$return_counter]['unmatchable'] = 1;
      $qa_array[$return_counter]['warning'] = "";
      // ***
      // For this array row, add a field for each key
      // in the required QA fields.
      // ***
      foreach ($array_require_qa_fields as
       $required_key => $required_value)
      {
        $qa_array[$return_counter][$required_key] = $string_fields[
         $required_value['position']];
      }  // foreach ($array_require_qa_fields as...
      $return_counter++;
    }  // if (trimmed_string_not_empty ($input_string))
  }  // for ($i=1; $i < count($sample_array); $i++)
  foreach ($qa_array as $key => &$upload_row) 
  {
    // ***
    // Sample information.
    // ***
    $sample_name = $upload_row['sample_name'];
    $mod_project_name = trim ($upload_row['project_name']);
    $lane_number = $upload_row['lane_number'];
    // Add lane to run if it does not already exist.
    if ($lane_uid_array[$lane_number] < 0)
    {
      $result_uid = pg_query ($dbconn, "
       SELECT nextval ('run_lane_run_lane_uid_seq')");
      if (!$result_uid)
      {
        $upload_row['warning'] .= pg_last_error ($dbconn);
      } else {
        $run_lane_uid = pg_fetch_result ($result_uid, 0, 0);
        $result_insert = pg_query ($dbconn, "
         INSERT INTO run_lane
          (run_lane_uid, run_uid, lane_number)
         VALUES
          ($run_lane_uid, $run_uid, $lane_number)");
        if (!$result_insert)
        {
          $upload_row['warning'] .= pg_last_error ($dbconn);
        } else {
          $lane_uid_array[$lane_number] = $run_lane_uid;
        }  // if (!$result_insert)
      }  // if (!$result_uid)
    }  // if ($lane_uid_array[$lane_number] < 0)
    $barcode_index = $upload_row['barcode_index'];
    // Skip if this is the undetermined indices row.
    if ($barcode_index != "Undetermined")
    {
      if (trimmed_string_not_empty ($sample_name) &&
          trimmed_string_not_empty ($mod_project_name))
      {
        $result_match = pg_query ($dbconn, "
         SELECT sample.sample_uid,
                project.project_uid
           FROM run_lane,
                run_lane_sample,
                sample,
                project
          WHERE run_lane.run_uid = $run_uid AND
                run_lane.lane_number = $lane_number AND
                run_lane.run_lane_uid =
                 run_lane_sample.run_lane_uid AND
                sample_name = '$sample_name' AND
                regexp_replace (
                 regexp_replace(project_name,'[\. \-\/]','_','g'),
                 '[^A-Za-z0-9_]', '', 'g') = '$mod_project_name' AND
                run_lane_sample.sample_uid = sample.sample_uid AND
                sample.project_uid = project.project_uid");
        if (!$result_match)
        {
          $upload_row['warning'] .= "For sample " .
           $sample_name . ": " . pg_last_error ( $dbconn);
        } else {
          if (pg_num_rows ($result_match) > 1)
          {
            $upload_row['warning'] .= "More " .
             "than one sample in this run lane is name " .
             $sample_name . ".";
          } elseif (pg_num_rows ($result_match) < 1) {
            if ($add_samples_to_project)
            {
              // Find the project for this sample.
              $result_project = pg_query ($dbconn, "
               SELECT project_uid
                 FROM project
                WHERE regexp_replace (
                       regexp_replace(project_name,'[\. \-\/]','_','g'),
                       '[^A-Za-z0-9_]', '', 'g') = '$mod_project_name'");
              if (!$result_project)
              {
                $upload_row['warning'] .= pg_last_error ($dbconn);
              } elseif (pg_num_rows ($result_project) < 1) {
                $upload_row['warning'] .= "Could not find project ".
                 $mod_project_name;
              } else {
                $upload_row['unmatchable'] = 0;
                $upload_row['add_to_project'] = 1;
                $upload_row['add_to_run'] = 1;
                $upload_row['project_uid'] = pg_fetch_result (
                 $result_project, 0, 0);
              }  // if (!$result_project)
            } else {
              $upload_row['warning'] .= "Project " . $mod_project_name .
               ", sample " . $sample_name .
               " not in database for this run and lane " .
               $lane_number . ".";
            }  // if ($add_samples_to_project)
          } else {
            $row = pg_fetch_assoc ($result_match);
            $upload_row['sample_uid'] = $row['sample_uid'];
            $upload_row['project_uid'] = $row['project_uid'];
            $upload_row['unmatchable'] = 0;
          }  // if (pg_num_rows ($result_match) > 1)
        }  // if (!$result_match)
      }  // if (trimmed_string_not_empty ($sample_name) &&...
    } else {
      $upload_row['unmatchable'] = 0;
    }  // if ($barcode_index != "Undetermined")
  }  // foreach ($qa_array as $key => $upload_row) 
  unset ($upload_row);
  if ($add_samples_to_project)
  {
    // ***
    // Create an index of projects and samples that need
    // to be added to the database.
    // ***
    foreach ($qa_array as $key => $row)
    {
      if ($row['add_to_project'] == 1)
      {
        $project_sample_string_array[$key] = 'Project:' .
         $row['project_uid'] . ', Sample:' . $row['sample_name'];
      } else {
        $project_sample_string_array[$key] = $key;
      }  // if ($row['add_to_project'] == 1)
    }  // foreach ($qa_array as $row)
    // ***
    // For samples that are in more than one lane
    // only one instance should should be added to the database.
    // ***
    asort ($project_sample_string_array);
    $previous_key = 'Null';
    foreach ($project_sample_string_array as $key => $value)
    {
      if ($value == $previous_key)
      {
        $qa_array[$key]['add_to_project'] = 0;
      } else {
        $previous_key = $value;
      }  // if ($value == $previous_key)
    }  // foreach ($project_sample_string_array as $key => $value)
  }  // if ($add_samples_to_project)
  return $qa_array;
}  // function array_from_qa_file
// ************************************************************
// This function accepts an array of the post-run QA data
// and returns an error for any duplicated barcode index
// or for an empty barcode index.
// ************************************************************
function dup_indices_in_qa_file ($qa_array)
{
  $array_error = array();
  // ***
  // Check that barcode index is not empty and
  // not already identified as a duplicate in the run lane.
  // ***
  foreach ($qa_array as $row_number => $qa_row)
    if (trimmed_string_not_empty ($qa_row['barcode_index']))
    {
      $array_barcode_index[] = "Lane " .
                               $qa_row['lane_number'] .
                               " barcode index " .
                               $qa_row['barcode_index'];
    } else {
      $display_row = $row_number + 1;
      $array_error[] = "Data row " . $display_row . " has no index value.";
    }  // if (trimmed_string_not_empty ($qa_row['barcode_index']))
  // Check for duplicate barcode indexes in upload file.
  foreach (array_count_values ($array_barcode_index) as
   $barcode_index => $barcode_index_count)
    if ($barcode_index_count > 1)
      $array_error[] = $barcode_index . " has " .
       $barcode_index_count . " lines in upload file.";
  return $array_error;
}  // function dup_indices_in_qa_file
// ************************************************************
// This function accepts an array of the post-run QA data
// and returns an error if there are rows to be added
// to a given project with duplicate sample names.
// ************************************************************
function dup_add_samples_in_qa_file ($qa_array)
{
  $array_error = array();
  $array_project_sample = array();
  // ***
  // Make an array of the project uid and sample name
  // for all the samples that are to be added
  // to a project.
  // ***
  foreach ($qa_array as $row_number => $qa_row)
    if ($qa_row['add_to_project'])
      $array_project_sample[] = "Lane " .
       $qa_row['lane_number'] .
       ", project " .
       $qa_row['project_name'] .
       ", sample " .
       $qa_row['sample_name'];
  // Check for duplicate project sample combinations in upload file.
  foreach (array_count_values ($array_project_sample) as
   $project_sample_string => $project_sample_string_count)
    if ($project_sample_string_count > 1)
      $array_error[] = $project_sample_string . " is repeated " .
       $project_sample_string_count . " times in upload file.";
  return $array_error;
}  // function dup_add_samples_in_qa_file
// **************************************************************
// This function creates a drop-down list based on
// the projects in the input run.
// **************************************************************
function drop_down_run_pis ($dbconn, $run_uid, $select_name,
 $select_value, $drop_down_class,
 $primary_investigator_view, $option_value_col,
 $option_display_col, $title="", $where_clause=" ",
 $default_display="Show All", $default_value="Show All",
 $default_in_select=0, $on_change_submit=1, $disabled_string="")
{
  require 'user_view.php';
  $drop_down_html = '';
  if (strlen (trim ($select_name)) > 0)
  {
    // Select all the option values and display strings.
    $query_string = "SELECT DISTINCT
            $primary_investigator_view.primary_investigator_uid,
            name AS primary_investigator
       FROM $primary_investigator_view,
            $project_view,
            $sample_view,
            $run_lane_view,
            $run_lane_sample_view
      WHERE $run_lane_view.run_uid = $run_uid AND
            $run_lane_view.run_lane_uid =
             $run_lane_sample_view.run_lane_uid AND
            $run_lane_sample_view.sample_uid =
             $sample_view.sample_uid AND
            $sample_view.project_uid = $project_view.project_uid AND
            $project_view.primary_investigator_uid =
             $primary_investigator_view.primary_investigator_uid
      ORDER BY name"; 
    $result_option = pg_query ($dbconn, $query_string);
    if (!$result_option)
    {
      $drop_down_html = '<span>'.
                        pg_last_error ($dbconn).'</span>';
    } else {
      if ($on_change_submit)
      {
        $onchange_var = 'onchange="this.form.submit();" ';
      } else {
        $onchange_var = ' ';
      }  // if ($on_change_submit)
      $drop_down_html = '<select name="' .
                        $select_name .
                        '" ' .
                        $onchange_var .
                        'class="' .
                        $drop_down_class .
                        '" title="' . $title. '" ' .
                        $disabled_string . '>';
      // If an option other than the default value has already been entered,
      // display it. Otherwise use the default display.
      $option_selected = 'selected="selected"';
      if (isset($select_value) && trimmed_string_not_empty ($select_value))
      {
        if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
        {
          $option_selected = '';
        }  // if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
      }  // if (isset($select_value) &&...
      $drop_down_html = $drop_down_html.
                        '<option value="'.
                        $default_value.'" '.
                        $option_selected.'>'.
                        $default_display.'</option>';
      for ($i=0; $i < pg_num_rows($result_option); $i++)
      {
        $row = pg_fetch_assoc ($result_option);
        $option_value = $row[$option_value_col];
        // If default is part of array, skip the default value.
        if (($default_in_select == 0)  ||
           (strcasecmp (trim ($option_value), trim ($default_value)) != 0))
        {
          $option_display = $row[$option_display_col];
          // If this option has been entered, display it.
          $option_selected = '';
          if (isset($select_value) && trimmed_string_not_empty ($select_value))
          {
            if ($option_value == $select_value)
            {
              $option_selected = 'selected="selected"';
            }  // if ($option_value == $select_value)
          }  // if (isset($select_value) &&
          $drop_down_html = $drop_down_html.'<option value="'.
                            $option_value.'" '.
                            $option_selected.'>'.
                            $option_display.'</option>';
        }  // if (($default_in_select == 0)  ||..
      }  // for ($i=0; $i < pg_num_rows($result_option); $i++)
      $drop_down_html = $drop_down_html.'</select>';
    }  // if (!result_option)
  }  // if (strlen (trim ($select_name)) > 0)
  return $drop_down_html;
}  // function drop_down_run_pis
// **************************************************************
// This function creates a drop-down list based on
// the projects in the input run.
// **************************************************************
function drop_down_run_projects ($dbconn, $run_uid, $select_name,
 $select_value, $drop_down_class,
 $project_view, $option_value_col,
 $option_display_col, $title="", $where_clause=" ",
 $default_display="Show All", $default_value="Show All",
 $default_in_select=0, $on_change_submit=1, $disabled_string="")
{
  require 'user_view.php';
  $drop_down_html = '';
  if (strlen (trim ($select_name)) > 0)
  {
    // Select all the option values and display strings.
    $query_string = "
     SELECT DISTINCT
            $project_view.project_uid,
            project_name
       FROM $project_view,
            $sample_view,
            $run_lane_view,
            $run_lane_sample_view
      WHERE $run_lane_view.run_uid = $run_uid AND
            $run_lane_view.run_lane_uid =
             $run_lane_sample_view.run_lane_uid AND
            $run_lane_sample_view.sample_uid =
             $sample_view.sample_uid AND
            $sample_view.project_uid = $project_view.project_uid
      ORDER BY project_name"; 
    $result_option = pg_query ($dbconn, $query_string);
    if (!$result_option)
    {
      $drop_down_html = '<span>'.
                        pg_last_error ($dbconn).'</span>';
    } else {
      if ($on_change_submit)
      {
        $onchange_var = 'onchange="this.form.submit();" ';
      } else {
        $onchange_var = ' ';
      }  // if ($on_change_submit)
      $drop_down_html = '<select name="' .
                        $select_name .
                        '" ' .
                        $onchange_var .
                        'class="' .
                        $drop_down_class .
                        '" title="' . $title. '" ' .
                        $disabled_string . '>';
      // ***
      // If an option other than the default value has already been entered,
      // display it. Otherwise use the default display.
      // ***
      $option_selected = 'selected="selected"';
      if (isset($select_value) && trimmed_string_not_empty ($select_value))
      {
        if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
        {
          $option_selected = '';
        }  // if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
      }  // if (isset($select_value) &&...
      $drop_down_html = $drop_down_html.
                        '<option value="'.
                        $default_value.'" '.
                        $option_selected.'>'.
                        $default_display.'</option>';
      for ($i=0; $i < pg_num_rows($result_option); $i++)
      {
        $row = pg_fetch_assoc ($result_option);
        $option_value = $row[$option_value_col];
        // If default is part of array, skip the default value.
        if (($default_in_select == 0)  ||
           (strcasecmp (trim ($option_value), trim ($default_value)) != 0))
        {
          $option_display = $row[$option_display_col];
          // If this option has been entered, display it.
          $option_selected = '';
          if (isset($select_value) && trimmed_string_not_empty ($select_value))
          {
            if ($option_value == $select_value)
            {
              $option_selected = 'selected="selected"';
            }  // if ($option_value == $select_value)
          }  // if (isset($select_value) &&
          $drop_down_html = $drop_down_html.'<option value="'.
                            $option_value.'" '.
                            $option_selected.'>'.
                            $option_display.'</option>';
        }  // if (($default_in_select == 0)  ||..
      }  // for ($i=0; $i < pg_num_rows($result_option); $i++)
      $drop_down_html = $drop_down_html.'</select>';
    }  // if (!result_option)
  }  // if (strlen (trim ($select_name)) > 0)
  return $drop_down_html;
}  // function drop_down_run_projects
// *******************************************************************
// This function adds the post run QA sample
// to the project and run lane.
// *******************************************************************
function add_qa_sample_to_database (
 $dbconn, $run_lane_uid, $project_uid,
 $sample_name, $sample_description, $barcode_index,
 $species, $add_to_project)
{
  $sample_uid = -999;
  // Check whether the sample already exists in the project.
  $result_sample = pg_query ($dbconn, "
   SELECT sample_uid
     FROM sample
    WHERE project_uid = $project_uid AND
          sample_name = '$sample_name'");
  if (!$result_sample)
  {
    return -999;
  } elseif (pg_num_rows ($result_sample) > 0) {
    $sample_uid = pg_fetch_result ($result_sample, 0, 0);
  } else {
    // Get the sample_uid that will be used for the sample record.
    $result_uid = pg_query ($dbconn, "
     SELECT nextval ('sample_sample_uid_seq')");
    if (!$result_uid)
    {
      return -999;
    } else {
      if (! trimmed_string_not_empty ($species))
        $species = "NA";
      // Create sample.
      $sample_uid = pg_fetch_result ($result_uid, 0, 0);
      $insert_sample = pg_query ($dbconn, "
       INSERT INTO sample
        (sample_uid, project_uid, sample_name,
         barcode, species,
         sample_type, barcode_index,
         sample_description,
         comments)
       VALUES
        ($sample_uid, $project_uid, '$sample_name',
         'custom', '$species',
         'Not entered for this sample.', '$barcode_index',
         '$sample_description',
         'This sample was generated by uploading the post-run QA data.')");
      if (!$insert_sample)
        return -999;
    }  // if (!$result_uid)
  }  // if (!$result_sample)
  // Add sample to run lane.
  $insert_run_sample = pg_query ($dbconn, "
   INSERT INTO run_lane_sample
    (run_lane_uid, sample_uid, comments)
   VALUES
    ($run_lane_uid, $sample_uid,
   'This is a custom sample that was generated by " .
   "uploading the post-run QA data.')");
  if (!$insert_run_sample)
    return -999;
  return $sample_uid;
}  // function add_qa_sample_to_database
// **************************************************************
// This function returns an array of run_lane_uid for each
// lane in the input run.
// **************************************************************
function all_lane_uids_for_run ($dbconn, $run_uid, $num_run_lanes)
{
  $lane_uid_array = array();
  for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
  {
    $lane_uid_array[$lane_number] = -999;
    $result_lane = pg_query ($dbconn, "
     SELECT run_lane.run_lane_uid
       FROM run_lane
      WHERE run_uid = $run_uid AND
            lane_number = $lane_number");
    if (!$result_lane)
    {
      $lane_uid_array[$lane_number] = -999;
    } elseif (pg_num_rows ($result_lane) > 0) {
      $lane_uid_array[$lane_number] = pg_fetch_result ($result_lane, 0, 0);
    }  // if (!$result_lane)
  }  // for ($lane_number=1; $lane_number <= $num_run_lanes;...
  return $lane_uid_array;
}  // function all_lane_uids_for_run
?>
