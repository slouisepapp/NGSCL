<?php
session_start();
if (isset($_POST['process_form_type']))
{
  if ($_POST['process_form_type'] == 1)
  {
    if (isset($_POST['submit_project_details']))
    {
      // Return to project details page.
      unset ($_SESSION['barcode_format']);
      unset ($_SESSION['entry_method']);
      unset ($_SESSION['submit_project_details']);
      header("location: project_details.php");
      exit;
    }  // if (isset($_POST['submit_project_details']))
  }  // if ($_POST['process_form_type'] == 1)
}  // if (isset($_POST['process_form_type']))
require_once('db_fns.php');
require_once('sample_functions.php');
require_once('constants.php');
$sample_required_array = array(0 => 'sample_name',
                               1 => 'status',
                               2 => 'prep_type',
                               3 => 'species',
                               4 => 'sample_type');
$prep_type_array = array();
$array_error = array();
$barcode_array_error = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_return") &&
      ($thislabel != "form_barcode_type") &&
      ($thislabel != "form_table")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
// ****
// Set variable that indicate the entry method and barcode format.
// Change pages if this is required by the settings.
// ****
// Mode 1
$entry_method = (isset ($_SESSION['entry_method']) ?
 $_SESSION['entry_method'] : "");
$barcode_format = (isset ($_SESSION['barcode_format']) ?
 $_SESSION['barcode_format'] : "");
if ($entry_method == 'fields' && $barcode_format == 'standard')
{
  $standard_checked = 'checked="checked"';
  $custom_checked = '';
  $fields_checked = 'checked="checked"';
  $file_checked = '';
// Mode 2
} elseif ($entry_method == 'fields' && $barcode_format == 'custom') {
  header("location: add_samples_mode2.php");
  exit;
// Mode 3
} elseif ($entry_method == 'file' && $barcode_format == 'standard') {
  header("location: add_samples_mode3.php");
  exit;
// Mode4 
} elseif ($entry_method == 'file' && $barcode_format == 'custom') {
  header("location: add_samples_mode4.php");
  exit;
} else {
  $standard_checked = 'checked="checked"';
  $custom_checked = '';
  $fields_checked = 'checked="checked"';
  $file_checked = '';
}  // if ($entry_method == 'fields' && $barcode_format == 'standard')
// Determine what action brought us here and process accordingly.
if (isset($_POST['process_fields']))
{
  if ($_POST['process_fields'] == 1)
  {
    if (isset($_POST['submit_table']))
    {
      $sample_line_array = array_of_sample_data (
       'standard', $use_sample_bonus_columns, $barcode_separator);
      // Populate sample_name_array.
      foreach ($sample_line_array as $samplerow => $samplevalue)
      {
        $sample_name_array[$samplerow] = $samplevalue['sample_name'];
      }  // foreach ($sample_line_array as $samplerow => $samplevalue)
      // Check sample array for obvious errors.
      $array_error = check_row_samples ($dbconn, $sample_name_array,
       $sample_line_array, $sample_required_array, $project_uid,
       $array_sample_status_values, $max_sample_name_length,
       $max_batch_group_length, $use_sample_bonus_columns);
      // Check that all the barcodes in the array are standard.
      $barcode_array_error = check_standard_barcodes (
       $dbconn, $sample_line_array, $undeclared_barcode);
      $array_error = error_array_merge ($array_error, $barcode_array_error);
      if (count($array_error) < 1)
      {
       // Insert sample line array into sample table.
       $array_error = insert_samples ($dbconn, $project_uid,
        $sample_line_array, $use_sample_bonus_columns, $barcode_separator);
       if (count($array_error) < 1)
       {
        // If the inserts were successful, return to project view page.
        unset ($_SESSION['barcode_format']);
        unset ($_SESSION['barcode_format']);
        unset ($_SESSION['submit_table']);
        header("location: project_details.php");
        exit;
       }  // if (count($array_error < 1)
     }  // if (count($array_error < 1)
    }  // if (isset($_POST['submit_table']))
  }  // if ($_POST['process_fields'] == 1)
}  // if (isset($_POST['process_fields']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo '<title>Add Samples, ',$abbreviated_app_name,'</title>';
?>
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
  <script src="javascript_source.js"
   language="javascript" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!-- Begin
//add a new row to the table
function addRow (prepTypeString, displayPrepTypeString,
 sampleMouseover, use_sample_bonus_columns)
{
  var prepTypeArray = prepTypeString.split(',');
  var displayPrepTypeArray = displayPrepTypeString.split(',');
  // Initialize arrays for select elements.
  statusArray = new Array("Active", "Holding", "Archive", "Trash");
  // add a row to the rows collection and get a reference to the newly added row
  var newRow = document.getElementById("tblGrid").insertRow(-1);
  // sample_name column
  var oCell_1 = newRow.insertCell(-1);
  oElement_1 = inputTextElement ("20", "inputrow", "sample_name[]",
               sampleMouseover);
  oCell_1.appendChild(oElement_1);
  // sample_description column
  var oCell_1a = newRow.insertCell(-1);
  oElement_1a = textareaElement ("15", "2", "inputseriftext", "sample_description[]");
  oCell_1a.appendChild(oElement_1a);
  // status drop-down
  var oCell_2 = newRow.insertCell(-1);
  oElement_2 = selectElement("inputrow", "status[]", 0, statusArray);
  oCell_2.appendChild(oElement_2);
  // prep type drop-down
  var oCell_3 = newRow.insertCell(-1);
  oElement_3 = selectElement("inputrow", "prep_type[]", 0, prepTypeArray, displayPrepTypeArray);
  oCell_3.appendChild(oElement_3);
  // barcode number column
  var oCell_4 = newRow.insertCell(-1);
  oElement_4 = inputTextElement ("8", "inputrow", "barcode_number[]",
   "Positive integers only. No number allowed for TBD.");
  oElement_4.setAttribute("onblur", "testPosIntField(this);");
  oCell_4.appendChild(oElement_4);
  // species
  var oCell_5 = newRow.insertCell(-1);
  oElement_5 = inputTextElement ("12", "inputrow", "species[]",
  "Only alphanumeric characters, space, dot, and underscore allowed.");
  oCell_5.appendChild(oElement_5);
  // sample type
  var oCell_6 = newRow.insertCell(-1);
  oElement_6 = inputTextElement ("15", "inputrow", "sample_type[]");
  oCell_6.appendChild(oElement_6);
  // batch group
  var oCell_7 = newRow.insertCell(-1);
  oElement_7 = inputTextElement ("15", "inputrow", "batch_group[]");
  oCell_7.appendChild(oElement_7);
  if (use_sample_bonus_columns)
  {
    // concentration
    var oCell_b1 = newRow.insertCell(-1);
    oElement_b1 = inputTextElement ("8", "inputrow", "concentration[]",
     "Positive numbers only.");
    oElement_b1.setAttribute("onblur", "testPosRealField(this);");
    oCell_b1.appendChild(oElement_b1);
    // volume
    var oCell_b2 = newRow.insertCell(-1);
    oElement_b2 = inputTextElement ("8", "inputrow", "volume[]",
     "Positive numbers only.");
    oElement_b2.setAttribute("onblur", "testPosRealField(this);");
    oCell_b2.appendChild(oElement_b2);
    // rna_integrity_number
    var oCell_b2 = newRow.insertCell(-1);
    oElement_b2 = inputTextElement ("8", "inputrow", "rna_integrity_number[]",
     "Positive numbers only.");
    oElement_b2.setAttribute("onblur", "testPosRealField(this);");
    oCell_b2.appendChild(oElement_b2);
  }
  // comments 
  var oCell_8 = newRow.insertCell(-1);
  oElement_8 = textareaElement ("20", "2", "inputseriftext", "comments[]");
  oCell_8.appendChild(oElement_8);
  // copy button
  var oCell_9 = newRow.insertCell(-1);
  oElement_9 = copyRowButtonElementParm ("buttonrow", "Copy",
   "Copy row and append to end of table.",
   sampleMouseover, prepTypeString, displayPrepTypeString,
   use_sample_bonus_columns);
  oCell_9.appendChild(oElement_9);
  // delete button
  var oCell_10 = newRow.insertCell(-1);
  oElement_10 = deleteRowButtonElement("buttonrow", "Delete", "Delete row.");
  oCell_10.appendChild(oElement_10);
}  // function addRow
// Deletes the specified row from the table.
function removeRow(myindex)
{
  document.getElementById('tblGrid').deleteRow(myindex);
}  // function removeRow
// Copies the specified row and appends it to the table.
function copyRow(
 input_index,sampleMouseover,prepTypeString,displayPrepTypeString,
 use_sample_bonus_columns)
{
  var selectCount = 0;
  var inputCount = 0;
  var textAreaCount = 0;
  // Split input strings for prep type into arrays.
  var prepTypeArray = prepTypeString.split(',');
  var displayPrepTypeArray = displayPrepTypeString.split(',');
  var tab = document.getElementById("tblGrid");
  var old_row = tab.getElementsByTagName("tr").item(input_index);
  // Initialize arrays for select elements.
  statusArray = new Array("Active", "Holding", "Archive", "Trash");
  // add a row to the rows collection and get a reference to the newly added row
  var newRow = document.getElementById("tblGrid").insertRow(-1);
  // sample_name column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_1 = newRow.insertCell(-1);
  oElement_1 = inputTextElement ("20", "inputrow", "sample_name[]", 
   sampleMouseover, input_text);
  oCell_1.appendChild(oElement_1);
  // sample_description column
  var input_text = old_row.getElementsByTagName("textarea")[textAreaCount].value;
  textAreaCount++;
  var oCell_1a = newRow.insertCell(-1);
  oElement_1a = textareaElement ("15", "2", "inputseriftext", "sample_description[]", input_text);
  oCell_1a.appendChild(oElement_1a);
  // status drop-down
  var status_index = old_row.getElementsByTagName("select")[selectCount].selectedIndex;
  selectCount++;
  var oCell_2 = newRow.insertCell(-1);
  oElement_2 = selectElement("inputrow", "status[]", status_index, statusArray);
  oCell_2.appendChild(oElement_2);
  // prep type drop-down
  var prep_type_index = old_row.getElementsByTagName("select")[selectCount].selectedIndex;
  selectCount++;
  var oCell_3 = newRow.insertCell(-1);
  oElement_3 = selectElement("inputrow", "prep_type[]", prep_type_index, prepTypeArray, displayPrepTypeArray);
  oCell_3.appendChild(oElement_3);
  // barcode number column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_4 = newRow.insertCell(-1);
  oElement_4 = inputTextElement ("8", "inputrow", "barcode_number[]",
               "Positive integers only. No number allowed for TBD.",
               input_text);
  oElement_4.setAttribute("onblur", "testPosIntField(this);");
  oCell_4.appendChild(oElement_4);
  // species column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_5 = newRow.insertCell(-1);
  oElement_5 = inputTextElement ("12", "inputrow", "species[]",
               "Only alphanumeric characters, space, dot, and underscore allowed.", input_text);
  oCell_5.appendChild(oElement_5);
  // sample_type column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_6 = newRow.insertCell(-1);
  oElement_6 = inputTextElement (
                "15", "inputrow", "sample_type[]", "", input_text);
  oCell_6.appendChild(oElement_6);
  // batch_group column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_7 = newRow.insertCell(-1);
  oElement_7 = inputTextElement (
                "15", "inputrow", "batch_group[]", "", input_text);
  oCell_7.appendChild(oElement_7);
  if (use_sample_bonus_columns)
  {
    // concentration
    var input_text = old_row.getElementsByTagName("input")[inputCount].value;
    inputCount++;
    var oCell_b1 = newRow.insertCell(-1);
    oElement_b1 = inputTextElement ("8", "inputrow", "concentration[]",
                 "Positive nubers only.",
                 input_text);
    oElement_b1.setAttribute("onblur", "testPosRealField(this);");
    oCell_b1.appendChild(oElement_b1);
    // volume
    var input_text = old_row.getElementsByTagName("input")[inputCount].value;
    inputCount++;
    var oCell_b2 = newRow.insertCell(-1);
    oElement_b2 = inputTextElement ("8", "inputrow", "volume[]",
                 "Positive nubers only.",
                 input_text);
    oElement_b2.setAttribute("onblur", "testPosRealField(this);");
    oCell_b2.appendChild(oElement_b2);
    // rna_integrity_number
    var input_text = old_row.getElementsByTagName("input")[inputCount].value;
    inputCount++;
    var oCell_b3 = newRow.insertCell(-1);
    oElement_b3 = inputTextElement ("8", "inputrow", "[rna_integrity_number]",
                 "Positive nubers only.",
                 input_text);
    oElement_b3.setAttribute("onblur", "testPosRealField(this);");
    oCell_b3.appendChild(oElement_b3);
  }
  // comments
  var input_text = old_row.getElementsByTagName("textarea")[textAreaCount].value;
  textAreaCount++;
  var oCell_8 = newRow.insertCell(-1);
  oElement_8 = textareaElement("20", "2", "inputseriftext", "comments[]", input_text);
  oCell_8.appendChild(oElement_8);
  // copy button
  var oCell_9 = newRow.insertCell(-1);
  oElement_9 = copyRowButtonElementParm ("buttonrow", "Copy",
   "Copy row and append to end of table.",
   sampleMouseover, prepTypeString, displayPrepTypeString,
   use_sample_bonus_columns);
  oCell_9.appendChild(oElement_9);
  // delete button
  var oCell_10 = newRow.insertCell(-1);
  oElement_10 = deleteRowButtonElement("buttonrow", "Delete", "Delete row.");
  oCell_10.appendChild(oElement_10);
}  // function copyRow
// End -->
</script>
<?php
  readfile("text_styles.css");
?>
<script src="library/sorttable.js" 
  language="javascript" 
    type="text/javascript"></script>
</head>
<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
echo '<h1 align="center"><span class="titletext">Add Samples - ',
     $app_name,'</span></h1>';
echo '<!-- end #header --></div>';
  $project_name = "";
  $run_type = "";
  if ($project_uid > 0)
  {
    $result_puid = pg_query ($dbconn, "
     SELECT project_name,
            run_type
       FROM project,
            ref_run_type
      WHERE project_uid = $project_uid AND
            project.ref_run_type_uid = ref_run_type.ref_run_type_uid");
    if (!$result_puid)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $project_name = pg_fetch_result ($result_puid, 0, 0);
      $run_type = pg_fetch_result ($result_puid, 0, 1);
    }  // if (!$result_puid)
  }  // if ($project_uid > 0)
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  echo '<h3 class="grayed_out">Project: ',$project_name,'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$run_type,'</h3>';
  $prep_type_array = create_prep_type_array ($dbconn, $undeclared_barcode);
  // ****
  // Create a string that lists the prep types.
  // ****
  $prep_type_list = "";
  foreach ($prep_type_array as $array_num => $prep_type)
  {
    $prep_type_list .= ',' . $prep_type['prep_type'];
  }  // foreach ($prep_type_array as $array_num => $prep_type)
  // Remove the initial comma from the string.
  $prep_type_list = substr ($prep_type_list, 1);
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" name="form_return">
<input type="hidden" name="process_form_type" value="1" />
<input type="submit" value="Project Details" name="submit_project_details"
 title="Return to Project Details page." class="buttontext" />
<?php
  echo '<input type="button" value="See Barcode List" ',
       'title="Shows all the reference barcodes ',
       'and associated barcode indexes." ',
       'onclick="javascript:barcodeReferenceWindow()" />';
  echo '<table class="tableNoBorder"><tr>';
  echo '<td class="tdPadding">';
  echo '<h2>Entry Method</h2>';
  echo '<input type="radio" name="entry_method" value="fields" ',
       ' onclick="this.form.submit();" ',
       $fields_checked,
       ' />Enter Fields<br />';
  echo '<input type="radio" name="entry_method" value="file" ',
       ' onclick="this.form.submit();" ',
       $file_checked,
       ' />Upload File<br />';
  echo '</td><td class="tdPadding">';
  echo '<h2>Barcode Format</h2>';
  echo '<input type="radio" name="barcode_format" value="standard" ',
       ' onclick="this.form.submit();" ',
       $standard_checked,
       ' />Standard</br>';
  echo '<input type="radio" name="barcode_format" value="custom" ',
       ' onclick="this.form.submit();" ',
       $custom_checked,
       ' />Custom</form>';
  echo '</td></tr></table>';
  if (count($array_error) >= 1)
  {
    $error_exists = 0;
    foreach ($array_error as $error)
    {
      if (strlen(trim($error)) > 0)
      {
        $error_exists = 1;
        echo '<span class="errortext">'.$error.'</span><br />';
      }  // if (strlen(trim($error)) > 0)
    }  // foreach ($array_error as $error)
    if ($error_exists > 0) 
    {
      echo '<span class="errortext">Correct and resubmit.</span><br />';
    }  // if ($error_exists > 0) 
  }  // if (count($array_error) >= 1)
  echo '<form method="post" style="width:700px;" ',
       'action="',$_SERVER['PHP_SELF'],'" name="form_table">';
  echo '<p class="smallrequiredtext"><b><i>* Required Fields',
       '</i></b></p>';
  echo '<!--[if IE]>';
  echo '<div style="width:100%;">';
  echo '<![endif]-->';
  echo '<table id="tblGrid" border="1" align="left">';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Sample Name</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       'Sample Description</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Status</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Prep Type</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Barcode Number</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Species</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       '*Sample Type</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       'Batch Group</th>';
  if ($use_sample_bonus_columns)
  {
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center;" >',
         'Concentration</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center;" >',
         'Volume</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center;" >',
         'RIN</th>';
  }  // if ($use_sample_bonus_columns)
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       'Comments</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       'Copy Button</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >',
       'Delete Button</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // If we just submitted the table, write all the posted variables
  // to the table.
  if (isset($_POST['submit_table']))
  {
    foreach ($sample_line_array as $samplerow => $samplevalue)
    {
      echo '<tr>';
      echo '<td><input name="sample_name[]" type="text" ',
           'title="',$sample_mouseover,'" ',
           'class="inputrow" size="20" value="',
           $samplevalue['sample_name'],'" /></td>';
      echo '<td><textarea name="sample_description[]" cols="15" rows="2" ',
           'class="inputseriftext">',
           $samplevalue['sample_description'],
           '</textarea></td>';
      // Determine select indicator for each status option.
      $active_selected = '';
      $holding_selected = '';
      $archive_selected = '';
      $trash_selected = '';
      if ($samplevalue['status'] == 'Active')
      {
        $active_selected = 'selected="selected"';
      } elseif ($samplevalue['status'] == 'Holding') {
        $holding_selected = 'selected="selected"';
      } elseif ($samplevalue['status'] == 'Archive') {
        $archive_selected = 'selected="selected"';
      } elseif ($samplevalue['status'] == 'Trash') {
        $trash_selected = 'selected="selected"';
      }  // if ($samplevalue['status'] == 'Active')
      echo '<td><select name="status[]" class="inputrow">';
      echo '<option id="Active" value="Active" class="inputrow" ',
           $active_selected,'>Active</option>';
      echo '<option id="Holding" value="Holding" class="inputrow" ',
           $holding_selected,'>Holding</option>';
      echo '<option id="Archive" value="Archive" class="inputrow" ',
           $archive_selected,'>Archive</option>';
      echo '<option id="Trash" value="Trash" class="inputrow" ',
           $trash_selected,'>Trash</option>';
      echo '</select></td>';
      $prep_type_select = '<select name="prep_type[]" class="inputrow">';
      foreach ($prep_type_array as $array_num => $prep_type)
      {
        if (strtoupper($samplevalue['prep_type']) ==
            strtoupper($prep_type['prep_type']))
        {
          $prep_type_selected = 'selected="selected"';
        } else {
          $prep_type_selected = '';
        }  // if (strtoupper($samplevalue['prep_type']) ==
        $prep_type_select .= '<option id="' .
                           $prep_type['prep_type'] .
                           '" value = "' .
                           $prep_type['prep_type'] .
                           '" class="inputrow" '.
                           $prep_type_selected .
                           '>' .
                           $prep_type['prep_type'] .
                           '</option>';
      }  // foreach ($prep_type_array as $array_num => $prep_type)
      echo '<td>',$prep_type_select,'</td>';
      echo '<td width="5%"><input name="barcode_number[]" type="text" ',
           'title="Positive integers only. No number allowed for ',
           $undeclared_barcode,
           '." onblur="testPosIntField(this);" ',
           'class="inputrow" size="8" value="',
           $samplevalue['barcode_number'],
           '" /></td>';
      echo '<td><input name="species[]" type="text" ',
           'title="Only alphanumeric characters, ',
           'space, dot, and underscore allowed." ',
           'class="inputrow" size="12" value="',
           $samplevalue['species'],'" /></td>';
      echo '<td><input name="sample_type[]" type="text" ',
           'class="inputrow" size="15" value="',
           $samplevalue['sample_type'],'" /></td>';
      echo '<td><input name="batch_group[]" type="text" ',
           'class="inputrow" size="15" value="',
           $samplevalue['batch_group'],'" /></td>';
      if ($use_sample_bonus_columns)
      {
        echo '<td width="5%"><input name="concentration[]" type="text" ',
             'title="Positive numbers only." ',
             'onblur="testPosIntField(this);" ',
             'class="inputrow" size="8" value="',
             $samplevalue['concentration'],
             '" /></td>';
        echo '<td width="5%"><input name="volume[]" type="text" ',
             'title="Positive numbers only." ',
             'onblur="testPosIntField(this);" ',
             'class="inputrow" size="8" value="',
             $samplevalue['volume'],
             '" /></td>';
        echo '<td width="5%"><input name="rna_integrity_number[]" type="text" ',
             'title="Positive numbers only." ',
             'onblur="testPosIntField(this);" ',
             'class="inputrow" size="8" value="',
             $samplevalue['rna_integrity_number'],
             '" /></td>';
      }  // if ($use_sample_bonus_columns)
      echo '<td><textarea name="comments[]" cols="20" rows="2" ',
           'class="inputseriftext">',
           $samplevalue['comments'],
           '</textarea></td>';
      echo '<td>',
           '<input onclick="copyRow(this.parentNode.parentNode.rowIndex,',
           '\'',$sample_mouseover,'\',',
           '\'',$prep_type_list,'\',',
           '\'',$prep_type_list,'\',',
           '\'',$use_sample_bonus_columns,'\');" ',
           'type="button" class="buttonrow" value="Copy" ',
           'title="Copy row and append to end of table." /></td>';
      echo '<td>',
           '<input onclick="removeRow(this.parentNode.parentNode.rowIndex);"',
           ' type="button" class="buttonrow" value="Delete" ',
           'title="Delete row." /></td>';
      echo '</tr>';
    }  // foreach ($sample_line_array as $samplerow => $samplevalue)
  } else {
    // If this is the first time form is accessed.
    echo '<tr>';
    echo '<td><input name="sample_name[]" type="text" ',
         'title="',$sample_mouseover,'" ',
         'class="inputrow" size="20" /></td>';
    echo '<td><textarea name="sample_description[]" cols="15" rows="2" ',
         'class="inputseriftext"></textarea></td>';
    echo '<td><select name="status[]" class="inputrow">';
    echo '<option id="Active" value="Active" class="inputrow" ',
         'selected="selected">Active</option>';
    echo '<option id="Holding" value="Holding" class="inputrow" ',
         '>Holding</option>';
    echo '<option id="Archive" value="Archive" class="inputrow" ',
         '>Archive</option>';
    echo '<option id="Trash" value="Trash" class="inputrow" ',
         '>Trash</option>';
    echo '</select></td>';
    $prep_type_select = '<select name="prep_type[]" class="inputrow">';
    foreach ($prep_type_array as $array_num => $prep_type)
    {
      if (strtoupper($prep_type['prep_type']) ==
          strtoupper ($undeclared_barcode))
      {
        $prep_type_selected = 'selected="selected"';
      } else {
        $prep_type_selected = '';
      }  // if (strtoupper($prep_type['prep_type']) ==..
      $prep_type_select .= '<option id="' .
                         $prep_type['prep_type'] .
                         '" value = "' .
                         $prep_type['prep_type'] .
                         '" class="inputrow" '.
                         $prep_type_selected .
                         '>' .
                         $prep_type['prep_type'] .
                         '</option>';
    }  // foreach ($prep_type_array as $array_num => $prep_type)
    $prep_type_select .= '</select>';
    echo '<td>',$prep_type_select,'</td>';
    echo '<td width="5%"><input name="barcode_number[]" type="text" ',
         'title="Positive integers only. No number allowed for ',
         $undeclared_barcode,
         '." onblur="testPosIntField(this);" ',
         'class="inputrow" size="8" ',
         ' /></td>';
    echo '<td><input name="species[]" type="text" ',
         'title="Only alphanumeric characters, ',
         'space, dot, and underscore allowed." ',
         'class="inputrow" size="12"/></td>';
    echo '<td><input name="sample_type[]" type="text" ',
         'class="inputrow" size="15"/></td>';
    echo '<td><input name="batch_group[]" type="text" ',
         'class="inputrow" size="15"/></td>';
    if ($use_sample_bonus_columns)
    {
      echo '<td width="5%"><input name="concentration[]" type="text" ',
           'title="Positive numbers only." ',
           'onblur="testPosIntField(this);" ',
           'class="inputrow" size="8" /></td>';
      echo '<td width="5%"><input name="volume[]" type="text" ',
           'title="Positive numbers only." ',
           'onblur="testPosIntField(this);" ',
           'class="inputrow" size="8" /></td>';
      echo '<td width="5%"><input name="rna_integrity_number[]" type="text" ',
           'title="Positive numbers only." ',
           'onblur="testPosIntField(this);" ',
           'class="inputrow" size="8" /></td>';
    }  // if ($use_sample_bonus_columns)
    echo '<td><textarea name="comments[]" cols="20" rows="2" ',
         'class="inputseriftext"></textarea></td>';
    echo '<td>',
         '<input onclick="copyRow(this.parentNode.parentNode.rowIndex,',
         '\'',$sample_mouseover,'\',',
         '\'',$prep_type_list,'\',',
         '\'',$prep_type_list,'\',',
         '\'',$use_sample_bonus_columns,'\');" ',
         'type="button" class="buttonrow" value="Copy" ',
         'title="Copy row and append to end of table." /></td>';
    echo '<td>',
         '<input onclick="removeRow(this.parentNode.parentNode.rowIndex);" ',
         'type="button" class="buttonrow" value="Delete" ',
         'title="Delete row." /></td>';
    echo '</tr>';
  }  // if (isset($_POST['submit_table']))
  echo '</tbody></table>';
  echo '<!--[if IE]>';
  echo '</div>';
  echo '<![endif]-->';
  echo '<table class="tableNoBorder" style="text-align: left;">';
  echo '<tbody><tr>';
  echo '<td class="tdNoBorder"><input type="hidden" name="process_fields" ',
       'value="1"/>';
  echo '<input onclick="addRow(',
         '\'',$prep_type_list,'\',',
         '\'',$prep_type_list,'\',',
         '\'',$sample_mouseover,'\',',
         '\'',$use_sample_bonus_columns,'\');" ',
       'type="button" value="Add Row" ',
       ' title="Add an empty sample row." class="buttontext" /></td>';
  echo '<td><input type="submit" name="submit_table" value="Submit" ',
       'onclick="return confirm(\'Are you sure you want to add these ',
       'samples to the project?\');" ',
       'title="Submit table." class="buttontext" /></td>';
  echo '</tr></tbody></table>';
  echo '</form>';
?>
<hr />
<!-- Display a table of the samples for this project.  -->
<table id="sample_table" border="1" class="sortable" >
<thead>
  <tr>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample Description</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Status</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Barcode</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Barcode Index</th>
    <th class="sorttable_alpha" scope="col" width="200"
    style="text-align:center">
    Species</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Type</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Batch Group</th>
    <?php
    if ($use_sample_bonus_columns)
    {
      echo '<th class="sorttable_numeric" scope="col" width="200"',
            'style="text-align:center">',
            'Concentration</th>';
      echo '<th class="sorttable_numeric" scope="col" width="200"',
            'style="text-align:center">',
            'Volume</th>';
      echo '<th class="sorttable_numeric" scope="col" width="200"',
            'style="text-align:center">',
            'RIN</th>';
    }  // if ($use_sample_bonus_columns)
    ?>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
if ($project_uid > 0)
{
  $result = pg_query($dbconn,"
   SELECT sample_uid,
          sample_name,
          sample_description,
          sa.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          concentration,
          volume,
          rna_integrity_number,
          sa.comments
        FROM sample sa,
          project pr
       WHERE sa.project_uid = $project_uid AND
          sa.project_uid = pr.project_uid
       ORDER BY sample_name");
  if (!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
  } else {
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row_sample = pg_fetch_assoc ($result);
      $sample_uid = $row_sample['sample_uid'];
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="sampleWindow(\'',
           $row_sample['sample_uid'],'\');" ',
           'title="Display information on sample ',
           $row_sample['sample_name'],'.">',
           td_ready($row_sample['sample_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 100px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row_sample['sample_description']),
           '</font></div></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['status']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode_index']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['species']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['sample_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['batch_group']),'</td>';
      if ($use_sample_bonus_columns)
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['concentration']),'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['volume']),'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row_sample['rna_integrity_number']),'</td>';
      }  // if ($use_sample_bonus_columns)
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 150px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row_sample['comments']),
           '</font></div></td>';
      echo '</tr>';
    }  // for ($i=0; $i < pg_num_rows($result); $i++)
  }  // if (!$result)
}  // if ($project_uid > 0)
echo '</tbody>';
echo '</table>';
?>
  <!-- end #mainContent -->
  </div>
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
