<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_manage_lane_samples"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('run_functions.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
$array_dup_barcode = array();
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
$run_number_name = "";
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_return_to_details']))
    {
      header("location: run_details.php");
      exit;
    } elseif (isset($_POST['submit_table'])) {
      // Update the sample information.
      $sample_uid_array = $_SESSION['run_lane_sample_uid'];
      foreach ($sample_uid_array as $rowkey => $run_lane_sample_uid)
      {
        $sample_comment = ddl_ready (
                           $_SESSION['comments'][$run_lane_sample_uid]);
        $result_lane_update = pg_query ($dbconn, "
         UPDATE run_lane_sample
            SET comments = '$sample_comment'
          WHERE run_lane_sample_uid = $run_lane_sample_uid");
        if (!$result_lane_update)
          $array_error[] = pg_last_error ($dbconn);
      }  // foreach ($sample_uid_array as $rowkey => $sample_uid)
      unset($_POST['submit_table']);
      header("location: manage_lane_project_active_samples.php?project_uid=".
             $_SESSION['project_uid'].
             "&sticky_lane=".
             $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_notification'])) {
      $_SESSION['calling_page'] = 'manage_lane_project_active_samples.php';
      header("location: run_notification.php?sticky_lane=" .
             $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_exit'])) {
      header("location: manage_lane_project_active_samples.php?project_uid=".
             $_SESSION['project_uid'].
             "&sticky_lane=".
             $_SESSION['lane_number']);
      exit;
    }  // if (isset($_POST['submit_return_to_details']))
    // Set lane number and run_lane_uid according to pull-down menu.
    $_SESSION['lane_number'] = (isset ($_SESSION['choose_lane_number']) ?
     $_SESSION['choose_lane_number'] : 1);
    $lane_number = $_SESSION['lane_number'];
  }  // if ($_POST['process'] == 1)
} else {
  if ($run_uid > 0)
  {
    // Get run number and name.
    $result = pg_query ($dbconn, "
     SELECT run_number || '/' || run_name,
            run.ref_run_type_uid,
            run_type
       FROM run,
            ref_run_type
      WHERE run_uid = $run_uid AND
            run.ref_run_type_uid = ref_run_type.ref_run_type_uid");
    if (!$result)
    {
      echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
    } else {
      $_SESSION['run_number_name'] = pg_fetch_result ($result, 0, 0);
      $_SESSION['ref_run_type_uid'] = pg_fetch_result ($result, 0, 1);
      $_SESSION['run_type'] = pg_fetch_result ($result, 0, 2);
    }  // if (!$result)
  } else {
    $array_error[] = "No run selected.";
  }  // if ($run_uid > 0)
  // ****
  // On first accessing this page, get the lane passed
  // from another of the set of pages that manages lanes
  // or set the lane to one.
  // ****
  if (isset($_GET) &&
      isset($_GET['sticky_lane']) &&
      $_GET['sticky_lane'] != "")
  {
    $_SESSION['lane_number'] = $_GET['sticky_lane'];
  } else {
    $_SESSION['lane_number'] = 1;
  }  // if (isset($_GET) &&
  // Get the project uid.
  if (isset($_GET) &&
      isset($_GET['project_uid']) &&
      $_GET['project_uid'] != "")
  {
    $_SESSION['project_uid'] = $_GET['project_uid'];
  }  // if (isset($_GET) &&
  $lane_number = $_SESSION['lane_number'];
}  // if (isset($_POST['process']))
// Set run number, name, and type variables.
$run_number_name = (isset ($_SESSION['run_number_name']) ?
                    $_SESSION['run_number_name'] : "");
$ref_run_type_uid = (isset ($_SESSION['ref_run_type_uid']) ?
                     $_SESSION['ref_run_type_uid'] : 0);
$run_type = (isset ($_SESSION['run_type']) ?
             $_SESSION['run_type'] : "");
// Find run_lane_uid for this run and lane.
$run_lane_uid = find_run_lane ($dbconn, $run_uid, $lane_number);
$_SESSION['run_lane_uid'] = $run_lane_uid;
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Run Lane Comments, ',$abbreviated_app_name,'</title>';
?>
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
       'Update Run Lane Comments - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
// Get the run number and name.
  echo '<h3 class="grayed_out">Run: ',$run_number_name,'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$run_type,'</h3>';
  // ****
  // This is the pull-down for Lane Number.
  // ****
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_manage_lane_samples" >';
  echo '<table id=pull_down_table" class="tableNoBorder"><tbody><tr>';
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Lane</b><br />';
  echo '<select name="choose_lane_number" onchange="this.form.submit();" ',
       ' title="Choose lane to manage." class="inputtext" >';
  for ($lane_iteration=1; $lane_iteration <= $num_run_lanes; $lane_iteration++)
  {
    if ($lane_iteration == $lane_number)
    {
      echo '<option value="',
           $lane_iteration,
           '" selected="selected" >Lane ',
           $lane_iteration,
           '</option>';
    } else {
      echo '<option value="',
           $lane_iteration,
           '">Lane ',
           $lane_iteration,
           '</option>';
    }  // if ($lane_iteration == $lane_number)
  }  // for ($lane_iteration=1; $lane_iteration <= $num_run_lanes;...
  echo '</select>';
  echo '</td>';
  echo '</tr></tbody></table>';
  foreach ($array_error as $error)
  {
    if (strlen (trim ($error)) > 0)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // if (strlen (trim ($error)) > 0)
  }  // foreach ($array_error as $error)
  // Check for samples in the run with the wrong run type.
  $mismatch_string = run_sample_type_mismatch ($dbconn, $run_uid);
  if (strlen (trim ($mismatch_string)) > 0);
  {
    echo '<span class="errortext">',$mismatch_string,'</span><br />';
  }  // if (strlen (trim ($mismatch_string)) > 0);
  // Look for duplicate barcodes.
  if ($run_lane_uid > 0)
  {
    $array_dup_barcode = dup_barcodes_in_lane ($dbconn, $run_lane_uid);
  }  // if ($run_lane_uid > 0)
  foreach ($array_dup_barcode as $barcode_row)
  {
    echo '<span class="cautiontext">' .
         $barcode_row['barcode_caution'] .
         '</span><br />';
  }  // foreach ($array_dup_barcode as $barcode_row)
  echo '<input type="hidden" name="process" value="1" />';
  // The button to return to Run Details
  // should have slightly lareger text.
  echo '<input type="submit" name="submit_return_to_details" ',
       'value="Run Details" class="buttontext" ',
       'title="Return to Run Details page." />';
  echo '<input type="submit" name="submit_notification" ',
       'value="Notification" ',
       'title="Notify those users on the run mailing list." ',
       'class="buttontext" />';
  echo '<hr />';
  // Make a table for the submit buttons.
  echo '<input type="submit" name="submit_table" class="buttontext" ',
       'value="Save" ',
       'title="Save updated lane sample information." />';
  echo '<input type="submit" name="submit_reset" class="buttontext" ',
       'value="Reset" ',
       'title="Restore to most recent saved changes." />';
  echo '<input type="submit" name="submit_exit" value="Quit" ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
      'title="Return to Manage Lane Project Active Samples page ',
      'without saving." ',
      'class="buttontext" />';
  // Make a table for the lane samples.
  echo '<h2 class="headertext">Samples in Lane</h2>';
  echo '<table id="sample_table" border="1" width="100%" class="sortable" >';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Primary Investigator</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Contact</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Type</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Sample</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode Index</th>';
  echo '<th class="sorttable_numeric" scope="col" ',
       'style="text-align:center" >Load Concentration</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Batch Group</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Insert Size</th>';
  echo '<th class="sorttable_nosort_green" scope="col" ',
       'style="text-align:center">Lane Comment</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // ************
  // Find the current samples for the selected lane.
  // ************
  if ($run_lane_uid > 0)
  {
    // ****
    // Query combines lane samples with and without contacts, and
    // lanes without any samples.
    // ****
    $result_lane = pg_query ($dbconn, "
     SELECT run_lane_sample_uid,
            lane_number,
            load_concentration,
            batch_group,
            insert_size,
            primary_investigator.primary_investigator_uid,
            primary_investigator.name AS primary_investigator,
            contact.contact_uid,
            contact.name AS contact,
            sample_type,
            species,
            sample.sample_uid,
            sample_name,
            barcode,
            barcode_index,
            run_lane_sample.comments
       FROM run_lane,
            run_lane_sample,
            sample,
            project,
            primary_investigator,
            contact
      WHERE run_lane.run_lane_uid = $run_lane_uid AND
            run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
            run_lane_sample.sample_uid = sample.sample_uid AND
            sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid AND
            project.contact_uid = contact.contact_uid
      UNION
     SELECT run_lane_sample_uid,
            lane_number,
            load_concentration,
            batch_group,
            insert_size,
            primary_investigator.primary_investigator_uid,
            primary_investigator.name AS primary_investigator,
            0 AS contact_uid,
            ' ' AS contact,
            sample_type,
            species,
            sample.sample_uid,
            sample_name,
            barcode,
            barcode_index,
            run_lane_sample.comments
       FROM run_lane,
            run_lane_sample,
            sample,
            project,
            primary_investigator
      WHERE run_lane.run_lane_uid = $run_lane_uid AND
            run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
            run_lane_sample.sample_uid = sample.sample_uid AND
            sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid AND
            project.contact_uid IS NULL
     UNION
     SELECT -999 AS run_lane_sample_uid,
            lane_number,
            NULL AS load_concentration,
            ' ' AS batch_group,
            ' ' AS insert_size,
            0   AS primary_investigator_uid,
            ' ' AS primary_investigator,
            0   AS contact_uid,
            ' ' AS contact,
            ' ' AS sample_type,
            ' ' AS species,
            0   AS sample_uid,
            ' ' AS sample_name,
            ' ' AS barcode,
            ' ' AS barcode_index,
            ' ' AS comments
       FROM run_lane
      WHERE run_lane.run_lane_uid = $run_lane_uid AND
            run_lane_uid
        NOT IN (SELECT run_lane_uid
                  FROM run_lane_sample)
      ORDER BY sample_name");
    if (!$result_lane)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
      for ($i=0;$i<pg_num_rows($result_lane);$i++)
      {
        $row_lane = pg_fetch_assoc ($result_lane);
        // ****
        // Check if the barcode for this row is one of the
        // duplicate barcodes for this lane.
        // ****
        $td_lane_class = 'class="tdBlueBorder"';
        $link_color = "";
        foreach ($array_dup_barcode as $barcode_row)
        {
          if ($row_lane['barcode'] == $barcode_row['barcode'])
          {
             $td_lane_class = 'class="tdCaution"';
             $link_color = 'style="color: Brown;"';
             break;
          }  // if ($row_lane['barcode'] == $barcode_row['barcode'])
        }  //foreach ($array_dup_barcode as $barcode_row)
        echo '<tr>';
        // Check if this row is for a sample.
        if ($row_lane['primary_investigator_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
               $row_lane['primary_investigator_uid'],'\');" ',
               $link_color,
               ' title="Display information on primary investigator ',
               $row['name'],'." >',
               td_ready ($row_lane['primary_investigator']),'</a></td>';
        } else {
          echo '<td>&nbsp;</td>';
        }  // if ($row_lane['primary_investigator_uid'] > 0)
        if ($row_lane['contact_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" onclick="contactWindow(\'',
               $row_lane['contact_uid'],'\');" ',
               $link_color,
               ' title="Display information on contact ',
               $row['contact'],'." >',
               td_ready ($row_lane['contact']),'</a></td>';
        } else {
          echo '<td ',$td_lane_class,'>&nbsp;</td>';
        }  // if ($row_lane['contact_uid'] > 0)
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['sample_type']),'</td>';
        if ($row_lane['sample_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" onclick="sampleWindow(\'',
               $row_lane['sample_uid'],'\');" ',
               $link_color,
               ' title="Display information on sample ',
               $row['sample_name'],'." >',
               '<input type="hidden" name="run_lane_sample_uid[]" value="',
               $row_lane['run_lane_sample_uid'],'" />',
               td_ready ($row_lane['sample_name']),'</a></td>';
        } else {
          echo '<td>&nbsp;</td>';
        }  // if ($row_lane['sample_uid'] > 0)
        echo '<td style="text-align:center" ',
             $td_lane_class,'>',
             td_ready ($row_lane['barcode']),'</td>';
        echo '<td style="text-align:center" ',
             $td_lane_class,'>',
             td_ready ($row_lane['barcode_index']),'</td>';
        echo '<td style="text-align:center" ',
             $td_lane_class,'>',
             td_ready ($row_lane['load_concentration']),'</td>';
        echo '<td style="text-align:center" ',
             $td_lane_class,'>',
             td_ready ($row_lane['batch_group']),'</td>';
        echo '<td style="text-align:center" ',
             $td_lane_class,'>',
             td_ready ($row_lane['insert_size']),'</td>';
        // You are only allowed to updated comments if the lane has a sample.
        if ($row_lane['run_lane_sample_uid'] > 0)
        {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               '<textarea name="comments[',$row_lane['run_lane_sample_uid'],']" ',
               'title="Comment applies to the sample." ',
               'cols="40" rows="2" class="inputseriftext">',
               htmlentities ($row_lane['comments'], ENT_NOQUOTES),
               '</textarea></td>';
        } else {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               '&nbsp;</td>';
        }  // if ($row_lane['run_lane_sample_uid'] > 0)
        echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result_lane);$i++)
    }  //if (!$result_lane)
    } else {
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '</tr>';
  }  // if ($run_lane_uid > 0)
  unset($_SESSION['run_lane_sample_uid']);
  echo '</tbody>';
  echo '</table>';
  echo '<hr />';
  $project_uid = (isset ($_SESSION['project_uid']) ?
   $_SESSION['project_uid'] : 0);
  // Get the project name and the primary investigator name.
  if ($project_uid > 0)
  {
    $result_project = pg_query ($dbconn, "
     SELECT project_name,
            name
       FROM project,
            primary_investigator
      WHERE project.project_uid = $project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid");
    if (!$result_project)
    {
      echo '<span class="errotext">',pg_last_error ($dbconn),'</span><br />';
    } else {
      $row_project = pg_fetch_assoc ($result_project);
      echo '<h3 class="grayed_out">Project: ',
           $row_project['project_name'],
           '<br />Primary Investigator: ',
           $row_project['name'],
           '</h3>';
    }  // if (!$result_project)
  }  // if ($project_uid > 0)
  echo '<h2 class="headertext">Active ',$run_type,' Samples</h2>';
  echo '<table id="add_sample_table" border="1" width="100%" class="sortable">';
  echo '<thead><tr>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center" >Sample</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Barcode Index</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Species</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Type</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Batch Group</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Sample Comments</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  $run_lane_uid = (isset ($_SESSION['run_lane_uid']) ?
   $_SESSION['run_lane_uid'] : 0);
  $result_td = pg_query ($dbconn, "
   SELECT sample.sample_uid AS sample_uid_open,
          sample_name,
          sample.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          sample.comments
     FROM sample,
          project
    WHERE sample.project_uid = $project_uid AND
          sample.project_uid = project.project_uid AND
          sample.status = 'Active' AND
          project.ref_run_type_uid = $ref_run_type_uid AND
          sample.sample_uid NOT IN
          (SELECT run_lane_sample.sample_uid
             FROM run_lane_sample
            WHERE run_lane_uid = $run_lane_uid)
    ORDER BY sample_name");
  if (!$result_td)
  {
    echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
  } else {
      for ($i=0;$i<pg_num_rows($result_td);$i++)
      {
        $row = pg_fetch_assoc ($result_td);
        echo '<tr>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             '<a href="javascript:void(0)" onclick="sampleWindow(\'',
             $row['sample_uid_open'],
             '\');" title="Display information on sample ',
             $row['sample_name'],'.">',
             td_ready($row['sample_name']),'</a></td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['barcode']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['barcode_index']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['species']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['sample_type']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['batch_group']),'</td>';
        echo '<td class="tdBlueBorder" style="text-align:left">',
             '<div style="width: 250px; height: 30px; overflow: ',
             'auto; padding: 5px;"><font face="sylfaen">',
             td_ready($row['comments']),
             '</font></div></td>';
        echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result_td);$i++)
    unset($_SESSION['sample_uid_open']);
  }  // if (!$result_td)
  echo '</tbody></table>';
?>
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
