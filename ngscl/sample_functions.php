<?php
require_once 'constants.php';
// ***********************************************************
// This function checks an array of sample data for errors.
// An array of error messages are returned if errors are found.
// ***********************************************************
function check_row_samples ($dbconn, $input_name_array, $input_line_array,
 $required_fields_array, $project_uid, $array_sample_status_values,
 $max_sample_name_length, $max_batch_group_length,
 $use_sample_bonus_columns=FALSE, $type = "INSERT")
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
      // Initialize the error string for this row.
      $display_row = $rowkey + 1;
      $error_prefix = "Data row " . $display_row . ": ";
      $row_error_string = "";
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
          $row_error_string = $error_prefix .
                              "Sample name \"" . $sample_name .
                              "\" invalid. Must be no more than " .
                              $max_sample_name_length .
                              " characters";
          if (!alphanum_hyphen_underscore_only ($sample_name))
          {
             $row_error_string .= ", and only alphanumeric characters, " .
                                  "hyphen, and underscore allowed.";
          }  // if (!alphanum_hyphen_underscore_only ($sample_name))
        } elseif (!alphanum_hyphen_underscore_only ($sample_name))
          {
          $row_error_string = $error_prefix .
                              "Sample name \"" . $sample_name .
                              "\" invalid. Only alphanumeric characters, " .
                              "hyphen, and underscore allowed.";
        }  // if (strlen($sample_name) > $max_sample_name_length)
        if (strlen(trim($row_error_string)) < 1)
        {
          // Check that the sample name is not already in the database.
          if (strtoupper($type) != "UPDATE")
          {
            $result_sample = pg_query ($dbconn, "
             SELECT COUNT(1) AS row_count
               FROM sample
              WHERE project_uid = $project_uid AND
                    sample_name = '$sample_name'");
          } else {
            $sample_uid = $rowvalue['sample_uid'];
            $result_sample = pg_query ($dbconn, "
             SELECT COUNT(1) AS row_count
               FROM sample
              WHERE project_uid = $project_uid AND
                    sample_name = '$sample_name' AND
                    sample_uid != $sample_uid");
          }  // if (strtoupper($type) != "UPDATE")
          if (!$result_sample)
          {
            $array_error[$rowkey] = $error_prefix . pg_last_error($dbconn);
            return $array_error;
          } elseif ($line = pg_fetch_assoc($result_sample)) {
            if ($line['row_count'] > 0)
            {
              $row_error_string = $error_prefix . "Sample \"" .
                                  $sample_name.
                                  "\" already exists for this project";
            }  // if ($line['row_count'] > 0)
          }  // if (!$result_sample)
        }  // if (strlen(trim($row_error_string)) < 1)
      }  // if (strlen(trim($rowvalue['sample_name'])) > 0)
      // Check that the batch group name is not too long.
      if (strlen(trim($rowvalue['batch_group'])) > 0)
      { 
        $batch_group = trim ($rowvalue['batch_group']);
        if (strlen($batch_group) > $max_batch_group_length)
        {
           if (strlen(trim($row_error_string)) > 0)
           {
             $row_error_string .= ". Batch group \"" .
                                 $batch_group .
                                 "\" invalid. Must be no more than " .
                                 $max_batch_group_length . " characters";
           } else {
             $row_error_string = $error_prefix . "Batch group \"" .
                                 $batch_group .
                                 "\" invalid. Must be no more than " .
                                 $max_batch_group_length . " characters";
           }  // if (strlen(trim($row_error_string)) > 0)
        }  // if (strlen($batch_group) > $max_batch_group_length)
      }  // if (strlen(trim($rowvalue['batch_group'])) > 0)
      if ($use_sample_bonus_columns)
      {
        // Check that concentration is a positive number.
        if (strlen(trim($rowvalue['concentration'])) > 0)
        { 
          $concentration = trim ($rowvalue['concentration']);
          if (is_numeric ($concentration))
          {
            if ($concentration < 0.0)
            {
              if (strlen(trim($row_error_string)) < 1)
                $row_error_string = $error_prefix;
              $row_error_string .= ". Concentration must be greater than 0";
            }  // if ($concentration < 0.0)
          } else {
            if (strlen(trim($row_error_string)) < 1)
              $row_error_string = $error_prefix;
            $row_error_string .= ". Concentration must be a number";
          }  // if (is_numeric ($check_row_samples))
        }  // if (strlen(trim($rowvalue['concentration'])) > 0)
        // Check that volume is a positive number.
        if (strlen(trim($rowvalue['volume'])) > 0)
        { 
          $volume = trim ($rowvalue['volume']);
          if (is_numeric ($volume))
          {
            if ($volume < 0.0)
            {
              if (strlen(trim($row_error_string)) < 1)
                $row_error_string = $error_prefix;
              $row_error_string .= ". Volume must be greater than 0";
            }  // if ($volume < 0.0)
          } else {
            if (strlen(trim($row_error_string)) < 1)
              $row_error_string = $error_prefix;
            $row_error_string .= ". Volume must be a number";
          }  // if (is_numeric ($check_row_samples))
        }  // if (strlen(trim($rowvalue['volume'])) > 0)
        // Check that the rna_integrity_number is between 0 and 10.
        if (strlen(trim($rowvalue['rna_integrity_number'])) > 0)
        { 
          $rna_integrity_number = trim ($rowvalue['rna_integrity_number']);
          if (is_numeric ($rna_integrity_number))
          {
            if ($rna_integrity_number < 0.0 || $rna_integrity_number > 10.0)
            {
              if (strlen(trim($row_error_string)) < 1)
                $row_error_string = $error_prefix;
              $row_error_string .= ". RIN must be between 0 and 10";
            }  // if ($rna_integrity_number < 0.0 ||...
          } else {
            if (strlen(trim($row_error_string)) < 1)
              $row_error_string = $error_prefix;
            $row_error_string .= ". RIN must be a number";
          }  // if (is_numeric ($check_row_samples))
        }  // if (strlen(trim($rowvalue['rna_integrity_number'])) > 0)
      }  // if ($use_sample_bonus_columns)
      // Check for missing fields.
      $missing_fields = missing_field_list (
       $rowvalue, $required_fields_array);
      if (strlen(trim($missing_fields)) > 0)
      {
         if (strlen(trim($row_error_string)) > 0)
         {
           $row_error_string .= ". Required fields missing". $missing_fields;
         } else {
           $row_error_string = $error_prefix .
                               "Required fields missing" . $missing_fields;
         }  // if (strlen(trim($row_error_string)) > 0)
      }  // if (strlen(trim($missing_fields)) > 0)
      // Check formatting of individual row variables.
      $format_error_msg = check_sample_values (
       $rowvalue, $array_sample_status_values);
      if (strlen(trim($format_error_msg)) > 0)
      {
        if (strlen(trim($row_error_string)) > 0)
        {
          $row_error_string .= ". " . $format_error_msg;
        } else {
          $row_error_string = $error_prefix . $format_error_msg;
        }  // if (strlen(trim($row_error_string)) > 0)
      }  // if (strlen (trim ($format_error_msg)) > 0)
      if (strlen(trim($row_error_string)) > 0)
      {
        $array_error[$rowkey] = $row_error_string;
      }  // if (strlen(trim($row_error_string)) > 0)
    }  // foreach ($input_line_array as $rowkey as $rowvalue)
  }  // if (count($input_name_array) > count(array_unique($input_name_array)))
  return $array_error;
}  // function check_row_samples
// **************************************************************
// This function updates an array of samples for a given project. 
// An array of error messages is returned.
// **************************************************************
function update_samples (
 $dbconn, $project_uid, $sample_line_array,
 $use_sample_bonus_columns=FALSE, $barcode_separator="")
{
  $array_error = array();
  // Loop through all the rows and all the keys of the input line array.
  foreach ($sample_line_array as $rowkey => $rowvalue)
  {
    $display_row = $rowkey + 1;
    $sample_uid = $rowvalue['sample_uid'];
    $sample_name = ddl_ready ($rowvalue['sample_name']);
    $sample_description = ddl_ready ($rowvalue['sample_description']);
    $status = $rowvalue['status'];
    if (isset ($rowvalue['prep_type']))
    {
      $barcode = ddl_ready (join_barcode (
                  $rowvalue['prep_type'], $rowvalue['barcode_number'],
                  $barcode_separator));
      $barcode_index = ddl_ready (strtoupper (retrieve_barcode_index (
       $dbconn, $rowvalue['prep_type'], $rowvalue['barcode_number'])));
    } else {
      $barcode = ddl_ready ($rowvalue['barcode']);
      $barcode_index = ddl_ready (strtoupper ($rowvalue['barcode_index']));
    }  // if (isset ($rowvalue['prep_type']))
    $species = ddl_ready ($rowvalue['species']);
    $sample_type = ddl_ready ($rowvalue['sample_type']);
    $batch_group = ddl_ready ($rowvalue['batch_group']);
    $comments = ddl_ready ($rowvalue['comments']);
    if ($use_sample_bonus_columns)
    {
      $concentration = ddl_ready ($rowvalue['concentration']);
      $concentration = (is_numeric ($concentration) ?
       $concentration : 'NULL');
      $volume = ddl_ready ($rowvalue['volume']);
      $volume = (is_numeric ($volume) ? $volume : 'NULL');
      $rna_integrity_number = ddl_ready ($rowvalue['rna_integrity_number']);
      $rna_integrity_number = (is_numeric ($rna_integrity_number) ?
       $rna_integrity_number : 'NULL');
      $result = pg_query ($dbconn, "
       UPDATE sample
          SET sample_name = '$sample_name',
              sample_description = '$sample_description',
              status = '$status',
              barcode = '$barcode',
              barcode_index = '$barcode_index',
              species = '$species',
              sample_type = '$sample_type',
              batch_group = '$batch_group',
              concentration = $concentration,
              volume = $volume,
              rna_integrity_number = $rna_integrity_number,
              comments = '$comments'
        WHERE project_uid = $project_uid AND
              sample_uid = $sample_uid");
    } else {
      $result = pg_query ($dbconn, "
       UPDATE sample
          SET sample_name = '$sample_name',
              sample_description = '$sample_description',
              status = '$status',
              barcode = '$barcode',
              barcode_index = '$barcode_index',
              species = '$species',
              sample_type = '$sample_type',
              batch_group = '$batch_group',
              comments = '$comments'
        WHERE project_uid = $project_uid AND
              sample_uid = $sample_uid");
    }  // if ($use_sample_bonus_columns)
    if (!$result)
    {
      $array_error[] = 'Stopped processing at row '.
                       $display_row.
                       '. '.
                       pg_last_error($dbconn);
      break;
    }  // if (!$result)
  }  // foreach ($sample_line_array as $rowkey => $rowvalue)
  return $array_error;
}  // function update_samples
// **************************************************************
// This function checks that there are no custom barcodes
// in the input project.
// **************************************************************
function all_project_barcodes_standard (
 $dbconn, $project_uid, $barcode_separator="")
{
  // Count the number of custom barcodes.
  $result_barcode = pg_query ($dbconn, "
   SELECT COUNT(1)
     FROM sample
    WHERE project_uid = $project_uid AND
          upper (barcode) != 'TBD' AND
          upper (barcode) NOT IN
    (SELECT upper (prep_type) || '$barcode_separator' || barcode_number
       FROM ref_barcode,
            ref_prep_type
      WHERE ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid)");
  if (!$result_barcode)
  {
    $all_standard_boolean = FALSE;
  } else {
    $count_row = pg_fetch_result ($result_barcode, 0, 0);
    if ($count_row > 0)
    {
      $all_standard_boolean = FALSE;
    } else {
      $all_standard_boolean = TRUE;
    }  // if ($count_row > 0)
  }  // if (!$result_barcode)
  return $all_standard_boolean;
}  // function all_project_barcodes_standard ($dbconn, $project_uid)
// **************************************************************
// This function joins the input prep_type and barcode number
// using the input barcode separator.
// **************************************************************
function join_barcode ($prep_type, $barcode_number, $barcode_separator = "")
{
  if (strlen (trim ($barcode_number)) > 0)
  {
    $barcode = $prep_type . $barcode_separator . $barcode_number;
  } else {
    $barcode = $prep_type;
  }  // if (strlen (trim ($barcode_number)) > 0)
  return $barcode;
}  // function join_barcode
// **************************************************************
// This function retrieves the barcode index for the input
// standard barcode.
// **************************************************************
function retrieve_barcode_index (
 $dbconn, $prep_type, $barcode_number)
{
  $barcode_index = "";
  if ($prep_type != 'TBD')
  {
    $result = pg_query ($dbconn, "
     SELECT barcode_index
       FROM ref_prep_type,
            ref_barcode
      WHERE ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid AND
           prep_type = '$prep_type' AND
           barcode_number = $barcode_number");
    if (!$result)
    {
    } else {
      $barcode_index = pg_fetch_result ($result, 0, 0);
    }  // if (!$result)
  }  // if ($prep_type != 'TBD')
  return $barcode_index;
}  // function retrieve_barcode_index
// *************************************************************
// This function returns an array of sample data, based
// on SESSION variables.
// *************************************************************
function array_of_sample_data (
 $barcode_format, $use_sample_bonus_columns=FALSE, $barcode_separator = "")
{
  // Put posted table values into sample line array.
  $line_number = 0;
  foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
  {
    // If at least one field is not blank add the row to the output array.
    if ((strlen(trim($sample_name)) > 0) ||
        (strlen(trim($_SESSION['sample_description'][$samplerow])) > 0) ||
        (strlen(trim($_SESSION['species'][$samplerow])) > 0) ||
        (strlen(trim($_SESSION['sample_type'][$samplerow])) > 0) ||
        (strlen(trim($_SESSION['batch_group'][$samplerow])) > 0) ||
        (strlen(trim($_SESSION['comments'][$samplerow])) > 0))
    {
      $sample_line_array[$line_number]['sample_name'] = trim ($sample_name);
      $sample_name_array[$line_number] = trim ($sample_name);
      $sample_line_array[$line_number]['sample_description'] = trim (
       $_SESSION['sample_description'][$samplerow]);
      $sample_line_array[$line_number]['status'] = $_SESSION['status'][$samplerow];
      if ($barcode_format == 'standard')
      {
        $sample_line_array[$line_number]['prep_type'] = $_SESSION['prep_type'][$samplerow];
        $sample_line_array[$line_number]['barcode_number'] = $_SESSION['barcode_number'][$samplerow];
      } elseif ($barcode_format == 'custom') {
        $sample_line_array[$line_number]['barcode'] = ddl_ready (
         $_SESSION['barcode'][$samplerow]);
        $sample_line_array[$line_number]['barcode_index'] = ddl_ready (
         strtoupper ($_SESSION['barcode_index'][$samplerow]));
      }  // if ($barcode_format == 'standard')
      $sample_line_array[$line_number]['species'] = trim ($_SESSION['species'][$samplerow]);
      $sample_line_array[$line_number]['sample_type'] = trim ($_SESSION['sample_type'][$samplerow]);
      $sample_line_array[$line_number]['batch_group'] = trim ($_SESSION['batch_group'][$samplerow]);
      if ($use_sample_bonus_columns)
      {
        $sample_line_array[$line_number]['volume'] = trim ($_SESSION['volume'][$samplerow]);
        $sample_line_array[$line_number]['concentration'] = trim ($_SESSION['concentration'][$samplerow]);
        $sample_line_array[$line_number]['rna_integrity_number'] = trim ($_SESSION['rna_integrity_number'][$samplerow]);
      }  // if ($use_sample_bonus_columns)
      $sample_line_array[$line_number]['comments'] = trim ($_SESSION['comments'][$samplerow]);
      $line_number = $line_number + 1;
    }  // if ((strlen(trim($sample_name)) > 0) ||...
  }  // foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
  return $sample_line_array;
}  // function array_of_sample_data
// **************************************************************
// This function inserts an array of samples for a given project. 
// An array of error messages is returned.
// **************************************************************
function insert_samples (
 $dbconn, $project_uid, $sample_line_array,
 $use_sample_bonus_columns=FALSE, $barcode_separator = "")
{
  $array_error = array();
  // Loop through all the rows and all the keys of the input line array.
  foreach ($sample_line_array as $rowkey => $rowvalue)
  {
    $display_row = $rowkey + 1;
    $sample_name = ddl_ready ($rowvalue['sample_name']);
    $sample_description = ddl_ready ($rowvalue['sample_description']);
    $status = ddl_ready (ucfirst (strtolower ($rowvalue['status'])));
    if (isset ($rowvalue['prep_type']))
    {
      $barcode = ddl_ready (join_barcode (
                  $rowvalue['prep_type'], $rowvalue['barcode_number'],
                  $barcode_separator));
      $barcode_index = ddl_ready (strtoupper (retrieve_barcode_index (
       $dbconn, $rowvalue['prep_type'], $rowvalue['barcode_number'])));
    } else {
      $barcode = ddl_ready ($rowvalue['barcode']);
      $barcode_index = ddl_ready (strtoupper ($rowvalue['barcode_index']));
    }  // if (isset ($rowvalue['prep_type']))
    $species = ddl_ready ($rowvalue['species']);
    $sample_type = ddl_ready ($rowvalue['sample_type']);
    $batch_group = ddl_ready ($rowvalue['batch_group']);
    if ($use_sample_bonus_columns)
    {
      $concentration = ddl_ready ($rowvalue['concentration']);
      $volume = ddl_ready ($rowvalue['volume']);
      $rna_integrity_number = ddl_ready ($rowvalue['rna_integrity_number']);
      $concentration = (is_numeric ($concentration) ?
       $concentration : 'NULL');
      $volume = (is_numeric ($volume) ? $volume : 'NULL');
      $rna_integrity_number = (is_numeric ($rna_integrity_number) ?
       $rna_integrity_number : 'NULL');

    } else {
      $concentration = 'NULL';
      $volume = 'NULL';
      $rna_integrity_number = 'NULL';
    }  // if ($use_sample_bonus_columns)
    $comments = ddl_ready ($rowvalue['comments']);
    $result = pg_query($dbconn,"
     INSERT INTO sample
      (project_uid, sample_name, sample_description,
       status, barcode, barcode_index,
       species, sample_type, batch_group,
       concentration, volume, rna_integrity_number,
       comments)
     VALUES
      ($project_uid, '$sample_name', '$sample_description',
       '$status', '$barcode', '$barcode_index',
       '$species', '$sample_type', '$batch_group',
       $concentration, $volume, $rna_integrity_number,
       '$comments')");
    if (!$result)
    {
      $array_error[] = 'Stopped processing at row '.
                       $display_row.
                       '. '.
                       pg_last_error($dbconn);
      break;
    }  // if (!$result)
  }  // foreach ($sample_line_array as $rowkey => $rowvalue)
  return $array_error;
}  // function insert_samples
// ***************************************************************
// This function returns an array describing the prep types
// with the input undeclared barcode as the first row.
// ***************************************************************
function create_prep_type_array ($dbconn, $undeclared_barcode)
{
  $prep_type_array = array();
  $prep_type_array[0]['prep_type'] = $undeclared_barcode;
  // Make an array of prep_types.
  $result = pg_query ($dbconn, "
   SELECT ref_prep_type.ref_prep_type_uid,
          prep_type
     FROM ref_barcode,
          ref_prep_type
    WHERE ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid
    GROUP BY ref_prep_type.ref_prep_type_uid,
             prep_type
    ORDER BY prep_type");
  if (!$result)
  {
    $prep_type_array[1]['prep_type'] = 'Error';
  } else {
    $line_number = 1;
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row = pg_fetch_assoc ($result);
      $prep_type_array[$line_number]['prep_type'] = $row['prep_type'];
      $line_number++;
    }  // for ($i=0; $i < pg_num_rows($result); $i++)
  }  // if (!$result)
  return $prep_type_array;
}  // function create_prep_type_array
// **************************************************************
// This function checks values of variables
// in the input sample row.
// An error message is returned if errors are found.
// **************************************************************
function check_sample_values ($rowvalue,
                              $array_sample_status_values)
{
  $error_msg = "";
  // ****
  // Check that the status is in the array.
  // ****
  if (strlen (trim( $rowvalue['status'])) > 0)
  {
    if (strlen (trim (
         match_to_array ($rowvalue['status'],
          $array_sample_status_values))) < 1)
    {
      // Create an error message for sample status value.
      $error_msg .= " Status must be in (";
      foreach ($array_sample_status_values as $valid_status)
      {
        $error_msg .= $valid_status . ",";
      }  // foreach ($array_sample_status_values as $valid_status)
      $error_msg = rtrim ($error_msg, ",");
      $error_msg .= ").";
    }  // if (strlen (trim (...
  }  // if (strlen (trim( $rowvalue['status'])) > 0)
  // ****
  // Check that the prep type contains only
  // alphanumeric characters and the underscore.
  // ****
  if (isset ($rowvalue['prep_type']) &&
      strlen (trim ($rowvalue['prep_type'])) > 0)
  {
    $prep_type = $rowvalue['prep_type'];
    if (!alphanum_plus_underscore_only ($prep_type))
    {
      $error_msg .= " Prep Type " . $prep_type .
                              " invalid.  Only alphanumeric characters " .
                              "and underscore allowed.";
    }  // if (!alphanum_plus_underscore_only ($prep_type))
  }  // if (isset ($rowvalue['prep_type']) &&...
  // ****
  // Check that the barcode contains only
  // alphanumeric characters and the underscore.
  // ****
  if (isset ($rowvalue['barcode']) &&
      strlen (trim ($rowvalue['barcode'])) > 0)
  {
    $barcode = $rowvalue['barcode'];
    if (!alphanum_plus_underscore_only ($barcode))
    {
      $error_msg .= " Barcode " . $barcode .
                              " invalid.  Only alphanumeric characters " .
                              "and underscore allowed.";
    }  // if (!alphanum_plus_underscore_only ($barcode))
  }  // if (isset ($rowvalue['barcode']) &&...
  // ****
  // Check that the barcode index has valid nucleotides only.
  // ****
  if (isset ($rowvalue['barcode_index']))
  {
    if (strlen (trim( $rowvalue['barcode_index'])) > 0)
    {
      if (! dba_nt_only ($rowvalue['barcode_index']))
      {
         $error_msg .= " Barcode index may only " .
                      "have characters A, C, G, T.";
      }  // if (! dba_nt_only ($rowvalue['barcode_index']))
    }  // if (strlen (trim( $rowvalue['barcode_index'])) > 0)
  }  // if (isset ($rowvalue['prep_type']))
  // ****
  // Check that the species contains alphanumber, space,
  // dot, and underscore characters only.
  // ****
  $species = $rowvalue['species'];
  if (strlen (trim( $species)) > 0)
  {
    if (! alphanum_and_input_chars_only ($species, '._ '))
    {
      $error_msg .= " Species " . $species .
                   " invalid.  Only alphanumeric " .
                   "characters space, dot, and " .
                   "underscore allowed.";
    }  // if (! alphanum_and_input_chars_only ($species, '._ '))
  }  // if (strlen (trim( $rowvalue['species'])) > 0)
  return $error_msg;
}  // function check_sample_values 
?>
