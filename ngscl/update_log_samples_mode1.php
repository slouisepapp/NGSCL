<?php
session_start();
require_once('db_fns.php');
require_once('project_functions.php');
require_once('constants.php');
$sample_name_array = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_return") &&
      ($thislabel != "form_barcode_type") &&
      ($thislabel != "form_table")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
// ****
// Set variable that indicate the entry method.
// Change pages if this is required by the settings.
// ****
// Mode 1
$entry_method = (isset ($_SESSION['entry_method']) ?
 $_SESSION['entry_method'] : "fields");
if ($entry_method == 'fields')
{
  $fields_checked = 'checked="checked"';
  $file_checked = '';
// Mode 2
} elseif ($entry_method == 'file') {
  header("location: update_log_samples_mode2.php");
  exit;
}  // if ($entry_method == 'fields')
$sample_required_array = array(0 => 'sample_name', 1=>'species');
$array_error = array();
$dbconn = database_connect();
$project = (isset ($_SESSION['project']) ?
 $_SESSION['project'] : "");
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
$project_log_uid = (isset ($_SESSION['project_log_uid']) ?
 $_SESSION['project_log_uid'] : 0);
// Determine what action brought us here and process accordingly.
if (isset($_POST['process_fields']))
{
  if ($_POST['process_fields'] == 1)
  {
    if (isset($_POST['submit_table']))
    {
      $my_log_sample_array = array_of_log_sample_data ();
      unset($_SESSION['sample_name']);
      // Populate sample_name_array.
      foreach ($my_log_sample_array as $samplerow => $samplevalue)
      {
        $sample_name_array[$samplerow] = $samplevalue['sample_name'];
      }  // foreach ($my_log_sample_array as $samplerow => $samplevalue)
      // Check sample array for obvious errors.
      $array_error = check_log_samples (
       $dbconn, $_SESSION['project_log_run_lane'],
       $project_log_uid, $sample_name_array,
       $my_log_sample_array, $sample_required_array,
       $max_batch_group_length, $max_sample_name_length);
      if (count($array_error) < 1)
      {
       // Insert sample line array into project log sample table.
       $array_error = update_log_samples (
        $dbconn, $_SESSION['project_log_run_lane'],
        $project_log_uid, $my_log_sample_array);
       if (count($array_error) < 1)
       {
         // If the inserts were successful, return to project log page.
         unset ($_SESSION['entry_method']);
         unset ($_SESSION['submit_table']);
         header("location: project_log.php");
         exit;
       } else {
         foreach ($array_error as $error)
         {
           if (strlen(trim($error)) > 0)
           {
             $error_exists = 1;
             echo '<span class="errortext">'.$error.'</span><br />';
           }  // if (strlen(trim($error)) > 0)
         }  // foreach ($array_error as $error)
       }  // if (count($array_error < 1)
     }  // if (count($array_error < 1)
    } elseif (isset($_POST['submit_reset'])) {
      $my_log_run_lane = new ProjectLogRunLane (
       $dbconn, $_SESSION['project_log_run_lane'], $project_log_uid);
      $my_log_sample_array = $my_log_run_lane->getArrayCopy();
    } elseif (isset($_POST['submit_return'])) {
      unset ($_SESSION['entry_method']);
      unset ($_SESSION['submit_table']);
      header("location: project_log.php");
      exit;
    }  // if (isset($_POST['submit_table']))
  } else {
    $my_log_run_lane = new ProjectLogRunLane (
     $dbconn, $_SESSION['project_log_run_lane'], $project_log_uid);
    $my_log_sample_array = $my_log_run_lane->getArrayCopy();
  }  // if ($_POST['process_fields'] == 1)
} else {
  $my_log_run_lane = new ProjectLogRunLane (
   $dbconn, $_SESSION['project_log_run_lane'], $project_log_uid);
  $my_log_sample_array = $my_log_run_lane->getArrayCopy();
}  // if (isset($_POST['process_fields']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo '<title>Log Samples, ',$abbreviated_app_name,'</title>';
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
function addRow (sampleMouseover)
{
  // add a row to the rows collection and get a reference to the newly added row
  var newRow = document.getElementById("tblGrid").insertRow(-1);
  // batch group column
  var oCell_1 = newRow.insertCell(-1);
  oElement_1 = inputTextElement ("15", "inputrow", "batch_group[]", "");
  oCell_1.appendChild(oElement_1);
  // sample name column
  var oCell_2 = newRow.insertCell(-1);
  oElement_2 = inputTextElement ("20", "inputrow", "sample_name[]",
               sampleMouseover);
  oCell_2.appendChild(oElement_2);
  // sample description column
  var oCell_3 = newRow.insertCell(-1);
  oElement_3 = inputTextElement ("50", "inputrow", "sample_description[]",
               "");
  oCell_3.appendChild(oElement_3);
  // species column
  var oCell_4 = newRow.insertCell(-1);
  oElement_4 = inputTextElement ("20", "inputrow", "species[]",
  "Only alphanumeric characters, space, dot, and underscore allowed.");
  oCell_4.appendChild(oElement_4);
  // copy button
  var oCell_5 = newRow.insertCell(-1);
  oElement_5 = copyRowButtonElementParm("buttonrow", "Copy", "Copy row and append to end of lane.", sampleMouseover);
  oCell_5.appendChild(oElement_5);
  // delete button
  var oCell_6 = newRow.insertCell(-1);
  oElement_6 = deleteRowButtonElement("buttonrow", "Delete", "Delete row.");
  oCell_6.appendChild(oElement_6);
}  // function addRow
// Deletes the specified row from the table.
function removeRow(myindex)
{
  document.getElementById('tblGrid').deleteRow(myindex);
}  // function removeRow
// Copies the specified row and appends it to the table.
function copyRow (input_index, sampleMouseover)
{
  var inputCount = 0;
  var tab = document.getElementById("tblGrid");
  var old_row = tab.getElementsByTagName("tr").item(input_index);
  // Initialize arrays for select elements.
  var newRow = document.getElementById("tblGrid").insertRow(-1);
  // batch group column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_1 = newRow.insertCell(-1);
  oElement_1 = inputTextElement ("15", "inputrow", "batch_group[]",
   "", input_text);
  oCell_1.appendChild(oElement_1);
  // sample name column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_2 = newRow.insertCell(-1);
  oElement_2 = inputTextElement ("20", "inputrow", "sample_name[]", 
   sampleMouseover, input_text);
  oCell_2.appendChild(oElement_2);
  // sample description column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_3 = newRow.insertCell(-1);
  oElement_3 = inputTextElement ("50", "inputrow", "sample_description[]", 
   "", input_text);
  oCell_3.appendChild(oElement_3);
  // species column
  var input_text = old_row.getElementsByTagName("input")[inputCount].value;
  inputCount++;
  var oCell_4 = newRow.insertCell(-1);
  oElement_4 = inputTextElement ("20", "inputrow",
   "species[]",
   "Only alphanumeric characters, space, dot, and underscore allowed.",
   input_text);
  oCell_4.appendChild(oElement_4);
  // copy button
  var oCell_5 = newRow.insertCell(-1);
  oElement_5 = copyRowButtonElementParm("buttonrow", "Copy", "Copy row and append to end of lane.", sampleMouseover);
  oCell_5.appendChild(oElement_5);
  // delete button
  var oCell_6 = newRow.insertCell(-1);
  oElement_6 = deleteRowButtonElement("buttonrow", "Delete", "Delete row.");
  oCell_6.appendChild(oElement_6);
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
echo '<h1 align="center"><span class="titletext">Update Project Log Samples - ',
     $app_name,'</span></h1>';
echo '<!-- end #header --></div>';
  $project_name = "";
  if ($project_uid > 0)
  {
    $result_puid = pg_query ($dbconn, "
     SELECT project_name
       FROM $project
      WHERE project_uid = $project_uid");
    if (!$result_puid)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $project_name = pg_fetch_result ($result_puid, 0, 0);
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
  echo '<form method="post" action="',
       $_SERVER['PHP_SELF'],'" name="form_return">';
  echo '<input type="hidden" name="process_form_type" ',
       'value="1" />';
  echo '<input type="submit" value="Save" ',
       'name="submit_table" ',
       'title="Submit project log samples." ',
       'class="buttontext" />';
  echo '<input type="submit" value="Reset" ',
       'name="submit_reset" ',
       'title="Reset project log samples." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_return" ',
       'value="Quit" ',
       'title="Return to Project Log page without saving." ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
       'class="buttontext" />';
  echo '<table class="tableNoBorder"><tr>';
  echo '<td class="tdPadding">';
  echo '<h2>Entry Method</h2>';
  echo '<input type="radio" name="entry_method" value="fields" ',
       ' onclick="this.form.submit();" ',
       $fields_checked,
       ' />Enter Fields (add/update samples)<br />';
  echo '<input type="radio" name="entry_method" value="file" ',
       ' onclick="this.form.submit();" ',
       $file_checked,
       ' />Upload File (add samples)<br />';
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
  echo '<p class="displaytext"><b>',$sample_format_msg,'</b></p>';
  echo '<p class="smallrequiredtext"><b><i>* Required Fields',
       '</i></b></p>';
  echo '<!--[if IE]>';
  echo '<div style="width:100%;">';
  echo '<![endif]-->';
  echo '<table id="tblGrid" border="1" align="left">';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo 'Batch Group</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo '*Sample Name</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo 'Sample Description</th>';
  echo '<th class="thSmallerRedBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo 'Species</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo 'Copy Button</th>';
  echo '<th class="thSmallerBlueBorder" scope="col" ',
       'style="text-align:center;" >';
  echo 'Delete Button</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  if (count ($my_log_sample_array) > 0)
  {
    // Write all the log samples to the table.
    foreach ($my_log_sample_array as $samplerow => $samplevalue)
    {
      echo '<tr>';
      echo '<td><input name="batch_group[]" type="text" ',
           'title="Batch group for this sample." ',
           'class="inputrow" size="15" value="',
           input_ready_quotes ($samplevalue['batch_group']),'" /></td>';
      echo '<td><input name="sample_name[]" type="text" ',
           'title="',$sample_mouseover,'" ',
           'class="inputrow" size="20" value="',
           input_ready_quotes ($samplevalue['sample_name']),'" /></td>';
      echo '<td><input name="sample_description[]" type="text" ',
           'title="" ',
           'class="inputrow" size="50" value="',
           input_ready_quotes ($samplevalue['sample_description']),
           '" /></td>';
      echo '<td><input name="species[]" type="text" ',
           'title="Only alphanumeric characters, ',
           'space, dot, and underscore allowed." ',
           'class="inputrow" size="20" value="',
           input_ready_quotes ($samplevalue['species']),
           '" /></td>';
      echo '<td>',
           '<input onclick="copyRow(this.parentNode.parentNode.rowIndex,\'',
           $sample_mouseover,'\');" ',
           'type="button" class="buttonrow" value="Copy" ',
           'title="Copy row and append to end of lane." /></td>';
      echo '<td>',
           '<input onclick="removeRow(this.parentNode.parentNode.rowIndex);"',
           ' type="button" class="buttonrow" value="Delete" ',
           'title="Delete row." /></td>';
      echo '</tr>';
    }  // foreach ($my_log_sample_array as $samplerow => $samplevalue)
  } else {
    // If the log has no samples as yet, add a blank row.
    echo '<tr>';
    echo '<td><input name="batch_group[]" type="text" ',
         'title="Batch group for this sample." ',
         'class="inputrow" size="15" /></td>';
    echo '<td><input name="sample_name[]" type="text" ',
         'title="',$sample_mouseover,'" ',
         'class="inputrow" size="20" /></td>';
    echo '<td><input name="sample_description[]" type="text" ',
         'title="" ',
         'class="inputrow" size="50" /></td>';
    echo '<td><input name="species[]" type="text" ',
         'title="Only alphanumeric characters, ',
         'space, dot, and underscore allowed." ',
         'class="inputrow" size="20" /></td>';
    echo '<td>',
         '<input onclick="copyRow(this.parentNode.parentNode.rowIndex,\'',
         $sample_mouseover,'\');" ',
         'type="button" class="buttonrow" value="Copy" ',
         'title="Copy row and append to end of lane." /></td>';
    echo '<td>',
         '<input onclick="removeRow(this.parentNode.parentNode.rowIndex);"',
         ' type="button" class="buttonrow" value="Delete" ',
         'title="Delete row." /></td>';
    echo '</tr>';
  }  // if (count ($my_log_sample_array) > 0)
  echo '</tbody></table>';
  echo '<!--[if IE]>';
  echo '</div>';
  echo '<![endif]-->';
  echo '<table class="tableNoBorder" style="text-align: left;">';
  echo '<tbody><tr>';
  echo '<td class="tdNoBorder"><input type="hidden" name="process_fields" ',
       'value="1"/>';
  echo '<input onclick="addRow(\'',$sample_mouseover,'\');" ',
       'type="button" value="Add Row" ',
       ' title="Add an empty log sample row." class="buttontext" /></td>';
  echo '</tr></tbody></table>';
  echo '</form>';
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
