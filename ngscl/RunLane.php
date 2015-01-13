<?php
// **************************************************************
// This class defines the project_log_uid based on
// the input table and project_uid.
// If no associated project_log exists then it is created.
// **************************************************************
class ProjectLog
{
  private static $log_sample_suffix = '_run_lane';
  function __construct (
   $dbconn, $project_table, $project_log_table, $project_uid)
  {
    $this->project_log_table = $project_log_table;
    $this->db_error = "";
    $col_array = array();
    // Get the project name.
    $col_array['label'] = 'Project';
    $col_array['type'] = 'varchar';
    $col_array['value'] = "";
    $query_failed = 1;
    $result_puid = pg_query ($dbconn, "
     SELECT project_name
       FROM $project_table
      WHERE project_uid = $project_uid");
    if (!$result_puid)
    {
      $this->db_error = pg_last_error ($dbconn);
    } elseif (pg_num_rows ($result_puid) > 0) {
      $col_array['value'] = pg_fetch_result ($result_puid, 0, 0);
      $this->project_name = $col_array;
      // Get all the project data in the project_log table or view.
      $query_failed = 0;
      $result_query = pg_query ($dbconn, "
       SELECT *
         FROM $project_log_table
        WHERE project_uid = $project_uid");
      if (!$result_query)
      {
        $this->db_error = pg_last_error ($dbconn);
        $query_failed = 1;
      } else {
        if (pg_num_rows ($result_query) < 1)
        {
          // Get new project_log_uid from sequence.
          $result_seq = pg_query ($dbconn, "
           SELECT nextval ('project_log_project_log_uid_seq')");
          if (!$result_seq)
          {
            $this->db_error = pg_last_error ($dbconn);
            $query_failed = 1;
          } else {
            // Create a new, empty project log.
            $project_log_uid = pg_fetch_result ($result_seq, 0, 0);
            $result_insert = pg_query ($dbconn, "
             INSERT INTO " . $this->project_log_table . "
              (project_log_uid, project_uid, core_director_approval,
               primary_investigator_approval)
             VALUES
              ($project_log_uid, $project_uid, FALSE, FALSE)");
            if (!$result_insert)
            {
              $this->db_error = pg_last_error ($dbconn);
              $query_failed = 1;
            } else {
              // Get the log information from the project log table or view.
              $result_query = pg_query ($dbconn, "
               SELECT *
                 FROM " . $this->project_log_table . "
                WHERE project_uid = $project_uid");
              if (!$result_query)
              {
                $this->db_error = pg_last_error ($dbconn);
                $query_failed = 1;
              }  // if (!$result_query)
            }  // if (!$result_insert)
          }  // if (!$result_seq)
        }  // if (pg_num_rows ($result_query) < 1) {
      }  // if (!$result_query)
    }  // if (!$result_puid)
    if ($query_failed < 1)
    {
      // Populate all the log information variables.
      for ($i=0; $i < pg_num_fields ($result_query); $i++)
      {
        $col_name = pg_field_name ($result_query, $i);
        $col_array['label'] = variable_to_label ($col_name);
        $col_array['type'] = pg_field_type ($result_query, $i);
        $col_array['value'] = pg_fetch_result ($result_query, 0, $i);
        $this->$col_name = $col_array;
      }  // for ($i=0; $i < pg_num_fields ($result_query); $i++)
    } else {
      // Indicate that the project log could not be created.
      $col_array['label'] = 'Project Log Uid';
      $col_array['type'] = 'int4';
      $col_array['value'] = 0;
      $this->project_log_uid = $col_array;
    }  // if ($query_failed > 0)
  }  // function __construct
  // ****
  // This function populates the session variables
  // with all the project log variables of the class.
  // ****
  function populate_session ($dbconn)
  {
    // Get all the column names from the project log table or view.
    $col_array = array();
    $result_query = pg_query ($dbconn, "
     SELECT *
       FROM " . $this->project_log_table . "
      WHERE 1 = 2");
    if (!$result_query)
    {
      $this->db_error = pg_last_error ($dbconn);
      $_SESSION['project_log_uid'] = 0;
    } else {
      // Populate the session variables with the object values.
      for ($i=0; $i < pg_num_fields ($result_query); $i++)
      {
        $col_name = pg_field_name ($result_query, $i);
        if ($col_name != 'project_uid')
        {
          $col_array = $this->$col_name;
          $col_value = $col_array['value'];
          $_SESSION[$col_name] = $col_value;
        }  // if ($col_name != 'project_uid')
      }  // for ($i=0; $i < pg_num_fields ($result_query); $i++)
    }  // if (!$result_query)
  }  // function populate_session
  // This function makes a report of the project log.
  function report ($project_log_run_lane)
  {
    $report_string = "";
    $my_dump = get_object_vars ($this);
    foreach ($my_dump as $col_name => $col_array)
    {
      if ($col_name != 'project_uid' && $col_name != 'project_log_uid' &&
          $col_name != 'project_log_table' && $col_name != 'db_error')
      {
        if ($col_array['type'] == 'text')
        {
          $report_value = replace_eol ($col_array['value']);
        } elseif ($col_array['type'] == 'bool') {
          $report_value = standardize_boolean ($col_array['value']);
        } elseif ($col_name == 'estimated_price' &&
                  trimmed_string_not_empty ($col_array['value'])) {
          $report_value = number_format ($col_array['value'], 2, '.', '');
        } else {
          $report_value = $col_array['value'];
        }  // if ($col_array['type'] == 'text') &&...
        $report_string .= '"' . $col_array['label'] .
                          ': ' .
                          $report_value .
                          "\"\r\n";
        if ($col_name == 'read_length')
        {
          $report_string .= $project_log_run_lane->report() . "\r\n";
        }  // if ($col_name == 'read_length')
      }  // if ($col_name != 'project_uid' && $col_name != 'project_log_uid'...
    }  // foreach ($my_dump as $col_name => $col_array)
    return ($report_string);
  }  // function report ()
  function display ($project_log_run_lane)
  {
    $display_string = "";
    $my_dump = get_object_vars ($this);
    foreach ($my_dump as $col_name => $col_array)
    {
      if ($col_name != 'project_uid' && $col_name != 'project_log_uid' &&
          $col_name != 'project_log_table' && $col_name != 'db_error')
      {
        if ($col_array['type'] == 'bool')
        {
          $report_value = standardize_boolean ($col_array['value']);
        } elseif ($col_name == 'estimated_price' &&
                  trimmed_string_not_empty ($col_array['value'])) {
          $report_value = td_ready ('$'.number_format($col_array['value'],2));
        } else {
          $report_value = td_ready ($col_array['value']);
        }  // if ($col_array['type'] == 'bool')
        if ($col_name == 'hypothesis')
        {
          $display_string .= '<hr /><b>EXPERIMENTAL CONDITIONS</b><br /><br />';
        } elseif ($col_name == 'sample_purification_method') {
          $display_string .= '<hr /><b>LIBRARY PREP</b><br /><br />';
        }  // if ($col_name == 'hypothesis')
        $display_string .= bolded_label ($col_array['label']) .
                          $report_value .
                          "<br /><br />";
        if ($col_name == 'read_length')
        {
          $display_string .= $project_log_run_lane->display() . "<br />";
        } elseif ($col_name == 'conditions_comments' ||
                  $col_name == 'library_prep_comments') {
          $display_string .= '<hr />';
        }  // if ($col_name == 'read_length')
      }  // if ($col_name != 'project_uid' &&...
    }  // foreach ($my_dump as $col_name => $col_array)
    return ($display_string);
  }  // function display
  function delete_from_database ($dbconn, $project_log_run_lane)
  {
    $array_error = array();
    // Get new deleted_project_log_uid from sequence.
    $result_seq = pg_query ($dbconn, "
    SELECT nextval ('deleted_project_log_deleted_project_log_uid_seq')");
    if (!$result_seq)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $deleted_project_log_uid = pg_fetch_result ($result_seq, 0, 0);
      $project_uid = $this->project_uid['value'];
      $name_of_experimenter = ddl_ready ($this->name_of_experimenter['value']);
      $experiment_title = ddl_ready ($this->experiment_title['value']);
      $core_director_approval = standardize_boolean (
       $this->core_director_approval['value']);
      $core_director_approval_date = (
       trimmed_string_not_empty ($this->core_director_approval_date['value']) ?
       "'" . $this->core_director_approval_date['value'] . "'" : 'NULL');
      $primary_investigator_approval = standardize_boolean (
       $this->primary_investigator_approval['value']);
      $primary_investigator_approval_date = (
       trimmed_string_not_empty (
        $this->primary_investigator_approval_date['value']) ?
       "'" . $this->primary_investigator_approval_date . "'" : 'NULL');
      $run_number = (trimmed_string_not_empty ($this->run_number['value']) ?
       $this->run_number['value'] : 'NULL');
      $lanes = ddl_ready ($this->lanes['value']);
      $run_date = (trimmed_string_not_empty ($this->run_date['value']) ?
       "'" . $this->run_date['value'] . "'" : 'NULL');
      $experiment_type = ddl_ready ($this->experiment_type['value']);
      $results_summary = ddl_ready ($this->results_summary['value']);
      $account_number = ddl_ready ($this->account_number['value']);
      $estimated_price = (trimmed_string_not_empty (
       $this->estimated_price['value']) ?
       $this->estimated_price['value'] : 'NULL');
      $estimated_price_comments = ddl_ready (
       $this->estimated_price_comments['value']);
      $hypothesis = ddl_ready ($this->hypothesis['value']);
      $cells_used = ddl_ready ($this->cells_used['value']);
      $experimental_methods = ddl_ready ($this->experimental_methods['value']);
      $time_course = ddl_ready ($this->time_course['value']);
      $experimental_category = ddl_ready (
       $this->experimental_category['value']);
      $conditions_comments = ddl_ready ($this->conditions_comments['value']);
      $batching_instructions = ddl_ready (
       $this->batching_instructions['value']);
      $sample_purification_method = ddl_ready (
       $this->sample_purification_method['value']);
      $kits_used = ddl_ready ($this->kits_used['value']);
      $amplification_method = ddl_ready ($this->amplification_method['value']);
      $barcoding_used = ddl_ready ($this->barcoding_used['value']);
      $new_method_or_comparison_study = ddl_ready (
       $this->new_method_or_comparison_study['value']);
      $library_prep_comments = ddl_ready (
       $this->library_prep_comments['value']);
      $read_length = ddl_ready ($this->read_length['value']);
      $adapter_sequences = ddl_ready ($this->adapter_sequences['value']);
      $barcode_sequences = ddl_ready ($this->barcode_sequences['value']);
      // Insert into deleted_project_log.
      $result_insert = pg_query ($dbconn, "
       INSERT INTO deleted_project_log
        (deleted_project_log_uid, project_uid,
         name_of_experimenter,
         experiment_title, core_director_approval,
         core_director_approval_date, primary_investigator_approval,
         primary_investigator_approval_date, run_number,
         lanes, run_date,
         experiment_type, results_summary, account_number,
         estimated_price, estimated_price_comments,
         hypothesis, cells_used,
         experimental_methods, time_course,
         experimental_category, conditions_comments,
         batching_instructions, sample_purification_method,
         kits_used, amplification_method,
         barcoding_used, new_method_or_comparison_study,
         library_prep_comments, read_length,
         adapter_sequences, barcode_sequences)
       VALUES
        ($deleted_project_log_uid, $project_uid,
         '$name_of_experimenter',
         '$experiment_title', $core_director_approval,
         $core_director_approval_date, $primary_investigator_approval,
         $primary_investigator_approval_date, $run_number,
         '$lanes', $run_date,
         '$experiment_type', '$results_summary', '$account_number',
         $estimated_price, '$estimated_price_comments',
         '$hypothesis', '$cells_used',
         '$experimental_methods', '$time_course',
         '$experimental_category', '$conditions_comments',
         '$batching_instructions', '$sample_purification_method',
         '$kits_used', '$amplification_method',
         '$barcoding_used', '$new_method_or_comparison_study',
         '$library_prep_comments', '$read_length',
         '$adapter_sequences', '$barcode_sequences')");
      if (!$result_insert)
      {
        $array_error[] = pg_last_error ($dbconn);
      } else {
        // ****
        // Insert into deleted_project_log_run_lane and
        // delete from project_log_run_lane.
        // ****
        $project_log_run_lane_table = $this->project_log_table .
                                      self::$log_sample_suffix;
        $run_lane_error = $project_log_run_lane->delete_from_database (
         $dbconn, $deleted_project_log_uid, $project_log_run_lane_table);
        if (count ($run_lane_error) < 1)
        {
           // Delete from project_log.
          $result_delete = pg_query ($dbconn, "
           DELETE FROM ".$this->project_log_table."
            WHERE project_log_uid = ".$this->project_log_uid['value']);
          if (!$result_delete)
          {
            $array_error[] = pg_last_error ($dbconn);
          }  // if (!$result_delete)
        } else {
          $array_error = array_merge ($array_error, $run_lane_error);
        }  // if (count ($run_lane_error) < 1)
      }  // if (!$result_insert)
    }  // if (!$result_seq)
    return $array_error;
  }  // function delete_from_database
}  // class ProjectLog
// **************************************************************
// This class defines the project log run lanes based on the
// the project_log_uid.
// **************************************************************
class ProjectLogRunLane
 extends ArrayObject
{
  function __construct (
  $dbconn, $project_log_run_lane_table, $project_log_uid)
  {
    // Select values from project_log_run_lane table.
    $log_sample_array = array();
    $result = pg_query ($dbconn, "
     SELECT project_log_run_lane_uid,
            batch_group,
            sample_name,
            sample_description,
            species
       FROM $project_log_run_lane_table
      WHERE project_log_uid = $project_log_uid
      ORDER BY batch_group,
               project_log_run_lane_uid");
    if (!$result)
    {
    } else {
      for ($i=0; $i<pg_num_rows($result); $i++)
      {
        $log_sample_array[] = pg_fetch_assoc ($result);
      }  // for ($i=0; $i<pg_num_rows($result); $i++)
    }  // if (!$result)
    parent::__construct ($log_sample_array);
  }  // function __construct
  function populate_session ()
  {
    $my_array = $this->getArrayCopy();
    foreach ($my_array as $key => $row)
    {
      foreach ($row as $name => $value)
      {
        $_SESSION[$name][$key] = $value;
      }  // foreach ($row as $name => $value)
    }  // foreach ($my_array as $name => $value)
  }  // function populate_session
  function report ()
  {
    $report_string = "Batch Group,Sample Name,Sample Description,Species\r\n";
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $report_string .= $iterator->current()['batch_group'] . ',' .
       $iterator->current()['sample_name'] . ',"' .
       $iterator->current()['sample_description'] . '","' .
       $iterator->current()['species'] . "\"\r\n";
    }  // for ($iterator = $this->getIterator();
    return ($report_string);
  }  // function report ()
  function report_std_barcode ()
  {
    $report_string = "sample_name\tsample_description\t" .
     "status\tprep_type\tbarcode_number\t" .
     "species\tsample_type\tbatch_group\tcomments\r\n";
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $report_string .= $iterator->current()['sample_name'] .
       "\t\"" .
       $iterator->current()['sample_description'] .
       "\"\t\t\t\t\"" .
       $iterator->current()['species'] .
       "\"\t\t\"" .
       $iterator->current()['batch_group'] .
       "\"\t\r\n";
    }  // for ($iterator = $this->getIterator();
    return ($report_string);
  }  // function report_std_barcode ()
  function report_custom_barcode ()
  {
    $report_string = "sample_name\tsample_description\t" .
     "status\tbarcode\tbarcode_index\t" .
     "species\tsample_type\tbatch_group\tcomments\r\n";
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $report_string .= $iterator->current()['sample_name'] .
       "\t\"" .
       $iterator->current()['sample_description'] .
       "\"\t\t\t\t\"" .
       $iterator->current()['species'] .
       "\"\t\t\"" .
       $iterator->current()['batch_group'] .
       "\t\r\n";
    }  // for ($iterator = $this->getIterator();
    return ($report_string);
  }  // function report_custom_barcode ()
  function display ()
  {
    $display_string = '<table border="1"><thead><tr>' .
     '<th>Batch Group</th><th>Sample Name</th><th>Sample Description</th>' .
     '<th>Species</th></tr></thead><tbody>';
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $display_string .= '<tr><td>' .
       td_ready ($iterator->current()['batch_group']) . '</td><td>' .
       td_ready ($iterator->current()['sample_name']) . '</td><td>' .
       td_ready ($iterator->current()['sample_description']) . '</td><td>' .
       td_ready ($iterator->current()['species']) .
       '</td></tr>';
    }  // for ($iterator = $this->getIterator();
    $display_string .= '</tbody></table>';
    return ($display_string);
  }  // function display ()
  function delete_from_database (
   $dbconn, $deleted_project_log_uid, $project_log_run_lane_table)
  {
    $array_error = array();
    // Insert into deleted_project_log_run_lane.
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $project_log_run_lane_uid = $iterator->current()[
       'project_log_run_lane_uid'];
      $batch_group = $iterator->current()['batch_group'];
      $sample_name = $iterator->current()['sample_name'];
      $sample_description = $iterator->current()['sample_description'];
      $species = $iterator->current()['species'];
      $result_insert = pg_query ($dbconn, "
       INSERT INTO deleted_project_log_run_lane
        (deleted_project_log_uid, batch_group, sample_name,
         sample_description, species)
       VALUES
        ($deleted_project_log_uid, '$batch_group', '$sample_name',
         '$sample_description', '$species')");
      if (!$result_insert)
      {  
        $array_error[] = pg_last_error ($dbconn);
      } else {
        $result_delete = pg_query ($dbconn, "
         DELETE FROM $project_log_run_lane_table
          WHERE project_log_run_lane_uid = $project_log_run_lane_uid");
        if (!$result_delete)
        {  
          $array_error[] = pg_last_error ($dbconn);
        }  // if (!$result_delete)
      }  // if (!$result)
    }  // for ($iterator = $this->getIterator();...
    return $array_error;
  }  // function delete_from_database
}  // class ProjectLogRunLane
// ***********************************************************
// This function checks an array of project log
// sample data for errors.
// An array of error messages are returned if errors are found.
// ***********************************************************
function check_log_samples ($dbconn, $project_log_run_lane,
 $project_log_uid, $input_name_array,
 $input_line_array, $required_fields_array,
 $max_batch_group_length, $max_sample_name_length,
 $check_project_samples=FALSE)
{
  $array_error = array();
  // Check that there are no duplicate sample names in the input array.
  if (count($input_name_array) > count(array_unique($input_name_array)))
  {
    $array_error[0] = 'You tried to add the same sample name more than once.';
  } else {
    // Loop through all the rows and all the keys of the input line array.
    foreach ($input_line_array as $rowkey => $rowvalue)
    {
      $display_row = $rowkey + 1;
      $row_error = "";
      // Check if there is a sample name in this row.
      if (strlen(trim($rowvalue['sample_name'])) > 0)
      { 
        $sample_name = trim ($rowvalue['sample_name']);
        // ****
        // Check that the sample name is not too long and contains
        // only alphanumeric characters and the underscore.
        // ****
        if (strlen($sample_name) > $max_sample_name_length)
        {
          $row_error = "Data row ".$display_row.
                               " Sample name " . $sample_name .
                               " invalid. Must be no more than " .
                               $max_sample_name_length .
                               " characters";
          if (!alphanum_hyphen_underscore_only ($sample_name))
          {
            $row_error .= ", and only alphanumeric characters, " .
                                 "hyphen, and underscore allowed.";
          }  // if (!alphanum_hyphen_underscore_only ($sample_name))
        } elseif (!alphanum_hyphen_underscore_only ($sample_name)) {
          $row_error = "Data row ".$display_row.
                       " Sample name " . $sample_name .
                       " invalid. Only alphanumeric characters, " .
                       "hyphen, and underscore allowed.";
        }  // if (strlen($sample_name) > $max_sample_name_length)
        // Check that the sample name is not already in the project log.
        if (strlen($row_error) < 1 && $check_project_samples == TRUE)
        {
          $result_sample = pg_query ($dbconn, "
           SELECT COUNT(1) AS row_count
             FROM $project_log_run_lane
            WHERE project_log_uid = $project_log_uid AND
                  sample_name = '$sample_name'");
          if (!$result_sample)
          {
            $array_error[0] = "Data row ".$display_row.": ".
                              pg_last_error($dbconn);
            return $array_error;
          } elseif ($line = pg_fetch_assoc($result_sample)) {
            if ($line['row_count'] > 0)
            {
              $row_error = "Data row ".$display_row.
                           ": Sample \"".$sample_name.
                           "\" already exists for this project log.";
            } // if ($line['row_count'] > 0)
          }  // if (!$result_sample)
        }  // if (strlen($sample_name_error) < 1 &&...
        if (strlen($row_error) < 1)
        {
          // If no errors for row found, check that all required fields exist.
          $missing_fields = missing_field_list (
           $rowvalue, $required_fields_array);
          if (strlen(trim($missing_fields)) > 0)
          {
            $row_error = "Data row ".$display_row.
                                    ": Required field missing".
                                    $missing_fields . ".";
          }  // if (strlen(trim($missing_fields)) > 0)
        }  // if (strlen($sample_name_error) < 1)
      } else {
        // The sample name is missing for this row.
        // Check that all the required fields exist.
        $missing_fields = missing_field_list ($rowvalue,
                                              $required_fields_array);
        $row_error = "Data row ".$display_row.
                                ": Required field missing".
                                $missing_fields . ".";
      }  // if (strlen(trim($rowvalue['sample_name'])) > 0)
      // Check that the batch group name is not too long.
      $batch_group = trim ($rowvalue['batch_group']);
      if (strlen($batch_group) > $max_batch_group_length)
      {
        if (strlen(trim($row_error)) >0)
        {
          $row_error .= " Batch group " . $batch_group .
                        " invalid. Must be no more than " .
                        $max_batch_group_length .
                        " characters";
        } else {
          $row_error = "Data row ".$display_row.
                       ": Batch group " . $batch_group .
                       " invalid. Must be no more than " .
                       $max_batch_group_length .
                       " characters";
        }  //if (strlen(trim($row_error)) >0)
      }  // if (strlen($batch_group) > $max_batch_group_length)
      if (strlen(trim($row_error)) >0)
      {
        $array_error[$rowkey] = $row_error;
      }  // if (strlen(trim($row_error)) >0)
    }  // foreach ($input_line_array as $rowkey as $rowvalue)
  }  // if (count($input_name_array) > count(array_unique($input_name_array)))
  return $array_error;
}  // function check_log_samples
// *************************************************************
// This function returns an array of log sample data, based
// on SESSION variables.
// *************************************************************
function array_of_log_sample_data ()
{
  // Put posted table values into sample line array.
  $line_number = 0;
  $sample_line_array = array();
  if (isset ($_SESSION['sample_name']))
  {
    foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
    {
      // If at least one field is not blank add the row to the output array.
      if ((strlen(trim($sample_name)) > 0) ||
          (strlen(trim($_SESSION['batch_group'][$samplerow])) > 0) ||
          (strlen(trim($_SESSION['sample_description'][$samplerow])) > 0) ||
          (strlen(trim($_SESSION['species'][$samplerow])) > 0))
      {
        $sample_line_array[$line_number]['batch_group'] = $_SESSION['batch_group'][$samplerow];
        $sample_line_array[$line_number]['sample_name'] = trim ($sample_name);
        $sample_line_array[$line_number]['sample_description'] = $_SESSION['sample_description'][$samplerow];
        $sample_line_array[$line_number]['species'] = trim ($_SESSION['species'][$samplerow]);
        $line_number = $line_number + 1;
      }  // if ((strlen(trim($sample_name)) > 0) ||...
    }  // foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
  }  // if (isset ($_SESSION['sample_name']))
  return $sample_line_array;
}  // function array_of_log_sample_data
// **************************************************************
// This function updates an array of log samples
// for a given project log. 
// An array of error messages is returned.
// **************************************************************
function update_log_samples (
 $dbconn, $project_log_run_lane, $project_log_uid, $sample_line_array)
{
  $array_error = array();
  // Delete all the log samples for this project log.
  $project_log_uid = ddl_ready ($project_log_uid);
  $result_delete = pg_query ($dbconn, "
   DELETE FROM $project_log_run_lane
    WHERE project_log_uid = $project_log_uid");
  if (!$result_delete)
  {
    $array_error[] = 'Error deleting previous samples from ' .
                     $project_log_run_lane .
                     ' table: ' .
                     pg_last_error($dbconn);
  } else {
    // Loop through all the rows and all the keys of the input line array.
    foreach ($sample_line_array as $rowkey => $rowvalue)
    {
      // Get new project_log_run_lane_uid from sequence.
      $result_seq = pg_query ($dbconn, "
       SELECT nextval ('project_log_run_lane_project_log_run_lane_uid_seq')");
      if (!$result_seq)
      {
        $array_error[] = 'Stopped processing at row '.
                         $display_row.
                         '. '.
                         pg_last_error($dbconn);
        break;
      } else {
        $project_log_run_lane_uid = pg_fetch_result ($result_seq, 0, 0);
        $display_row = $rowkey + 1;
        $batch_group = ddl_ready ($rowvalue['batch_group']);
        $sample_name = ddl_ready ($rowvalue['sample_name']);
        $sample_description = ddl_ready ($rowvalue['sample_description']);
        $species = ddl_ready ($rowvalue['species']);
        $result_insert = pg_query ($dbconn, "
         INSERT INTO $project_log_run_lane
          (project_log_run_lane_uid, project_log_uid,
           batch_group, sample_name,
           sample_description, species)
         VALUES
           ($project_log_run_lane_uid, $project_log_uid,
            '$batch_group', '$sample_name',
            '$sample_description', '$species')");
        if (!$result_insert)
        {
          $array_error[] = 'Stopped processing at row '.
                           $display_row.
                           '. '.
                           pg_last_error($dbconn);
          break;
        }  // if (!$result_insert)
      }  // if (!$result_seq)
    }  // foreach ($sample_line_array as $rowkey => $rowvalue)
  }  // if (!$result_delete)
  return $array_error;
}  // function update_log_samples
// **************************************************************
// This function appends an array of log samples
// for a given project log. 
// An array of error messages is returned.
// **************************************************************
function append_log_samples (
 $dbconn, $project_log_run_lane, $project_log_uid, $sample_line_array)
{
  $array_error = array();
  // Loop through all the rows and all the keys of the input line array.
  foreach ($sample_line_array as $rowkey => $rowvalue)
  {
    // Get new project_log_run_lane_uid from sequence.
    $result_seq = pg_query ($dbconn, "
     SELECT nextval ('project_log_run_lane_project_log_run_lane_uid_seq')");
    if (!$result_seq)
    {
      $array_error[] = 'Stopped processing at row '.
                       $display_row.
                       '. '.
                       pg_last_error($dbconn);
      break;
    } else {
        $project_log_run_lane_uid = pg_fetch_result ($result_seq, 0, 0);
      $display_row = $rowkey + 1;
      $batch_group = ddl_ready ($rowvalue['batch_group']);
      $sample_name = ddl_ready ($rowvalue['sample_name']);
      $sample_description = ddl_ready ($rowvalue['sample_description']);
      $species = ddl_ready ($rowvalue['species']);
      $result_insert = pg_query ($dbconn, "
       INSERT INTO $project_log_run_lane
        (project_log_run_lane_uid, project_log_uid,
         batch_group, sample_name,
         sample_description, species)
       VALUES
         ($project_log_run_lane_uid, $project_log_uid,
          '$batch_group', '$sample_name',
          '$sample_description', '$species')");
      if (!$result_insert)
      {
        $array_error[] = 'Stopped processing at row '.
                         $display_row.
                         '. '.
                         pg_last_error($dbconn);
        break;
      }  // if (!$result_insert)
    }  // if (!$result_seq)
  }  // foreach ($sample_line_array as $rowkey => $rowvalue)
  return $array_error;
}  // function append_log_samples
// **************************************************************
// This function returns an html string that bolds the input string.
// **************************************************************
function bolded_label ($input_string)
{
  $return_string = '<b>' . $input_string . ': </b>';
  return $return_string;
}  // function bolded_label
?>
