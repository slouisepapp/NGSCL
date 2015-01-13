<?php
// **************************************************************
// This function connects to the database. It returns
// the PostgreSQL connection resource on success and
// FALSE on failure.
// **************************************************************
function database_connect()
{
  $db_name = (isset($_SESSION['db_name']) ? $_SESSION['db_name'] : "");
  $schema_name = (isset($_SESSION['schema_name']) ?
                  $_SESSION['schema_name'] : "");
  $postgres_port = (isset($_SESSION['postgres_port']) ?
                  $_SESSION['postgres_port'] : 5432);
  $user = (isset($_SESSION['user']) ? $_SESSION['user'] : "");
  $pass = (isset($_SESSION['pass']) ? $_SESSION['pass'] : "");
  $conn_string = "host=localhost port=" .
                 $postgres_port .
                 " dbname=" .
                 $db_name .
                 " user=$user password=$pass";
    // Keep buffer warning from showing if logon unsuccessful.
    ob_start();
    $dbconn = pg_connect($conn_string);
    // Turn buffer warning back on.
    ob_end_clean();
    if(!$dbconn)
        return FALSE;
    $result = pg_query($dbconn, 'SET search_path='.$schema_name.',public');
    if (!$result)
      return FALSE;
    return $dbconn;
} // function database_connect
// **************************************************************
// This abstract class defines the user information.
// **************************************************************
abstract class DbUser
{
  abstract function __construct ($dbconn, $username);
}  // abstract class DbUser
// **************************************************************
// This class defines the user information.
// **************************************************************
class NgsclUser
 extends DbUser
{
  function __construct ($dbconn, $username)
  {
    // Determine the most privileged role of the user.
    $result_role = pg_query ($dbconn, '
     SELECT role_name
       FROM information_schema.enabled_roles');
    $this->app_role = "";
    $this->admin_role = "";
    if (!$result_role)
    {
    } else {
      for ($i=0; $i < pg_num_rows ($result_role); $i++)
      {
        $role_name = pg_fetch_result ($result_role, $i, 0);
        if ($role_name == 'dac_grants')
        {
          $this->app_role = 'dac_grants';
          $this->admin_role = 'dac_grants';
          break;
        } elseif ($role_name == 'pi_admin') {
          $this->admin_role = 'pi_admin';
          $this->app_role = 'pi_user';
        } elseif ($role_name == 'pi_user') {
          $this->app_role = 'pi_user';
        }  // if ($role_name = 'dac_grants')
      }  // for ($i=0; $i < pg_num_rows ($result_role); $i++)
    }  // if (!$result_role)
  }  // function __construct
}  // class NgsclUser
function upload_text_file($upload_dir, $max_file_size)
{
  $upload_message = "";
  $uploadfile = $upload_dir . "/" . basename($_FILES['userfile']['name']);
  // Limit the file size.
  if ($_FILES['userfile']['size'] < $max_file_size) {
    if ($_FILES['userfile']['type'] = "text/plain") {
      if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        if (! copy($_FILES['userfile']['tmp_name'], $uploadfile)) {
          $upload_message = "Upload unsuccessful.";
          }  // if (! copy($_FILES['userfile']['tmp_name'], $uploadfile))
      } else {
        $upload_message = "Upload unsuccessful.";
        }  // if (is_uploaded_file...
    } else {
      $upload_message = "File is not a plain text file.";
      } // if ($_FILES['userfile']['type'] = "text/plain")
  } else {
    $upload_message = "File is larger than ".round ($max_file_size, 1)."M.";
  }  // if ($_FILES['userfile']['size']...
  return $upload_message;
}  // function upload_text_file
function clear_pi_vars()
{
  if(isset($_SESSION['last_name']))
    unset($_SESSION['last_name']);
  if(isset($_SESSION['first_name']))
    unset($_SESSION['first_name']);
  if(isset($_SESSION['choose_primary_investigator_status']))
    unset($_SESSION['choose_primary_investigator_status']);
  if(isset($_SESSION['email_address']))
    unset($_SESSION['email_address']);
  if(isset($_SESSION['phone_number']))
    unset($_SESSION['phone_number']);
  if(isset($_SESSION['comments']))
    unset($_SESSION['comments']);
}  // function clear_pi_vars
function clear_contact_vars()
{
  if(isset($_SESSION['choose_pi']))
    unset($_SESSION['choose_pi']);
  if(isset($_SESSION['last_name']))
    unset($_SESSION['last_name']);
  if(isset($_SESSION['first_name']))
    unset($_SESSION['first_name']);
  if(isset($_SESSION['email_address']))
    unset($_SESSION['email_address']);
  if(isset($_SESSION['phone_number']))
    unset($_SESSION['phone_number']);
  if(isset($_SESSION['comments']))
    unset($_SESSION['comments']);
}  // function clear_contact_vars
function clear_project_vars()
{
  if(isset($_SESSION['choose_pi']))
    unset($_SESSION['choose_pi']);
  if(isset($_SESSION['project_name']))
    unset($_SESSION['project_name']);
  if(isset($_SESSION['choose_project_status']))
    unset($_SESSION['choose_project_status']);
  if(isset($_SESSION['creation_date']))
    unset($_SESSION['creation_date']);
  if(isset($_SESSION['project_description']))
    unset($_SESSION['project_description']);
  if(isset($_SESSION['analysis_notes']))
    unset($_SESSION['analysis_notes']);
  if(isset($_SESSION['admin_comments']))
    unset($_SESSION['admin_comments']);
  if(isset($_SESSION['choose_contact']))
    unset($_SESSION['choose_contact']);
}  // function clear_project_vars
function clear_prep_note_vars()
{
  if(isset($_SESSION['library_prep_note_name']))
    unset($_SESSION['library_prep_note_name']);
  if(isset($_SESSION['creation_date']))
    unset($_SESSION['creation_date']);
  if(isset($_SESSION['comments']))
    unset($_SESSION['comments']);
}  // function clear_prep_note_vars
function clear_run_vars()
{
  if(isset($_SESSION['run_number']))
    unset($_SESSION['run_number']);
  if(isset($_SESSION['run_name']))
    unset($_SESSION['run_name']);
  if(isset($_SESSION['choose_run_type']))
    unset($_SESSION['choose_run_type']);
  if(isset($_SESSION['read_type']))
    unset($_SESSION['read_type']);
  if(isset($_SESSION['read_1_length']))
    unset($_SESSION['read_1_length']);
  if(isset($_SESSION['read_2_length']))
    unset($_SESSION['read_2_length']);
  if(isset($_SESSION['read_length_indexing']))
    unset($_SESSION['read_length_indexing']);
  if(isset($_SESSION['hi_seq_slot']))
    unset($_SESSION['hi_seq_slot']);
  if(isset($_SESSION['cluster_gen_start_date']))
    unset($_SESSION['cluster_gen_start_date']);
  if(isset($_SESSION['sequencing_start_date']))
    unset($_SESSION['sequencing_start_date']);
  if(isset($_SESSION['truseq_cluster_gen_kit']))
    unset($_SESSION['truseq_cluster_gen_kit']);
  if(isset($_SESSION['flow_cell_hs_id']))
    unset($_SESSION['flow_cell_hs_id']);
  if(isset($_SESSION['sequencing_kits']))
    unset($_SESSION['sequencing_kits']);
  if(isset($_SESSION['comments']))
    unset($_SESSION['comments']);
}  // function clear_run_vars
// **************************************************************
// This function returns a blank space if the input is null.
// Otherwise, it simply returns the input.
// It is useful in ensuring a border if the table cell content is null.
// Also, it runs the htmlentities to encode special HTML characters.
// This is a security measure.
// **************************************************************
function td_ready($cell_content)
{
  $td_content = htmlentities ($cell_content, ENT_NOQUOTES);
  if (empty($td_content) || strlen(trim($td_content)) < 1)
    if ($td_content != "0")
      $td_content = "&nbsp;";
  return $td_content;
}  // function td_ready
// **************************************************************
// This function returns an empty string if the input is not set.
// Otherwise, it simply returns the input.
// Also, it runs the htmlentities to encode special HTML characters.
// This is a security measure.
// **************************************************************
function input_ready($cell_content)
{
  $input_content = htmlentities ($cell_content, ENT_NOQUOTES);
  return $input_content;
}  // function input_ready
// **************************************************************
// This function returns an empty string if the input is not set.
// Otherwise, it simply returns the input.
// Also, it runs the htmlentities to encode special HTML characters.
// This is a security measure.
// **************************************************************
function input_ready_quotes($cell_content)
{
  $input_content = htmlentities ($cell_content, ENT_QUOTES);
  return $input_content;
}  // function input_ready
// *************************
// This function parses an input string by the input delimiter,
// and puts the fields in a new row of the input array.
// *************************
function string_to_array ($input_string, $delimiter, $key_array) {
  // Explode key string by delimiter.
  $required_num_fields = count($key_array);
  // Parse string.
  $string_fields = str_getcsv ($input_string, $delimiter);
  $num_fields = count($string_fields);
  $all_blank = 1;
  // Check that at least one field is not blank.
  for ($i=0; $i<$num_fields; $i++) {
    if (strlen(trim($string_fields[$i])) > 0)
    {
      $all_blank = 0;
    }
  }  // for ($i=0; $i<$num_fields; $i++)
  // If string is not empty, add to input array.
  if ($all_blank == 0)
  {
    // Add fields to the input array.
    for ($i=0; $i<$num_fields; $i++)
    {
      $output_array[$key_array[$i]] = str_replace (
       '"', '', $string_fields[$i]);
    }  // for ($i=0; $i<$num_fields; $i++) {
    // Add to string fields if it has less than the required number of fields.
    for ($i=$num_fields; $i<$required_num_fields; $i++)
    {
      $output_array[$key_array[$i]] = "";
    }  // for ($i=0; $i<$num_fields; $i++)
    return $output_array;
  } else {
    return FALSE;
  }  // if ($all_blank == 0)
}  // function string_to_array
// **************************************************************
// This function compares an input array of strings to
// an input string in which the fields are separated by
// a delimiter.  If the fields in the array and the fields
// in the input string are different, error messages are output.
// **************************************************************
function validate_header_string ($sample_key_array, $comparison_string,
 $delimiter) {
  // Create an array to hold errors.
  $array_error = array();
  // Strip trailing delimiters.
  $mask = $delimiter . "\r\n";
  $comparison_string = rtrim ($comparison_string, $mask);
  // Parse comparison string by delimiter.
  $comparison_fields = str_getcsv ($comparison_string, $delimiter);
  // If the number of fields is not the same, return error.
  $correct_num_fields = count($sample_key_array);
  $comparison_num_fields = count($comparison_fields);
  if ($comparison_num_fields > $correct_num_fields)
  {
    $err_msg = "Too many columns.  There should be ".$correct_num_fields.".";
    array_push ($array_error, $err_msg);
  } elseif ($comparison_num_fields < $correct_num_fields) {
    $err_msg = "Not enough columns.  There should be ".$correct_num_fields.".";
    array_push ($array_error, $err_msg);
  } else {
    // Compare each field.  Ignore case and whether plural.
    for ($i=0; $i < $correct_num_fields; $i++)
    {
      if (strcasecmp (trim($sample_key_array[$i]," \n\r\0sS"),
                      trim($comparison_fields[$i]," \n\r\0sS")) != 0)
      {
        $col_number = $i + 1;
        $err_msg = "Column header ".$col_number.
                   " (".$comparison_fields[$i].
                   ") is incorrect. Should be ".$sample_key_array[$i].".";
        array_push ($array_error, $err_msg);
      }  // if (strcasecmp ($trim($sample_key_array[i]," sS"),...
    }  // for ($i=0; $i < $correct_num_fields; $i++) {
  }  // if (count($sample_key_array) != count($comparison_fields))
  return $array_error;
}  // function validate_header_string
// **************************************************************
// This function will return a string indicating TRUE if the input
// is any string value accepted for the true boolean by PostgreSQL.
// Otherwise, a string indicating FALSE will be returned.
// **************************************************************
function standardize_boolean($boolean_input)
{
  $boolean_string = strtoupper ($boolean_input);
  // Any boolean value accepted as true by PostgreSQL will be set to true.
  if (($boolean_string == 'TRUE') ||
      ($boolean_string == 'T') ||
      ($boolean_string == 'Y') ||
      ($boolean_string == 'YES') ||
      ($boolean_string == 'ON') ||
      ($boolean_string == '1'))
  {
    return 'TRUE';
  } else {
    return 'FALSE';
  }  // if (($boolean_string == 'TRUE') ||...
}  // function standardize_boolean
// **************************************************************
// This function accepts an array and a list of keys for the array.
// If any of the key fields in the input list are empty
// in the input array, then the key is added to the list of
// missing fields.
// **************************************************************
function missing_field_list ($input_array, $key_list) {
  $missing_fields = '';
  // Loop through all the required fields.
  foreach ($key_list as $keynum => $keyvalue)
  {
    // If the field is missing update error boolean and
    // add to the error message.
    if (strlen (trim ($input_array[$keyvalue])) < 1)
    {
      $missing_fields = $missing_fields.", ".$keyvalue;
    }  // if (strlen (trim ($input_array[$keyvalue])) < 1)
  }  // foreach ($key_list as $keynum => $keyvalue)
  return ($missing_fields);
}  // function missing_field_list ($input_array, $key_list)
// **************************************************************
// This function returns a SQL condition for the WHERE clause.
// **************************************************************
function where_condition ($variable_name, $variable_value,
                           $needs_quotes=0, $default_value="Show All")
{
  $condition_string = "";
  // Condition string is empty if variable name is empty.
  if (strlen (trim ($variable_name)) > 0)
  {
    if (isset ($variable_value) && strlen (trim ($variable_value)) > 0)
    {
      // Condition string is empty if variable name is Show All or -1.
      if (strcasecmp (trim($variable_value), trim($default_value)) != 0)
      {
        // Add quotes to variable value if they are needed.
        if ($needs_quotes == 0)
        {
          $condition_string = $variable_name." = ".$variable_value;
        } else {
          $condition_string = $variable_name." = '".$variable_value."'";
        }  // if ($needs_quotes == 0)
      } // if (strcasecmp (trim($variable_value), trim("SHOW ALL")) != 0)
    }  // if (isset ($variable_value))
  }  // if (strlen (trim ($variable_name)) > 0) &&...
  return $condition_string;
}  // function where_condition
// **************************************************************
// This function creates a drop-down boolean list.
// **************************************************************
function drop_down_boolean ($select_name, $true_selected, $false_selected,
                            $drop_down_class, $title="")
{
  $drop_down_html = '<select name="'.
                    $select_name.
                    '" class="'.
                    $drop_down_class.
                    '" title="'.
                    $title.'">'.
                    '<option value="TRUE" '.
                    $true_selected.
                    '>TRUE</option>'.
                    '<option value="FALSE" '.
                    $false_selected.
                    '>FALSE</option>'.
                    '</select>';
  return $drop_down_html;
}  // function drop_down_boolean
// **************************************************************
// This function creates a drop-down list based on an input array.
// **************************************************************
function drop_down_array ($select_name, $select_value,
                          $drop_down_class, $option_array,
                          $title="", $default_display="Show All",
                          $default_value="Show All", $default_in_array=0,
                          $keyed_array=0)
{
  $drop_down_html = '';
  if (strlen (trim ($select_name)) > 0)
    {
    $drop_down_html = '<select name="'.
                      $select_name.
                      '" onchange="this.form.submit();" class="'.
                      $drop_down_class.'" title="'.$title.'">';
    // If an option other than the default value has already been entered,
    // display it.  Otherwise use the default name.
    $option_selected = 'selected="selected"';
    if (isset($select_value) && trimmed_string_not_empty ($select_value))
    {
      if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
      {
        $option_selected = '';
      }  // if (strcasecmp (trim ($select_value), trim ($default_value)) != 0)
    }  // if (isset($select_value) && trimmed_string_not_empty ($select_value))
    $drop_down_html = $drop_down_html.
                      '<option value="'.
                      $default_value.'" '.
                      $option_selected.
                      '>'.
                      $default_display.'</option>';
    foreach ($option_array as $rowkey => $rowvalue)
    {
      $option_display = $rowvalue;
      if ($keyed_array == 0)
      {
        $option_value = $rowvalue;
      } else {
        $option_value = $rowkey;
      }  // if ($keyed_array == 0)
      // If default is part of array, skip the default value.
      if (($default_in_array == 0)  ||
           (strcasecmp (trim ($option_value), trim ($default_value)) != 0))
      {
        // If this option has been entered, select it.
        $option_selected = '';
        if (isset($select_value) && trimmed_string_not_empty ($select_value))
        {
          if ($option_value == $select_value)
          {
            $option_selected = 'selected="selected"';
          }  // if ($option_value == $select_value)
        }  // if (isset($select_value) &&...
        $drop_down_html = $drop_down_html.'<option value="'.
                          $option_value.'" '.
                          $option_selected.'>'.
                          $option_display.'</option>';
      }  // if (($default_in_array == 0)  ||...
    }  // foreach ($option_array as $option_value)
    $drop_down_html = $drop_down_html.'</select>';
  }  // if (strlen (trim ($select_name)) > 0)
  return $drop_down_html;
}  // function drop_down_array
// **************************************************************
// This function creates a drop-down list based on a table select.
// **************************************************************
function drop_down_table ($dbconn, $select_name, $select_value,
                          $drop_down_class, $option_table,
                          $option_value_col, $option_display_col,
                          $title="", $where_clause=" ",
                          $default_display="Show All",
                          $default_value="Show All", $default_in_select=0,
                          $on_change_submit=1, $disabled_string="")
{
  $drop_down_html = '';
  if (strlen (trim ($select_name)) > 0)
  {
    // Select all the option values and display strings.
    $query_string = 'SELECT '.$option_value_col.', '.
                            $option_display_col.
                    '  FROM '.$option_table.' '.
                    $where_clause.
                    ' ORDER BY '.$option_display_col;
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
}  // function drop_down_table
// **************************************************************
// This function prepares a string to be used in a SQL statement.
// **************************************************************
function ddl_ready ($input_string)
{
  $ddl_text = trim (str_replace("'", "''", $input_string), "\"");
  $ddl_text = trim ($ddl_text);
  // Set a reasonable upper limit on string length.
  if (strlen ($ddl_text) > 10000)
  {
    $ddl_text = substr ($ddl_text, 0, 10000);
  }  // if (strlen ($ddl_text) > 10000)
  return $ddl_text;
}  // function ddl_ready ($input_string)
// **************************************************************
// This function clears the session drop down list variables.
// **************************************************************
function clear_drop_down_vars()
{
  if(isset($_SESSION['choose_archive']))
    unset($_SESSION['choose_archive']);
  if(isset($_SESSION['choose_contact']))
    unset($_SESSION['choose_contact']);
  if(isset($_SESSION['choose_pi']))
    unset($_SESSION['choose_pi']);
  if(isset($_SESSION['choose_project']))
    unset($_SESSION['choose_project']);
  if(isset($_SESSION['choose_project_status']))
    unset($_SESSION['choose_project_status']);
  if(isset($_SESSION['choose_sample_status']))
    unset($_SESSION['choose_sample_status']);
  if(isset($_SESSION['choose_sample_status_archive_select']))
    unset($_SESSION['choose_sample_status_archive_select']);
  if(isset($_SESSION['choose_pi_archive_select']))
    unset($_SESSION['choose_pi_archive_select']);
  if(isset($_SESSION['choose_pi_contact_select']))
    unset($_SESSION['choose_pi_contact_select']);
  if(isset($_SESSION['choose_project_archive_select']))
    unset($_SESSION['choose_project_archive_select']);
  if(isset($_SESSION['choose_archive_archive_select']))
    unset($_SESSION['choose_archive_archive_select']);
}  // function clear_drop_down_vars()
// **************************************************************
// This function replaces end-of-line characters with a space.
// **************************************************************
function replace_eol ($input_text)
{
  $replacement = " ";
  $pattern = '/(\r\n)/';
  $input_text = preg_replace ($pattern, $replacement, $input_text);
  $pattern = '/\r|\n/';
  $input_text = preg_replace ($pattern, $replacement, $input_text);
  return $input_text;
}  // function replace_eol ($input_text)
// **************************************************************
// This function checks that the input string contains only
// the characters that represent DNA nucleotides (actgACGT)
// plus the hyphen.
// **************************************************************
function dba_nt_only ($input_text)
{
  if (preg_match('/[^ACGTacgt-]/', $input_text))
  {
    return FALSE;
  } else {
    return TRUE;
  }  // if (preg_match('/[^ACGTacgt-]/', $input_text)
}  // function dba_nt_only
// **************************************************************
// This function checks that the input string contains only
// the (English) alpha and numeric characters and underscores.
// **************************************************************
function alphanum_plus_underscore_only ($input_text)
{
  if (preg_match('/[^A-Za-z0-9_]/', $input_text))
  {
    return FALSE;
  } else {
    return TRUE;
  }  // if (preg_match('/[^A-Za-z0-9_]/', $input_text))
}  // function alphanum_plus_underscore_only
// **************************************************************
// This function modifies the input string to contain only
// the (English) alpha and numeric characters and underscores.
// A subset of the forbidden characters will be replaced and
// the rest simply removed.
// **************************************************************
function convert_to_alphanum_plus_underscore_only ($input_string)
{
  // Replace forbidden characters where this makes sense.
  $return_string = preg_replace ('/[\. \-\/]/', '_', $input_string);
  // Remove all the rest of the forbidden characters.
  $return_string = preg_replace ('/[^A-Za-z0-9_]/', '', $return_string);
  return $return_string;
}  // function convert_to_alphanum_plus_underscore_only
// **************************************************************
// This function checks that the first input string contains
// only the (English) alpha and numeric characters
// and the characters in the second input string.
// **************************************************************
function alphanum_and_input_chars_only ($input_text, $input_chars)
{
  $pattern = "/[^A-Za-z0-9" . $input_chars . "]/";
  if (preg_match($pattern, $input_text))
  {
    return FALSE;
  } else {
    return TRUE;
  }  // if (preg_match($pattern, $input_text))
}  // function alphanum_and_input_chars_only
// **************************************************************
// This function checks an array of sample data for barcodes
// that are not standard.
// An array of error messages are returned if errors are found.
// **************************************************************
function check_standard_barcodes (
 $dbconn, &$input_line_array, $undeclared_barcode)
{
  $array_error = array();
  // Loop through all the rows of the input line array.
  foreach ($input_line_array as $rowkey => $rowvalue)
  {
    $barcode_error = "";
    $prep_type = trim ($rowvalue['prep_type']);
    $barcode_number = trim ($rowvalue['barcode_number']);
    // Check that the prep type exists.
    if (strlen ($prep_type) > 0)
    {
      // Check that the prep type is valid.
      if ($prep_type != $undeclared_barcode)
      {
        $result_prep_type = pg_query ($dbconn, "
         SELECT prep_type
           FROM ref_prep_type
          WHERE upper (prep_type) = upper ('$prep_type')");
        if (!$result_prep_type)
        {
          $barcode_error = pg_last_error ($dbconn);
        } elseif (pg_num_rows ($result_prep_type) < 1) {
          $barcode_error = $prep_type . " is not a standard prep type.";
        } else {
          $input_line_array[$rowkey]['prep_type'] =
           pg_fetch_result ($result_prep_type, 0, 0);
          // Check if there is a barcode number.
          if (strlen ($barcode_number) < 1)
          {
            $barcode_error = "A barcode number is required.";
          } else {
            // Check that the barcode number is a positive integer.
           if (! (is_int ($barcode_number) || ctype_digit ($barcode_number)) ||
               $barcode_number < 1)
            {
              $barcode_error = "Barcode number " .
                               $barcode_number .
                               " is not a positive integer.";
            } else {
              // Check that the barcode number is valid for this prep type.
              $result_barcode_number = pg_query ($dbconn, "
               SELECT COUNT(1) 
                 FROM ref_barcode,
                      ref_prep_type
                WHERE ref_barcode.ref_prep_type_uid =
                       ref_prep_type.ref_prep_type_uid AND
                      upper (prep_type) = upper ('$prep_type') AND
                      barcode_number = $barcode_number");
              if (!$result_barcode_number)
              {
                $barcode_error = pg_last_error ($dbconn);
              } elseif (pg_fetch_result ($result_barcode_number, 0, 0) < 1) {
                $barcode_error = $barcode_number .
                                 " is not in the range for prep type " .
                                 $prep_type . ".";
              }  // if (!$result_barcode_number)
            }  // if (! (is_int ($barcode_number) ||...
          }  // if (strlen ($barcode_number) < 1)
        }  // if (!$result_prep_type)
      } else {
        if (strlen ($barcode_number) > 0)
        {
          $barcode_error = "Barcode number not permitted for " .
                           $undeclared_barcode . ".";
        }  // if (strlen ($barcode_number) > 0)
      }  // if ($prep_type != $undeclared_barcode)
    }  // if (strlen ($prep_type) > 0)
    if (strlen (trim ($barcode_error)) > 0)
    {
      $display_row = $rowkey + 1;
      $array_error[] = "Data row ". $display_row . ": " . $barcode_error;
    }  // if (strlen (trim ($barcode_error)) > 0)
  }  // foreach ($input_line_array as $rowkey as $rowvalue)
  return $array_error;
}  // function check_standard_barcodes
// ***************************************************************
// This function takes two arrays of messages which start
// with the prefix:
//    Data row <n>:
// The function returns a single array numerically sorted by <n>.
// ***************************************************************
function error_array_merge ($error_array1, $error_array2)
{
  $data_row_array = array();
  // Merge the two arrays.
  $merged_array = array_merge ($error_array1, $error_array2);
  // Create an array with a field indicating the data row.
  foreach ($merged_array as $key => $error_msg)
  {
    $pieces = explode (':', substr ($error_msg, 9));
    $data_row_array[$key] = $pieces[0];
  }  // foreach ($error_array1 as $key => $error_msg)
  // Sort the array by data row.
  $sort_success = array_multisort (
   $data_row_array, SORT_NUMERIC, $merged_array);
  if ($sort_success)
  {
    return $merged_array;
  } else {
    return 'Multisort failed.';
  }  // if ($sort_success)
}  // function error_array_merge
// **************************************************************
// This function checks that the input value matches one
// of the entries of the input array.  Case-sensitivity can
// be specified, but by default the match is not case-sensitive.
// The array value is returned.
// **************************************************************
function match_to_array ($input_value, $input_array, $case_sensitive = FALSE)
{
  $return_value = "";
  if ($case_sensitive)
  {
    foreach ($input_array as $rowvalue)
    {
      if (strcmp ($input_value, $rowvalue) == 0)
      {
        $return_value = $rowvalue;
        break;
      }  // if (strcasecmp ($input_value, $rowvalue) == 0)
    }  // foreach ($input_array as $rowvalue)
  } else {
    foreach ($input_array as $rowvalue)
    {
      if (strcasecmp ($input_value, $rowvalue) == 0)
      {
        $return_value = $rowvalue;
        break;
      }  // if (strcasecmp ($input_value, $rowvalue) == 0)
    }  // foreach ($input_array as $rowvalue)
  }  // if ($case_sensitive)
  return $return_value;
}  // function match_to_array 
// **************************************************************
// This function creates a file with read, write permissions
// for the php writer only.  Then the input string is written
// to the file.
// Returns TRUE if successful and FALSE otherwise.
// **************************************************************
function write_private_file ($file_path_and_name, $input_string)
{
  $return_value = FALSE;
  $file_string = htmlentities ($input_string);
  $open_handle = fopen ($file_path_and_name, "w");
  if (! $open_handle)
  {
  } else {
    if (fclose ($open_handle))
    {
      if (chmod ($file_path_and_name, 0600))
      {
        $write_handle = fopen ($file_path_and_name, "w");
        if (! $write_handle)
        {
        } else {
          if (fwrite ($write_handle, $file_string) &&
              fclose ($write_handle))
          {
            $return_value = TRUE;
          }  // if (fwrite ($write_handle, $file_string) &&...
        }  // if (! write_handle)
      }  // if (chmod ($file_path_and_name, 0600))
    }  // if (fclose ($open_handle))
  }  // if (! $open_handle)
  return $return_value;
}  // function write_private_file
// **************************************************************
// This function checks that the trimmed input string is 
// longer than zero.
// **************************************************************
function trimmed_string_not_empty ($input_text)
{
  if (strlen (trim ($input_text)) > 0)
  {
    return TRUE;
  } else {
    return FALSE;
  }  // if (strlen (trim ($input_text)) > 0)
}  // function trimmed_string_not_empty
// **************************************************************
// This abstract class defines the sidebar of the web page.
// **************************************************************
abstract class Sidebar
{
  abstract function __construct ($user);
  abstract function makeSidebar ();
}  // abstract class Sidebar
// **************************************************************
// This class defines the sidebar of the web page for the user.
// **************************************************************
class NgsclSidebar
 extends Sidebar
{
  function __construct ($user)
  {
    $this->user = $user;
    $this->array_option = array();
  }  // function __construct
  function makeSidebar ()
  {
    $sidebar_string = '<div id="sidebar1" class="sidebarmenu" ' .
                      'style="padding: 5px;">';
    $sidebar_string .= '<ul id="sidebarmenu1">';
    // Add menu items.
    foreach ($this->array_option as $key => $value)
    {
      $sidebar_string .= '<li><span class="class2"><a href="' . 
           $value['link'] .
           '" title="' . 
           $value['title'] .
           '">' . 
           $value['display'] .
           '</a></span>';
      // Add sub-menu items.
      if ($value['include_subitems'])
      {
        $sidebar_string .= '<ul>';
        foreach ($value['subarray'] as $subkey => $subvalue)
        {
          $sidebar_string .= '<li><span class="class2"><a href="' . 
               $subvalue['link'] .
               '" title="' . 
               $subvalue['title'] .
               '">' . 
               $subvalue['display'] .
               '</a></span></li>';
        }  // foreach ($value['subarray'] as $subkey => $subvalue)
        $sidebar_string .= '</ul>';
      }  // if ($value['include_subitems'])
      $sidebar_string .= '</li>';
    }  // foreach ($this->array_option as $key => $value)
    $sidebar_string .= '</ul>';
    if (isset($this->user))
      $sidebar_string .= '<br /><h4 style="color: gray;">User: ' . 
           $this->user . '</h4>';
    $sidebar_string .= '</div>';
    return ($sidebar_string);
  }  // function makeSidebar
}  // class NgsclSidebar
// **************************************************************
// This class defines the sidebar of the web page for the Admin user.
// **************************************************************
class NgsclAdminSidebar
 extends NgsclSidebar
{  
  function __construct ($user)
  {
    $this->user = $user;
    // Define all the sidebar menu options.
    $this->array_option = array (
     0 => array ("link" => 'primary_investigator.php',
                 "title" => 'Manage primary investigator.',
                 "display" => 'Primary Investigator',
                 "include_subitems" => FALSE),
     1 => array ("link" => 'project.php',
                 "title" => 'Manage project.',
                 "display" => 'Project',
                 "include_subitems" => FALSE),
     2 => array ("link" => '#',
                 "title" => '',
                 "display" => 'Sample',
                 "include_subitems" => TRUE,
                 "subarray" => array (
                   0 => array ("link" => 'sample.php',
                               "title" => 'Lists sample information.',
                               "display" => 'Sample List'),
                   1 => array ("link" => 'sample_archive.php',
                               "title" => 'Manage sample archive.',
                               "display" => 'Sample Archive'),
                   2 => array ("link" => 'library_prep_note.php',
                               "title" => 'Manage library prep notes.',
                               "display" => 'Library Prep Note'),
                   3 => array ("link" => 'manage_barcodes.php',
                               "title" => 'Manage barcodes.',
                               "display" => 'Barcodes'),
                   )
                 ),
     3 => array ("link" => 'work_schedule.php',
                 "title" => 'Manage work schedule.',
                 "display" => 'Work Schedule',
                 "include_subitems" => FALSE),
     4 => array ("link" => 'run.php',
                 "title" => 'Manage run.',
                 "display" => 'Run',
                 "include_subitems" => FALSE),
     5 => array ("link" => 'contact.php',
                 "title" => 'Manage contacts.',
                 "display" => 'Contact',
                 "include_subitems" => FALSE),
     6 => array ("link" => 'logout.php',
                 "title" => 'Log out of NGS Core - Illumina.',
                 "display" => 'Log Out',
                 "include_subitems" => FALSE)
    );
  }  // function __construct
}  // class NgsclAdminSidebar
// **************************************************************
// This class defines the sidebar of the web page for the pi user.
// **************************************************************
class NgsclPiSidebar
 extends NgsclSidebar
{  
  function __construct ($user)
  {
    $this->user = $user;
    // Define all the sidebar menu options.
    $this->array_option = array (
     0 => array ("link" => 'primary_investigator.php',
                 "title" => 'Manage primary investigator.',
                 "display" => 'Primary Investigator',
                 "include_subitems" => FALSE),
     1 => array ("link" => 'project.php',
                 "title" => 'Manage project.',
                 "display" => 'Project',
                 "include_subitems" => FALSE),
     2 => array ("link" => 'sample.php',
                 "title" => 'Lists sample information.',
                 "display" => 'Sample List',
                 "include_subitems" => FALSE),
     3 => array ("link" => 'run.php',
                 "title" => 'Manage run.',
                 "display" => 'Run',
                 "include_subitems" => FALSE),
     4 => array ("link" => 'contact.php',
                 "title" => 'Manage contacts.',
                 "display" => 'Contact',
                 "include_subitems" => FALSE),
     5 => array ("link" => 'logout.php',
                 "title" => 'Log out of NGS Core - Illumina.',
                 "display" => 'Log Out',
                 "include_subitems" => FALSE)
    );
  }  // function __construct
}  // class NgsclPiSidebar
// **************************************************************
// This abstract class defines the appropriate database table or
// view based on the input table, app role, and username.
// **************************************************************
abstract class CorrespondingView
{
  abstract function __construct (
   $dbconn, $tablename, $app_role, $username, $pi_uid);
}  // abstract class CorrespondingView
// **************************************************************
// This class defines the appropriate NGSCL database table
// or view based on the input table, app role, and username.
// **************************************************************
class NgsclCorrespondingView
 extends CorrespondingView
{
  function __construct (
   $dbconn, $tablename, $app_role, $username, $pi_uid)
  {
    $from_item = $tablename;
    // Tablename should be the associated view if the role is a pi.
    if ($app_role == 'pi_user')
    {
      $from_item = 'pi' . $pi_uid . '_' . $tablename;
    }  // if ($app_role == 'pi_user')
    $this->from_item = $from_item;
  }  // function __construct
}  // class NgsclCorrespondingView
// **************************************************************
// This class defines the primary_investigator_uid
// to be used in searches.
// **************************************************************
class SessionSearchPi
{
  function __construct ($dbconn, $app_role, $username)
  {
    $search_pi_uid = "";
    // Tablename should be the associated view if the role is a pi.
    if ($app_role == 'pi_user')
    {
      // Find the primary_investigator_uid for this user.
      $result_uid = pg_query ($dbconn, "
       SELECT primary_investigator_uid
        FROM pi_ngscl_role
        WHERE ngscl_role = '$username'");
      if (!$result_uid)
      {
      } else {
        if (pg_num_rows ($result_uid))
        {
          $search_pi_uid = pg_fetch_result ($result_uid, 0, 0);
        }  // if (pg_num_rows ($result_uid))
      }  // if (!$result_uid)
    }  // if ($app_role == 'pi_user')
    $this->search_pi_uid = $search_pi_uid;
  }  // function __construct
}  // class SessionSearchPi
// **************************************************************
// This abstract class defines an input field.
// **************************************************************
abstract class FormField
{
  protected static $input_class = 'inputtext';
  protected static $disabled_class = 'disabledtext';
  protected static $disabled_string = 'disabled="disabled"';
}  // abstract class FormField
// **************************************************************
// This class echoes an input text field and label.
// **************************************************************
class TextField
 extends FormField
{
  function makeInput (
   $initial_value, $label, $label_class, $name, $size, $title,
   $onblur_test='', $disabled_boolean=0)
  {
    $input_value = (isset ($initial_value) ?
     input_ready ($initial_value) : "");
    if (!$disabled_boolean)
    {
      $input_class = parent::$input_class;
      $disabled_string = '';
    } else {
      $input_class = parent::$disabled_class;
      $disabled_string = parent::$disabled_string;
    }  // if (!$disabled_boolean)
    if (strlen (trim ($onblur_test)) < 1)
    {
      $test_text = '';
    } else {
      $test_text =  'onblur="'.$onblur_test.'(this);" ';
    }  // if (strlen (trim ($onblur_test)) < 1)
    echo '<p style="text-align: left; margin: 2px;"><span class="' .
         $label_class . '">' .
         $label . '</span>';
    echo '<input type="text" name="' . $name .
         '" id="' . $name .
         '" size="' . $size .
         '" class="' . $input_class .
         '" value="' . $input_value .
         '" ' . $test_text .
         'title="' . $title .
         '" ' . $disabled_string .
         ' />' .
         '</p>';
  }  // function makeInput (
} // class TextField
// **************************************************************
// This class echoes an input text field.
// **************************************************************
class TableTextField
 extends FormField
{
  function makeInput (
   $initial_value, $tdClass, $name, $size, $title,
   $onblur_test='', $disabled_boolean=0)
  {
    $input_value = (isset ($initial_value) ?
     input_ready ($initial_value) : "");
    if (!$disabled_boolean)
    {
      $input_class = parent::$input_class;
      $disabled_string = '';
    } else {
      $input_class = parent::$disabled_class;
      $disabled_string = parent::$disabled_string;
    }  // if (!$disabled_boolean)
    if (strlen (trim ($onblur_test)) < 1)
    {
      $test_text = '';
    } else {
      $test_text =  'onblur="'.$onblur_test.'(this);" ';
    }  // if (strlen (trim ($onblur_test)) < 1)
    echo '<td class="',
         $tdClass,
         '"><input type="text" name="' . $name .
         '" id="' . $name .
         '" size="' . $size .
         '" class="' . $input_class .
         '" value="' . $input_value .
         '" ' . $test_text .
         'title="' . $title .
         '" ' . $disabled_string .
         ' /></td>';
  }  // function makeInput (
} // class TableTextField
// **************************************************************
// This class echoes an input textarea field and label.
// **************************************************************
class TextAreaField
 extends FormField
{
  function makeInput (
   $initial_value, $label, $label_class, $name, $cols, $rows, $title,
   $disabled_boolean=0)
  {
    $input_class = 'inputseriftext';
    $input_value = (isset ($initial_value) ?
     input_ready ($initial_value) : "");
    if (!$disabled_boolean)
    {
      $input_class = parent::$input_class;
      $disabled_string = '';
    } else {
      $input_class = parent::$disabled_class;
      $disabled_string = parent::$disabled_string;
    }  // if (!$disabled_boolean)
    echo '<p style="text-align: left; margin: 2px;"><span class="',
         $label_class,'">',
         $label,'</span><br />';
    echo '<textarea name="',$name,
         '" id="',$name,
         '" cols="',$cols,
         '" rows="',$rows,
         '" class="',$input_class,
         '" title="',$title,
         '" ',$disabled_string,
         ' >',$input_value,
         '</textarea>',
         '</p>';
  }  // function makeInput (
} // class TextAreaField
// **************************************************************
// This class echoes an input date field with pop-up calendar
// and label.
// **************************************************************
class DateField
 extends FormField
{
  function makeInput (
   $initial_date, $label, $label_class, $name, $title, $disabled_boolean=0)
  {
    $input_date = (isset ($initial_date) ?
     input_ready ($initial_date) : "");
    if (!$disabled_boolean)
    {
      $input_class = parent::$input_class;
      $disabled_string = '';
    } else {
      $input_class = parent::$disabled_class;
      $disabled_string = parent::$disabled_string;
    }  // if (!$disabled_boolean)
    echo '<p style="text-align: left; margin: 2px;"><span class="',
         $label_class,'">',
         $label,'</span>';
    echo '<input type="text" name="',$name,
         '" id="',$name,
         '" size="10" ',
         'class="',$input_class,
         '" value="',$input_date,
         '" title="',$title,
         '" ',$disabled_string,
         ' onclick="fPopCalendar(\'',$name,
         '\')" />',
         '</p>';
  }  // function makeInput (
} // class DateField
// **************************************************************
// This class echoes an check box and label.
// **************************************************************
class CheckBox
 extends FormField
{
  function makeInput (
   $checked_string, $label, $label_class,
   $name, $title, $onclick_function='', $disabled_boolean=0)
  {
    if (!$disabled_boolean)
    {
      $input_class = parent::$input_class;
      $disabled_string = '';
    } else {
      $input_class = parent::$disabled_class;
      $disabled_string = parent::$disabled_string;
    }  // if (!$disabled_boolean)
    if (strlen (trim ($onclick_function)) < 1)
    {
      $onclick_text = '';
    } else {
      $onclick_text =  'onclick="'.$onclick_function.';" ';
    }  // if (strlen (trim ($onclick_function)) < 1)
    echo '<p style="text-align: left; margin: 2px;"><span class="', 
         $label_class,'">', 
         $label,'</span>';
    echo '<input type="checkbox" name="',$name, 
         '" id="',$name,
         '" ',$checked_string, 
         ' ',$onclick_text, 
         'title="',$title, 
         '" class="',$input_class,
         '" ',$disabled_string, 
         ' />', 
         '</p>';
  }  // function makeInput (
} // class CheckBox
// **************************************************************
// This function returns the input string with underscores
// replaced by spaces and with each initial letter of a word
// capitalized.
// **************************************************************
function variable_to_label ($input_string)
{
  $return_string = ucwords (str_replace ('_', ' ', $input_string));
  // Replace A, An, And, Of, Or, and The with a, an, and, of, or, and the.
  $return_string = str_replace (' A ', ' a ', $return_string);
  $return_string = str_replace (' An ', ' an ', $return_string);
  $return_string = str_replace (' And ', ' and ', $return_string);
  $return_string = str_replace (' Of ', ' of ', $return_string);
  $return_string = str_replace (' Or ', ' or ', $return_string);
  $return_string = str_replace (' The ', ' the ', $return_string);
  return ($return_string);
}  // function variable_to_label
// **************************************************************
// This function returns the input string with percent format.
// **************************************************************
function decimal2_format ($input_string)
{
  $formatted_string = $input_string;
  if (strlen (trim ($input_string)) > 0)
  {
    $formatted_string = number_format ($input_string, 2, '.', ',');
  }  // if (strlen (trim ($input_string)) > 0)
  return $formatted_string;
}  // function decimal2_format
// **************************************************************
// This function returns the input string with comma thousands
// separator.
// **************************************************************
function easy_int_format ($input_string)
{
  $formatted_string = $input_string;
  if (strlen (trim ($input_string)) > 0)
  {
    $formatted_string = number_format ($input_string, 0, '.', ',');
  }  // if (strlen (trim ($input_string)) > 0)
  return $formatted_string;
}  // function easy_int_format
// **************************************************************
// This function returns an array of integers which indicate
// where the value of array 1 and array 2 match, keyed
// on the value of array 1.
// **************************************************************
function key_of_value_match ($array1, $array2)
{
  $array_position = array();
  foreach ($array1 as $key1 => $value)
    if (!array_search ($value, $array2))
    {
    } else {
      $array_position[$key1] = array_search ($value, $array2);
    }  // if (!array_search ($value, $array2))
  return $array_position;
}  // function key_of_value_match
// **************************************************************
// This function checks that the input string contains only
// the (English) alpha and numeric characters and underscores.
// **************************************************************
function alphanum_hyphen_underscore_only ($input_text)
{
  if (preg_match('/[^A-Za-z0-9_\-]/', $input_text))
  {
    return FALSE;
  } else {
    return TRUE;
  }  // if (preg_match('/[^A-Za-z0-9_\-]/', $input_text))
}  // function alphanum_hyphen_underscore_only
?>
