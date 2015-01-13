<?php
session_start();
if (isset($_POST['process_form_type']))
{
  if ($_POST['process_form_type'] == 1)
  {
    if (isset($_POST['submit_return']))
    {
      // Return to project details page.
      unset ($_SESSION['entry_method']);
      unset ($_SESSION['submit_return']);
      header("location: project_log.php");
      exit;
    }  // if (isset($_POST['submit_return']))
  }  // if ($_POST['process_form_type'] == 1)
}  // if (isset($_POST['process_form_type']))
require_once('db_fns.php');
require_once('project_functions.php');
require_once('constants.php');
$sample_key_array = array(0 => 'batch_group',
                          1 => 'sample_name',
                          2 => 'sample_description',
                          3 => 'species');
$sample_required_array = array(0 => 'sample_name', 1=>'species');
$array_error = array();
$sample_line_array = array();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_return") &&
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
 $_SESSION['entry_method'] : "");
if ($entry_method == 'fields')
{
  header("location: update_log_samples_mode1.php");
  exit;
// Mode 2
} elseif ($entry_method == 'file') {
  $fields_checked = '';
  $file_checked = 'checked="checked"';
}  // if ($entry_method == 'fields')
$dbconn = database_connect();
$project = (isset ($_SESSION['project']) ?
 $_SESSION['project'] : "");
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
$project_log_uid = (isset ($_SESSION['project_log_uid']) ?
 $_SESSION['project_log_uid'] : 0);
$project_log_run_lane = (isset ($_SESSION['project_log_run_lane']) ?
 $_SESSION['project_log_run_lane'] : "");
// Determine what action brought us here and process accordingly.
if (isset($_POST['process_file']))
{
  if ($_POST['process_file'] == 1)
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
          // Check that the header row is correct.
          $array_error = validate_header_string ($sample_key_array,
           $sample_lines[0], "\t");
          if (count($array_error) < 1)
          {
            for ($i=1; $i < count($sample_lines); $i++)
            {
              $sample_fields_array = string_to_array ($sample_lines[$i],
               "\t", $sample_key_array);
              if (! $sample_fields_array)
              {
                // Do nothing if this line was not added to array.
              } else {
                $sample_line_row = count ($sample_line_array);
                $sample_name_array[$sample_line_row] = $sample_fields_array['sample_name'];
                foreach ($sample_fields_array as $key => $value)
                {
                  $sample_line_array[$sample_line_row][$key] = $value;
                }  // foreach ($sample_fields_array as $key => $value)
              }  // if (! $sample_fields_array)
            }  // for ($i=1; $i < count($sample_lines); $i++)
            // Check sample array for obvious errors.
            $array_error = check_log_samples (
             $dbconn, $project_log_run_lane,
             $project_log_uid, $sample_name_array,
             $sample_line_array, $sample_required_array,
             $max_batch_group_length, $max_sample_name_length, TRUE);
            if (count($array_error) < 1)
            {
              // Insert sample line array into project log sample table.
              $array_error = append_log_samples (
               $dbconn, $_SESSION['project_log_run_lane'],
               $project_log_uid, $sample_line_array);            
              // If the inserts were successful, return to project log page.
              if (count($array_error) < 1)
              {
                unset ($_SESSION['entry_method']);
                unset ($_SESSION['submit_upload_file']);
                // Delete the upload file.
                if ($_FILES['userfile']['size'] > 0)
                {
                  unlink($upload_dir.'/'.basename($_FILES['userfile']['name']));
                }  // if ($_FILES['userfile']['size'] > 0)
                header("location: project_log.php");
                exit;
              }  // if (count($array_error < 1)
            }  // if (count($array_error) < 1)
          }  // if (count($array_error) < 1)
        }  //if (($_FILES['userfile']['size'] > 0)...
        // Delete the upload file.
        if ($_FILES['userfile']['size'] > 0)
        {
          unlink($upload_dir.'/'.basename($_FILES['userfile']['name']));
        }  // if ($_FILES['userfile']['size'] > 0)
      }  // if (isset($_FILES['userfile']))
    }  // if (isset($_POST['submit_upload_file']))
  }  // if ($_POST['process_file'] == 1)
}  // if (isset($_POST['process_file']))
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
echo '<h1 align="center"><span class="titletext">Add Project Log Samples - ',
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
    } elseif (pg_num_rows ($result_puid) > 0) {
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
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>" name="form_return">
<input type="hidden" name="process_form_type" value="1" />
<input type="submit" value="Project Log" name="submit_return"
 title="Return to Project Log page." class="buttontext" />
<?php
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
  echo '<p class="displaytext"><b>',$sample_format_msg,'</b></p>';
  echo '</td></tr></table></form>';
    if (count($array_error) >= 1)
    {
      $error_exists = 0;
      echo '<br />';
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
        echo '<span class="errortext">Correct file and upload again.',
             '</span><br /><br />';
      }  // if ($error_exists > 0) 
    }  // if (count($array_error) >= 1)
    echo '<form enctype="multipart/form-data" method="post" ',
         'style="width:700px;" action="',
         $_SERVER['PHP_SELF'],
         '" name="form_file">';
    echo '<input type="hidden" name="process_file" value="1" />';
    echo '<table class="tableNoBorder">';
    echo '<thead><tr><th class="thNoBorder"><b>Sample File</b></th>',
         '</tr></thead>';
    echo '<tbody><tr>';
    echo '<td><input type="hidden" name="MAX_FILE_SIZE" value="',
          $max_text_file_size,'" />';
    echo '<input name="userfile" type="file" ',
         'title="Choose a text file of samples" class="inputtext" /></td>';
    echo '<td><input type="submit" value="Upload File" ',
         'name="submit_upload_file" ',
         'title="Text file of sample values." /></td>';
    echo '<td><input type="button" value="See Example File" ',
         'title="Shows an example of a tab-delimited text file ',
         'of sample values." ',
         'onclick="javascript:example_pop_up(',
         '\'example_log_sample.png\')" ',
         '/></td>';
    echo '</tr></tbody>';
    echo '</table>';
    echo '</form>';
?>
<hr />
<!-- Display a table of the project log samples.  -->
<table id="sample_table" border="1" class="sortable" >
<thead>
  <tr>
    <th class="sorttable_numeric" scope="col" width="200"
     style="text-align:center" >
    Batch Group</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample Name</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample Description</th>
    <th class="sorttable_alpha" scope="col" width="200"
    style="text-align:center">
    Species</th>
  </tr>
</thead>
<tbody>
<?php
if ($project_uid > 0)
{
    $result = pg_query ($dbconn, "
     SELECT batch_group,
            sample_name,
            sample_description,
            species
       FROM $project_log_run_lane
      WHERE project_log_uid = $project_log_uid
      ORDER BY batch_group,
               project_log_run_lane_uid");
  if (!$result)
  {
    $array_error[] = pg_last_error ($dbconn);
    echo '<tr><td class="tdError" >',pg_last_error ($dbconn),'</td></tr>';
  } else {
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row_sample = pg_fetch_assoc ($result);
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['batch_group']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['sample_name']),'</td>';
     echo '<td class="tdBlueBorder" style="text-align:left">',
          '<div style="width: 200px; height: 40px; overflow: ',
          'auto; padding: 5px;"><font face="sylfaen">',
          td_ready($row_sample['sample_description']),
          '</font></div></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['species']),'</td>';
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
