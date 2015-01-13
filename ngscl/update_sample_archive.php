<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
$archive_fields = array(0 => 'freezer',
                        1 => 'shelf_number',
                        2 => 'box_number',
                        3 => 'box_position',
                        4 => 'comments');
$required_fields = array(0 => 'freezer',
                         1 => 'shelf_number',
                         2 => 'box_number',
                         3 => 'box_position');
$archive_line_array = array();
$array_error = array();
$dbconn = database_connect();
// **************************************************************
// This function accepts an array and a list of keys for the array.
// If any of the key fields in the input list have a value
// in the input array, then a 0 (indicating FALSE) is returned.
// Otherwise, a 1 (indicating TRUE) is returned.
// **************************************************************
function row_is_empty ($input_array, $key_list) {
  $empty_row = 1;
  // Loop through the input key list.
  foreach ($key_list as $keynum => $keyvalue)
  {
    if (strlen (trim ($input_array[$keyvalue])) > 0)
    {
      $empty_row = 0;
      break;
    }  // if (strlen (trim ($input_array[$keyvalue])) > 0)
  }  // foreach ($key_list as $keynum => $keyvalue)
  return ($empty_row);
}  // function row_is_empty ($input_array, $key_list)
// **************************************************************
// This function checks an array of sample archive data for errors.
// An array of error messages are returned if errors are found.
// **************************************************************
function check_row_archive (
 $archive_line_array, $archive_fields, $required_fields)
{
  $array_error = array();
  $missing_fields = '';
  foreach ($archive_line_array as $rowkey => $rowvalue)
  {
    // If all the fields are blank, then skip this row.
    // Check for any errors.
    if (row_is_empty ($rowvalue, $archive_fields) == 0)
    {
      // The error message should indicate any required fields that are missing.
      $missing_fields = missing_field_list ($rowvalue, $required_fields);
      if (strlen (trim ($missing_fields)) > 0)
      {
         $display_row = $rowkey + 1;
         $error_msg = "Row ".
                      $display_row.
                      " : Required fields missing".$missing_fields;
         $array_error[$rowkey] = $error_msg;
      }  // if (strlen (trim ($missing_fields)) > 0)
    }  // if (row_is_empty ($rowvalue, $archive_fields) == 0)
  }  // foreach ($archive_line_array as $rowkey => $rowvalue)
  return $array_error;
}  // function check_row_archive ($archive_line_array, $archive_fields)
// **************************************************************
// This function returns a html string to create a table row
// of archive data.
// **************************************************************
function archive_row_string ($archive_row)
{
  // Set variables for readability.
  $sample_uid = $archive_row['sample_uid'];
  $sample_name = $archive_row['sample_name'];
  $project_uid = $archive_row['project_uid'];
  $project_name = $archive_row['project_name'];
  $primary_investigator_uid = $archive_row['primary_investigator_uid'];
  $primary_investigator_name = $archive_row['primary_investigator_name'];
  $status = $archive_row['status'];
  $freezer = htmlentities ($archive_row['freezer']);
  $shelf_number = $archive_row['shelf_number'];
  $box_number = $archive_row['box_number'];
  $box_position = htmlentities($archive_row['box_position']);
  $comments = htmlentities($archive_row['comments']);
  $return_string = '<tr>'.
   '<td class="tdSmallerBlueBorder" style="text-align:center">'.
   '<a href="javascript:void(0)" onclick="sampleWindow(\''.
   $sample_uid.
   '\');" title="Display information on sample '.
   $sample_name.'.">'.
   td_ready($sample_name).'</a></td>'.
   '<td class="tdBlueBorder" style="text-align:center">'.
   '<a href="javascript:void(0)" onclick="projectWindow(\''.
   $project_uid.'\');" '.
   'title="Display information on project '.
   $project_name.'.">'.
   td_ready($project_name).'</a></td>'.
   '<td class="tdBlueBorder" style="text-align:center"><a '.
   'href="javascript:void(0)" onclick="primary_investigatorWindow(\''.
   $primary_investigator_uid.'\');" '.
   'title="Display information on primary investigator '.
   $primary_investigator_name.'.">'.
   td_ready($primary_investigator_name).'</a></td>'.
   '<td class="tdSmallerBlueBorder" style="text-align:center">'.
   td_ready($status).'</td>'.
   '<td><input name="freezer[]" type="text" '.
   'class="inputrow" size="20" value="'.
   $freezer.'" /></td>'.
   '<td><input name="shelf_number[]" type="text" '.
   'onBlur="testIntField(this);" class="inputrow" '.
   'title="Must be a positive whole number." '.
   'size="3" value="'.
   $shelf_number.'" /></td>'.
   '<td><input name="box_number[]" type="text" '.
   ' class="inputrow" size="5" value="'.
   $box_number.'" /></td>'.
   '<td><input name="box_position[]" type="text" '.
   'class="inputrow" size="10" value="'.
   $box_position.'" /></td>'.
   '<td>'.
   '<textarea name="comments[]" cols="20" rows="2" '.
   'class="inputseriftext">'.
   htmlentities ($comments, ENT_NOQUOTES).
   '</textarea></td>'.
   '<td><input type="button" class="buttonrow" value="Clear" '.
   'title="Clear input fields in this row." '.
   'onclick="clearRow(this.parentNode.parentNode.rowIndex);" '.
   '/></td>'.
   '</tr>';
  return $return_string;
}  // function archive_row_string ($archive_row)
// **************************************************************
// This function updates the database with an array of
// sample archive data. An array of error messages is returned.
// **************************************************************
function update_archive ($dbconn, $archive_line_array, $archive_fields)
{
  $array_error = array();
  // Loop through the archive name dimension.
  foreach ($archive_line_array as $rowkey => $rowvalue)
  {
    // Set variables for readability.
    $display_row = $rowkey + 1;
    $sample_uid = $rowvalue['sample_uid'];
    $ref_archive_name_uid = $rowvalue['ref_archive_name_uid'];
    $archive_name = $rowvalue['archive_name'];
    $freezer = ddl_ready ($rowvalue['freezer']);
    $shelf_number = $rowvalue['shelf_number'];
    $box_number = $rowvalue['box_number'];
    $box_position = ddl_ready ($rowvalue['box_position']);
    $comments = ddl_ready ($rowvalue['comments']);
    // See if this row already exists for the sample.
    $result_count = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM archive
      WHERE sample_uid = $sample_uid AND
            ref_archive_name_uid = $ref_archive_name_uid"); 
    if (!$result_count)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      if ($line = pg_fetch_assoc ($result_count))
      {
        if ($line['row_count'] > 0)
        {
          // ***
          // If row is not empty, update the row for this sample and
          // archive name.
          // ***
          if (row_is_empty ($rowvalue, $archive_fields) == 0)
          {
            $result_update = pg_query ($dbconn, "
             UPDATE archive
                SET freezer = '$freezer',
                    shelf_number = $shelf_number,
                    box_number = '$box_number',
                    box_position = '$box_position',
                    comments = '$comments'
              WHERE sample_uid = $sample_uid AND
                    ref_archive_name_uid = $ref_archive_name_uid"); 
            if (!$result_update)
            {
              $array_error[] = "Row ".$display_row.": ".pg_last_error($dbconn);
            }  // if (!$result_update)
          } else {
            // If row is empty, delete the row for this sample and archive name.
            $result_update = pg_query ($dbconn, "
             DELETE FROM archive
              WHERE sample_uid = $sample_uid AND
                    ref_archive_name_uid = $ref_archive_name_uid"); 
            if (!$result_update)
            {
              $array_error[] = "Row ".$display_row.": ".pg_last_error($dbconn);
            }  // if (!$result_update)
          }  // if (row_is_empty ($rowvalue, $archive_fields) == 0)
        } else {
          // If row is not empty, insert the row for this sample and archive name.
          if (row_is_empty ($rowvalue, $archive_fields) == 0)
          {
            $result_insert = pg_query ($dbconn, "
             INSERT INTO archive
              (sample_uid, ref_archive_name_uid, freezer,
               shelf_number, box_number, box_position, comments)
             VALUES
              ($sample_uid, $ref_archive_name_uid, '$freezer',
               $shelf_number, '$box_number', '$box_position', '$comments')");
            if (!$result_insert)
            {
              $array_error[] = "Row ".$display_row.": ".pg_last_error($dbconn);
            }  // if (!$result_update)
          }  // if (row_is_empty ($rowvalue, $archive_fields) == 0)
        }  // if ($line['row_count'] > 0)
      } else {
        $array_error[] = "Row ".$display_row.": ".pg_last_error($dbconn);
        break;
      }  // if ($line = pg_fetch_assoc ($result_count))
    }  // if (!$result_count)
  }  // foreach ($archive_line_array as $rowkey => $rowvalue)
  return $array_error;
}  // function update_archive
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_archive_update")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
  // ****
  // Determine what action brought us here and
  //  populate sample archive array accordingly.
  // ****
  if (isset ($_POST['submit_archive']))
  {
    header("location: sample_archive.php");
    exit;
  }  // if (isset ($_POST['submit_archive']))
  // If this is the first time through, or a reset, select from database.
  $ref_archive_name_uid = $_SESSION['choose_archive_archive_select'];
  $result_archive_name = pg_query ($dbconn, "
   SELECT archive_name
     FROM ref_archive_name
    WHERE ref_archive_name_uid = $ref_archive_name_uid");
  if (!$result_archive_name)
  {
    $array_error[0] = pg_last_error ($dbconn);
  } else {
    $archive_name = pg_fetch_result ($result_archive_name, 0, 0);
    if (!isset($_POST['process']) || isset($_POST['submit_reset_archive']))
    {
      $_SESSION['freezer_group'] = "";
      $_SESSION['shelf_number_group'] = "";
      $_SESSION['box_number_group'] = "";
      if (isset($_SESSION['sample_uid_archive']))
      {
        foreach ($_SESSION['sample_uid_archive'] as $samplerow => $sample_uid)
        {
          $result_td = pg_query($dbconn,"
           SELECT sample.sample_uid,
                  project.project_uid,
                  project.project_name,
                  primary_investigator.primary_investigator_uid,
                  primary_investigator.name AS primary_investigator_name,
                  sample_name,
                  sample.status,
                  archive.ref_archive_name_uid,
                  freezer,
                  shelf_number,
                  box_number,
                  box_position,
                  archive.comments
             FROM project,
                  primary_investigator,
                  sample
             LEFT OUTER JOIN archive
               ON sample.sample_uid = archive.sample_uid AND
                  ref_archive_name_uid = $ref_archive_name_uid
            WHERE sample.sample_uid = $sample_uid AND
                  sample.project_uid = project.project_uid AND
                  project.primary_investigator_uid =
                   primary_investigator.primary_investigator_uid");
          if (!$result_td)
          {
             $array_error[0] = pg_last_error ($dbconn);
          } else {
            $row_td = pg_fetch_assoc($result_td);
            $archive_line_array[$samplerow]['sample_uid'] = $sample_uid;
            $archive_line_array[$samplerow]['sample_name'] = $row_td['sample_name'];
            $archive_line_array[$samplerow]['project_uid'] = $row_td['project_uid'];
            $archive_line_array[$samplerow]['project_name'] = $row_td['project_name'];
            $archive_line_array[$samplerow]['primary_investigator_uid'] = $row_td['primary_investigator_uid'];
            $archive_line_array[$samplerow]['primary_investigator_name'] = $row_td['primary_investigator_name'];
            $archive_line_array[$samplerow]['status'] = $row_td['status'];
            $archive_line_array[$samplerow]['archive_name'] = $archive_name;
            $archive_line_array[$samplerow]['ref_archive_name_uid'] = $ref_archive_name_uid;
            $archive_line_array[$samplerow]['freezer'] = $row_td['freezer'];
            $archive_line_array[$samplerow]['shelf_number'] = $row_td['shelf_number'];
            $archive_line_array[$samplerow]['box_number'] = $row_td['box_number'];
            $archive_line_array[$samplerow]['box_position'] = $row_td['box_position'];
            $archive_line_array[$samplerow]['comments'] = $row_td['comments'];
          }  //if (!$result_td)
        }  // foreach ($_SESSION['sample_uid_archive'] as $samplerow =>...
      }  // if (isset($_SESSION['sample_uid_archive]))
    } elseif (isset($_SESSION['sample_uid_archive'])) {
      if (isset($_SESSION['sample_uid_archive']))
      {
        foreach ($_SESSION['sample_uid_archive'] as $samplerow => $sample_uid)
        {
          $result_td = pg_query($dbconn,"
           SELECT sample.sample_uid,
                  project.project_uid,
                  project.project_name,
                  primary_investigator.primary_investigator_uid,
                  primary_investigator.name AS primary_investigator_name,
                  sample_name,
                  sample.status,
                  archive.ref_archive_name_uid
             FROM project,
                  primary_investigator,
                  sample
             LEFT OUTER JOIN archive
               ON sample.sample_uid = archive.sample_uid AND
                  ref_archive_name_uid = $ref_archive_name_uid
            WHERE sample.sample_uid = $sample_uid AND
                  sample.project_uid = project.project_uid AND
                  project.primary_investigator_uid =
                   primary_investigator.primary_investigator_uid");
          if (!$result_td)
          {
             $array_error[0] = pg_last_error ($dbconn);
          } else {
            $row_td = pg_fetch_assoc($result_td);
            $archive_line_array[$samplerow]['sample_uid'] = $sample_uid;
            $archive_line_array[$samplerow]['sample_name'] = $row_td['sample_name'];
            $archive_line_array[$samplerow]['project_uid'] = $row_td['project_uid'];
            $archive_line_array[$samplerow]['project_name'] = $row_td['project_name'];
            $archive_line_array[$samplerow]['primary_investigator_uid'] = $row_td['primary_investigator_uid'];
            $archive_line_array[$samplerow]['primary_investigator_name'] = $row_td['primary_investigator_name'];
            $archive_line_array[$samplerow]['status'] = $row_td['status'];
            $archive_line_array[$samplerow]['archive_name'] = $archive_name;
            $archive_line_array[$samplerow]['ref_archive_name_uid'] = $ref_archive_name_uid;
            $archive_line_array[$samplerow]['freezer'] = trim ($_SESSION['freezer'][$samplerow]);
            $archive_line_array[$samplerow]['shelf_number'] = $_SESSION['shelf_number'][$samplerow];
            $archive_line_array[$samplerow]['box_number'] = $_SESSION['box_number'][$samplerow];
            $archive_line_array[$samplerow]['box_position'] = trim ($_SESSION['box_position'][$samplerow]);
            $archive_line_array[$samplerow]['comments'] = trim ($_SESSION['comments'][$samplerow]);
            if (isset($_POST['submit_archive_values']))
            {
              if (strlen (trim ($_SESSION['freezer_group'])) > 0)
              {
                $archive_line_array[$samplerow]['freezer'] = $_SESSION['freezer_group'];
              }  // if (strlen (trim ($_SESSION['freezer_group'])) > 0)
              if (strlen (trim ($_SESSION['shelf_number_group'])) > 0)
              {
                $archive_line_array[$samplerow]['shelf_number'] = $_SESSION['shelf_number_group'];
              }  // if (strlen (trim ($_SESSION['shelf_number_group'])) > 0)
              if (strlen (trim ($_SESSION['box_number_group'])) > 0)
              {
                $archive_line_array[$samplerow]['box_number'] = $_SESSION['box_number_group'];
              }  // if (strlen (trim ($_SESSION['box_number_group'])) > 0)
            }  // if (!isset($_POST['submit_archive_values'])
            $archive_line_array[$samplerow]['box_position'] = trim ($_SESSION['box_position'][$samplerow]);
            $archive_line_array[$samplerow]['comments'] = trim ($_SESSION['comments'][$samplerow]);
          }  //if (!$result_td)
        }  // foreach ($_SESSION['sample_uid_archive'] as $samplerow => $sample_uid)
      }  // if (isset($_SESSION['sample_uid_archive']))
      // Submit changes to the database.
      if (isset ($_POST['submit_update_archive']))
      {
         // Check for errors in array of archive lines.
         $array_error = check_row_archive (
          $archive_line_array, $archive_fields, $required_fields);
         if (count ($array_error) < 1)
         {
           // Update the database based on the archive lines.
           $array_error = update_archive ($dbconn,
                                          $archive_line_array, $archive_fields);
           // If update was successful, return to the sample archive page.
           if (count ($array_error) < 1)
           {
              header("location: sample_archive.php");
              exit;
           }  // if (count ($array_error) < 1)
         }  // if (count ($array_error) < 1)
      }  // if (isset ($_POST['submit_update_archive']))
    }  // if (!isset($_POST['process']) || isset($_POST['submit_reset']))
  }  // if (!result_archive_name)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Sample Archive, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!-- Begin
function clearRow(input_index)
{
  var tab = document.getElementById("sample_table");
  var index_row = tab.getElementsByTagName("tr").item(input_index);
  index_row.getElementsByTagName("input")[0].value = "";
  index_row.getElementsByTagName("input")[1].value = "";
  index_row.getElementsByTagName("input")[2].value = "";
  index_row.getElementsByTagName("input")[3].value = "";
  index_row.getElementsByTagName("textarea")[0].value = "";
}  // function clearRow
// End -->
</script>
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
  echo '<h1 style="text-align:center"><span class="titletext">Update Sample Archive - ',
       $app_name,'</span></h1>';
?>
  <!-- end #header --></div>
<?php
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  echo '<h3 class="grayed_out">Archive: ',
       $archive_name,'</h3>';
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
      echo '<span class="errortext">Correct and resubmit.',
           '</span><br />';
    }  // if ($error_exists > 0) 
  }  // if (count($array_error) >= 1)
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_archive_update" >';
  echo '<input type="hidden" name="process" value="1" />';
  echo '<input type="submit" name="submit_update_archive" ',
       'value="Update Records" class="buttontext" ',
       'title="Update archive records."/>&nbsp;';
  echo '<input type="submit" name="submit_reset_archive" value="Reset" ',
       'title="Restore to most recent saved changes." class="buttontext" />';
  echo '<input type="submit" name="submit_archive" value="Quit" ',
   'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
   'title="Return to Sample Archive page without saving." ',
   'class="buttontext" />';
  echo '<table id="apply_table" class="tableNoBorder"><tbody><tr>';
  if (!isset($_POST['process']) || isset($_POST['submit_reset_archive']))
  {
    $_SESSION['freezer_group'] = "";
    $_SESSION['shelf_number_group'] = "";
    $_SESSION['box_number_group'] = "";
  }  // if (!isset($_POST['process']) || isset($_POST['submit_reset_archive']))
  echo '<td style="text-align: left; margin: 2px;" class="smallertext" ><b>',
       'Freezer</b><br /><input name="freezer_group" type="text" value="',
       $_SESSION['freezer_group'],
       '" class="inputrow" size="10" ',
       'title="Enter a freezer to be applied to all the samples below."/>',
       '</td>';
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Shelf Number</b><br />',
       '<input name="shelf_number_group" type="text" value="',
       $_SESSION['shelf_number_group'],
       '" onblur="testIntField(this);" class="inputrow" size="3" ',
       'title="Enter a shelf number to be applied to all the samples below."/>',
       '</td>';
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Box Number</b><br />',
       '<input name="box_number_group" type="text" value="',
       $_SESSION['box_number_group'],
       '" class="inputrow" size="5" ',
       'title="Enter a box number to be applied to all the samples below."/>',
       '&nbsp;&nbsp;<input type="submit" name="submit_archive_values" ',
       'value="Apply to Samples" class="buttontext" ',
       'title="Apply the input values to the samples below."/>',
       '</td>';
  echo '</tr></tbody></table>';
  // Display any errors.
?>
<p style="text-align:left; margin: 2px;"><span class="smallrequiredtext">
<b><i>* Required Fields</i></b></span></p>
<table id="sample_table" border="1" width="100%" class="sortable">
<thead>
  <tr>
  <th class="sorttable_alpha" scope="col"
      style="text-align:center" >Sample</th>
  <th class="sorttable_alpha" scope="col" 
       style="text-align:center" >Project</th>
  <th class="sorttable_alpha" scope="col" 
       style="text-align:center" >PI</th>
  <th class="sorttable_alpha" scope="col" 
       style="text-align:center">Sample Status</th>
  <th class="sorttable_nosort_red" scope="col" 
       style="text-align:center">*Freezer</th>
  <th class="sorttable_nosort_red" scope="col" 
       style="text-align:center">*Shelf Number</th>
  <th class="sorttable_nosort_red" scope="col" 
       style="text-align:center">*Box Number</th>
  <th class="sorttable_nosort_red" scope="col" 
       style="text-align:center">*Box Position</th>
  <th class="sorttable_nosort" scope="col" 
       style="text-align:center">Archive Comments</th>
  <th class="sorttable_nosort" scope="col" 
      style="text-align:center" >Clear Button</th>
  </tr>
</thead>
<tbody>
<?php
  // Loop through the archive line array to create the archive table rows.
  foreach ($archive_line_array as $rowkey => $archive_row)
  {
    echo archive_row_string ($archive_row);
  }  // foreach ($archive_line_array as $rowkey => $archive_row)
?>
</tbody>
</table>
</form>
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
