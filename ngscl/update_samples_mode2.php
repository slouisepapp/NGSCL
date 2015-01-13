<?php
session_start();
$sample_required_array = array(0 => 'sample_name',
                               1 => 'status',
                               2 => 'barcode',
                               3 => 'species',
                               4 => 'sample_type');
$sample_line_array = array();
$sample_name_array = array();
$array_error = array();
$index_array_error = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_table")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('sample_functions.php');
require_once('constants.php');
$dbconn = database_connect();
// **************************************************************
// This function checks an array of sample data
// for barcode indexes with invalid characters.
// **************************************************************
function check_barcode_indexes ($dbconn, $input_line_array)
{
  $array_error = array();
  // Loop through all the rows of the input line array.
  foreach ($input_line_array as $rowkey => $rowvalue)
  {
    if (! dba_nt_only ($rowvalue['barcode_index']))
    {
      $display_row = $rowkey + 1;
      $array_error[] = "Data row ". $display_row .
                       ":  Barcode index may only contain " .
                       "the characters A, C, G, T, and hyphen.";
    }  // if (! dba_nt_only ($rowvalue['barcode_index']))
  }  // foreach ($input_line_array as $rowkey as $rowvalue)
  return $array_error;
}  // function check_barcode_indexes
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
if ($project_uid == 0)
  $array_error[] = "No project selected.";
// ****
// Set variable that indicates whether custom barcodes or
// standard barcodes are used.
// ****
$barcode_format = (isset ($_SESSION['barcode_format']) ?
 $_SESSION['barcode_format'] : "");
if ($barcode_format == 'standard')
{
  unset ($_SESSION['barcode_format']);
  header("location: update_samples_mode1.php");
  exit;
} else {
  $standard_checked = '';
  $custom_checked = 'checked="checked"';
}  // if ($barcode_format == 'standard')
// Set variable that indicates whether standard radio button is disabled.
$standard_disabled = '';
$standard_title = '';
if ($project_uid > 0)
{
  if (all_project_barcodes_standard (
       $dbconn, $project_uid, $barcode_separator) === false)
  {
    $standard_disabled = 'disabled="disabled"';
    $standard_title = 'title="Some barcodes in the project are not standard."';
  }  // if (all_project_barcodes_standard (
}  // if ($project_uid > 0)
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_project_details']))
    {
      // Return to project details page.
      unset($_SESSION['submit_update_table']);
      unset ($_SESSION['barcode_format']);
      header("location: project_details.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      unset($_SESSION['submit_update_table']);
    } elseif (isset($_POST['submit_update_table'])) {
      // Put posted table values into sample line array.
      $line_number = 0;
      foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
      {
        $sample_line_array[$line_number]['sample_uid'] =
         $_SESSION['sample_uid'][$samplerow];
        $sample_line_array[$line_number]['sample_name'] =
         trim ($sample_name);
        $sample_line_array[$line_number]['sample_description'] =
         trim ($_SESSION['sample_description'][$samplerow]);
        $sample_name_array[$line_number] = trim ($sample_name);
        $sample_line_array[$line_number]['status'] =
         $_SESSION['status'][$samplerow];
        $sample_line_array[$line_number]['barcode'] =
         trim ($_SESSION['barcode'][$samplerow]);
        $sample_line_array[$line_number]['barcode_index'] =
         trim ($_SESSION['barcode_index'][$samplerow]);
        $sample_line_array[$line_number]['species'] =
         trim ($_SESSION['species'][$samplerow]);
        $sample_line_array[$line_number]['sample_type'] =
         trim ($_SESSION['sample_type'][$samplerow]);
        $sample_line_array[$line_number]['batch_group'] =
         trim ($_SESSION['batch_group'][$samplerow]);
        if ($use_sample_bonus_columns)
        {
          $sample_line_array[$line_number]['concentration'] =
           trim ($_SESSION['concentration'][$samplerow]);
          $sample_line_array[$line_number]['volume'] =
           trim ($_SESSION['volume'][$samplerow]);
          $sample_line_array[$line_number]['rna_integrity_number'] =
           trim ($_SESSION['rna_integrity_number'][$samplerow]);
        }  // if ($use_sample_bonus_columns)
        $sample_line_array[$line_number]['comments'] =
         trim ($_SESSION['comments'][$samplerow]);
        $line_number = $line_number + 1;
      }  // foreach ($_SESSION['sample_name'] as $samplerow => $sample_name)
      // Check sample array for obvious errors.
      $array_error = check_row_samples ($dbconn, $sample_name_array,
       $sample_line_array, $sample_required_array, $project_uid,
       $array_sample_status_values, $update_sample_name_length,
       $max_batch_group_length, $use_sample_bonus_columns, "UPDATE");
      // Check that all barcode indexes contain DNA nucleotides only.
      $index_array_error = check_barcode_indexes ($dbconn, $sample_line_array);
      $array_error = array_merge ($array_error, $index_array_error);
      if (count ($array_error) < 1)
      {
       // Insert sample line array into sample table.
       $array_error = update_samples ($dbconn, $project_uid,
        $sample_line_array, $use_sample_bonus_columns, $barcode_separator);
       if (count($array_error) < 1)
       {
        // If the inserts were successful, return to project view details page.
        unset ($_SESSION['barcode_format']);
        unset($_SESSION['submit_update_table']);
        header("location: project_details.php");
        exit;
       }  // if (count($array_error < 1)
     }  // if (count ($array_error) < 1)
    } elseif (isset($_POST['submit_exit'])) {
      header("location: project_details.php");
      exit;
    }  // if (isset($_POST['submit_project_details']))
  }  // if ($_POST['process'] == 1)
} else {
  $standard_checked = '';
  $custom_checked = 'checked="checked"';
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Samples, ',$abbreviated_app_name,'</title>';
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
<?php
readfile("text_styles.css");
?>
</head>
<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Update Samples - ',
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
  echo '<div id="mainContent"> ',
       '<script src="javascript_source.js" ',
       'language="javascript" ',
       'type="text/javascript"></script>';
  echo '<h3 class="grayed_out">Project: ',$project_name,'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$run_type,'</h3>';
  if (count($array_error) > 0)
  {
    foreach ($array_error as $error)
    {
      if (strlen(trim($error)) > 0)
      {
        $error_exists = 1;
        echo '<span class="errortext">'.$error.'</span><br />';
      }  // if (strlen(trim($error)) > 0)
    }  // foreach ($array_error as $error)
    echo '<span class="errortext">Correct and resubmit.',
         '</span><br />';
  }  // if (count($array_error) >= 1)
?>
<form method="post" action="" name="form_table">
<input type="hidden" name="process" value="1"/>
<input type="submit" name="submit_project_details" value="Project Details" 
       title="Return to Project Details page." class="buttontext" />
<input type="submit" name="submit_update_table" value="Submit" 
       title="Submit table." class="buttontext" />
<input type="submit" name="submit_reset" value="Reset" class="buttontext"
       title="Restore to most recent saved changes." />
<?php
  echo '<input type="submit" name="submit_exit" value="Quit" ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
       'title="Return to Project Details page without saving." ',
       'class="buttontext" />';
  echo '<td><input type="button" value="See Barcode List" ',
       'title="Shows all the reference barcodes ',
       'and associated barcode indexes." ',
       'onclick="javascript:barcodeReferenceWindow()" />',
       '</td>';
  echo '<br />';
  echo '<h2>Barcode Format</h2>';
  echo '<input type="radio" name="barcode_format" value="standard" ',
       ' onclick="this.form.submit();" ',
       $standard_checked,' ',
       $standard_disabled,' ',
       $standard_title,
       ' />Standard<br />';
  echo '<input type="radio" name="barcode_format" value="custom" ',
       ' onclick="this.form.submit();" ',
       $custom_checked,
       ' />Custom<br />';
?>
<p style="text-align: left; margin: 2px;"><span class="smallrequiredtext">
 <b><i>* Required Fields</i></b></span></p>
<table id="tblGrid" border="1" width="100%">
<thead>
  <tr>
    <th class="thSmallerRedBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    *Sample</th>
    <th class="thSmallerBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    Sample Description</th>
    <th class="thSmallerRedBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    *Status</th>
    <th class="thSmallerRedBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    *Barcode</th>
    <th class="thSmallerBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    Barcode Index</th>
    <th class="thSmallerRedBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    *Species</th>
    <th class="thSmallerRedBlueBorder" scope="col" width="5%"
     style="text-align:center;" >
    *Type</th>
    <th class="thSmallerBlueBorder" scope="col" width="50%"
     style="text-align:center;" >
    Batch Group</th>
<?php
    if ($use_sample_bonus_columns)
    {
      echo '<th class="thSmallerBlueBorder" scope="col" width="50%"',
           'style="text-align:center;" >',
           'Concentration</th>';
      echo '<th class="thSmallerBlueBorder" scope="col" width="50%"',
           'style="text-align:center;" >',
           'Volume</th>';
      echo '<th class="thSmallerBlueBorder" scope="col" width="50%"',
           'style="text-align:center;" >',
           'RIN</th>';
    }  // if ($use_sample_bonus_columns)
?>
    <th class="thSmallerBlueBorder" scope="col" width="50%"
     style="text-align:center;" >
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
  if ($project_uid > 0)
  {
    if (!isset($_SESSION['submit_update_table']))
    {
      $result = pg_query($dbconn,"
       SELECT sample_uid,
              sample_name,
              sample_description,
              sample.status,
              barcode,
              barcode_index,
              species,
              sample_type,
              batch_group,
              concentration,
              volume,
              rna_integrity_number,
              comments
            FROM sample,
                 project
           WHERE project.project_uid = $project_uid AND
                 sample.project_uid = project.project_uid
           ORDER BY sample_name");
      if (!$result)
      {
        echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
      } else {
        $line_number = 0;
        for ($i=0; $i < pg_num_rows($result); $i++)
        {
        $row_sample = pg_fetch_assoc ($result);
        $sample_line_array[$line_number]['sample_uid'] =
         $row_sample['sample_uid'];
        $sample_line_array[$line_number]['sample_name'] =
         $row_sample['sample_name'];
        $sample_name_array[$line_number] = $row_sample['sample_name'];
        $sample_line_array[$line_number]['sample_description'] =
         $row_sample['sample_description'];
        $sample_line_array[$line_number]['status'] = $row_sample['status'];
        $sample_line_array[$line_number]['barcode'] = $row_sample['barcode'];
        $sample_line_array[$line_number]['barcode_index'] =
         $row_sample['barcode_index'];
        $sample_line_array[$line_number]['species'] = $row_sample['species'];
        $sample_line_array[$line_number]['sample_type'] =
         $row_sample['sample_type'];
        $sample_line_array[$line_number]['batch_group'] =
         $row_sample['batch_group'];
        if ($use_sample_bonus_columns)
        {
          $sample_line_array[$line_number]['concentration'] =
           $row_sample['concentration'];
          $sample_line_array[$line_number]['volume'] =
           $row_sample['volume'];
          $sample_line_array[$line_number]['rna_integrity_number'] =
           $row_sample['rna_integrity_number'];
        }  // if ($use_sample_bonus_columns)
        $sample_line_array[$line_number]['comments'] = $row_sample['comments'];
        $line_number++;
        }  // for ($i=0; $i < pg_num_rows($result); $i++)
      }  // if (!$result)
    }  // if (!isset($_SESSION['submit_update_table']))
    foreach ($sample_line_array as $rowkey => $rowvalue)
    {
      echo '<tr>';
      echo '<td width="5%">',
           '<input type="hidden" name="sample_uid[]" value="',
           $rowvalue['sample_uid'],'"/>',
           '<input name="sample_name[]" type="text" ',
           'title="',$sample_mouseover,'" ',
           'class="inputrow" size="15" value="',
           trim($rowvalue['sample_name'], '"'),
           '" /></td>';
      echo '<td>',
           '<textarea name="sample_description[]" cols="15" rows="2" ',
           'class="inputseriftext">',
           trim ($rowvalue['sample_description'], '"'),
           '</textarea></td>';
      // Determine select indicator for each status option.
      $active_selected = '';
      $holding_selected = '';
      $archive_selected = '';
      $trash_selected = '';
      if ($rowvalue['status'] == 'Active')
      {
        $active_selected = 'selected="selected"';
      } elseif ($rowvalue['status'] == 'Holding') {
        $holding_selected = 'selected="selected"';
      } elseif ($rowvalue['status'] == 'Archive') {
        $archive_selected = 'selected="selected"';
      } elseif ($rowvalue['status'] == 'Trash') {
        $trash_selected = 'selected="selected"';
      }  // if ($rowvalue['status'] == 'Active')
      echo '<td><select name="status[]" class="inputrow">';
      echo '<option value="Active" class="inputrow" ',
           $active_selected,'>Active</option>';
      echo '<option value="Holding" class="inputrow" ',
           $holding_selected,'>Holding</option>';
      echo '<option value="Archive" class="inputrow" ',
           $archive_selected,'>Archive</option>';
      echo '<option value="Trash" class="inputrow" ',
           $trash_selected,'>Trash</option>';
      echo '</select></td>';
      echo '<td width="5%"><input name="barcode[]" type="text" ',
           'title="Only alphanumeric characters and underscore allowed." ',
           'class="inputrow" size="12" value="',
           $rowvalue['barcode'],
           '" /></td>';
      echo '<td width="5%"><input name="barcode_index[]" type="text" ',
           'onblur="testDNAntField(this);" ',
           'title="Only the characters A, C, G, and T are permitted." ',
           'class="inputrow" size="35" value="',
           $rowvalue['barcode_index'],
           '" /></td>';
      echo '<td width="5%"><input name="species[]" type="text" ',
           'title="Only alphanumeric characters, ',
           'space, dot, and underscore allowed." ',
           'class="inputrow" size="12" value="',
           $rowvalue['species'],
           '" /></td>';
      echo '<td width="5%"><input name="sample_type[]" type="text" ',
           'class="inputrow" size="20" value="',
           $rowvalue['sample_type'],
           '" /></td>';
      echo '<td width="5%"><input name="batch_group[]" type="text" ',
           'class="inputrow" size="20" value="',
           $rowvalue['batch_group'],
           '" /></td>';
      if ($use_sample_bonus_columns)
      {
        echo '<td width="5%"><input name="concentration[]" type="text" ',
             'class="inputrow" size="20" value="',
             $rowvalue['concentration'],
             '" /></td>';
        echo '<td width="5%"><input name="volume[]" type="text" ',
             'class="inputrow" size="20" value="',
             $rowvalue['volume'],
             '" /></td>';
        echo '<td width="5%"><input name="rna_integrity_number[]" ',
             'type="text" ',
             'class="inputrow" size="20" value="',
             $rowvalue['rna_integrity_number'],
             '" /></td>';
      }  // if ($use_sample_bonus_columns)
      echo '<td>',
           '<textarea name="comments[]" cols="20" rows="2" ',
           'class="inputseriftext">',
           $rowvalue['comments'],
           '</textarea></td>';
      echo '</tr>';
    }  // foreach ($sample_line_array as $rowkey => $rowvalue)
  }  // if ($project_uid > 0)
  echo '</tbody></table>';
?>
</form>
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
