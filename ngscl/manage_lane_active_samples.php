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
$_SESSION['run_number_name'] = "";
$_SESSION['run_type'] = "";
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
    $ref_run_type_uid = pg_fetch_result ($result, 0, 1);
    $_SESSION['run_type'] = pg_fetch_result ($result, 0, 2);
  }  // if (!$result)
} else {
  $array_error[] = "No run selected.";
}  // if ($run_uid > 0)
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_return_to_details']))
    {
      header("location: run_details.php");
      exit;
    } elseif (isset($_POST['submit_show_all_samples'])) {
      header("location: manage_lane_all_samples.php?sticky_lane=".
             $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_remove_samples'])) {
      // Delete the selected samples from the lane.
      if (isset ($_SESSION['run_lane_uid']) &&
          isset ($_SESSION['run_lane_sample_uid']))
      {
        $array_error = remove_from_lane ($dbconn,
         $_SESSION['run_lane_uid'], $_SESSION['run_lane_sample_uid']);
      }  // if (isset ($_SESSION['run_lane_uid']) &&
      unset($_POST['submit_remove_samples']);
    } elseif (isset($_POST['submit_update_comments'])) {
      header("location: update_lane_comments_active_all.php?sticky_lane=".
      $_SESSION['lane_number']);
      exit;
    } elseif (isset($_POST['submit_notification'])) {
      $_SESSION['calling_page'] = 'manage_lane_active_samples.php';
      header("location: run_notification.php?sticky_lane=" .
              $_SESSION['lane_number']);
      exit;
    }  // if (isset($_POST['submit_return_to_details']))
    // Set lane number and run_lane_uid according to pull-down menu.
    if (isset ($_SESSION['choose_lane_number']) &&
        trimmed_string_not_empty ($_SESSION['choose_lane_number']))
    {
      $_SESSION['lane_number'] = $_SESSION['choose_lane_number'];
    } else {
      $_SESSION['lane_number'] = 1;
    }  // if (isset ($_SESSION['choose_lane_number']) &&...
    $lane_number = $_SESSION['lane_number'];
  }  // if ($_POST['process'] == 1)
} else {
  // ****
  // On first accessing this page, get the lane passed
  // from another of the set of pages that manages lanes
  // or set the lane to one.
  // ****
  if (isset($_GET) &&
      isset($_GET['sticky_lane']) &&
      trimmed_string_not_empty ($_GET['sticky_lane']))
  {
    $_SESSION['lane_number'] = $_GET['sticky_lane'];
  } else {
    $_SESSION['lane_number'] = 1;
  }  // if (isset($_GET) &&...
  $lane_number = $_SESSION['lane_number'];
}  // if (isset($_POST['process']))
// Find run_lane_uid for this run and lane.
$run_lane_uid = find_run_lane ($dbconn, $run_uid, $lane_number);
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Manage Run Lanes, ',$abbreviated_app_name,'</title>';
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
       'Manage Run Lanes - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  echo '<h3 class="grayed_out">Run: ',$_SESSION['run_number_name'],'</h3>';
  echo '<h3 class="grayed_out">Run Type: ',$_SESSION['run_type'],'</h3>';
  // ****
  // This is the pull-down for Lane Number.
  // ****
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_manage_lane_samples" >';
  echo '<table id=pull_down_table" class="tableNoBorder"><tbody><tr>';
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Lane</b><br />';
  echo '<select name="choose_lane_number" onchange="this.form.submit();" ',
       ' title="Choose lane to manage." class="pulldowntext" >';
  for ($lane_iteration=1; $lane_iteration <= $num_run_lanes; $lane_iteration++)
  {
    if ($lane_iteration == $lane_number)
    {
      echo '<option value="',
           $lane_iteration,
           '" class="inputrow" selected="selected" >Lane ',
           $lane_iteration,
           '</option>';
    } else {
      echo '<option value="',
           $lane_iteration,
           '" class="inputrow" >Lane ',
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
  // Look for duplicate barcodes.
  if ($run_lane_uid > 0)
  {
    $array_dup_barcode = dup_barcodes_in_lane ($dbconn, $run_lane_uid);
  }  // if ($run_lane_uid > 0)
  // Check for samples in the run with the wrong run type.
  $mismatch_string = run_sample_type_mismatch ($dbconn, $run_uid);
  if (strlen (trim ($mismatch_string)) > 0);
  {
    echo '<span class="errortext">',$mismatch_string,'</span><br />';
  }  // if (strlen (trim ($mismatch_string)) > 0);
  foreach ($array_dup_barcode as $barcode_row)
  {
    echo '<span class="cautiontext">' .
         $barcode_row['barcode_caution'] .
         '</span><br />';
  }  // foreach ($array_dup_barcode as $barcode_row)
  echo '<input type="hidden" name="process" value="1" />';
  echo '<input type="submit" name="submit_notification" ',
       'value="Notification" ',
       'title="Notify those users on the run mailing list." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_return_to_details" ',
       'value="Run Details" class="buttontext" ',
       'title="Return to Run Details page." />';
  echo '<hr />';
  // Make a table for the submit buttons.
  echo '<input type="submit" name="submit_remove_samples" class="buttontext" ',
       'value="Remove from Lane" ',
       'title="Remove selected samples from lane for this run." />&nbsp;';
  echo '<input type="submit" name="submit_update_comments" class="buttontext" ',
       'value="Update Comments" ',
       'title="Update the lane sample comments." />';
  // Make a table for the lane samples.
  echo '<h2 class="headertext">Samples in Lane</h2>';
  echo '<table id="sample_table" name="sample_table" ',
       'border="1" width="100%" class="sortable" >';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="sorttable_nosort" scope="col" width="2%" ',
       'style="text-align:center;" > ',
       '<input type="checkbox" name="submit_check_all" ',
       'onclick="checkAllTable(\'sample_table\',this)" ',
       'title="Select all items." />',
       '</th>';
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
       'style="text-align:center" >Batch Group</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center" >Insert Size</th>';
  echo '<th class="sorttable_alpha" scope="col" ',
       'style="text-align:center">Lane Comment</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // ************
  // Find the current samples for the selected lane.
  // ************
  if ($run_lane_uid > 0)
  {
    $_SESSION['run_lane_uid'] = $run_lane_uid;
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
        echo '<tr >';
        // Check if this row is for a sample.
        if ($row_lane['run_lane_sample_uid'] > 0)
        {
          $checked_string = "";
          // Determine whether the sample checkbox should be checked.
          if (isset($_POST['process']))
          {
            if ($_POST['process'] == 1)
            {
              // If sample was previously selected, mark checkbox as checked.
              if (isset($_SESSION['run_lane_sample_uid']))
              {
                foreach ($_SESSION['run_lane_sample_uid'] as $checkbox_uid)
                {
                  if ($row_lane['run_lane_sample_uid'] == $checkbox_uid)
                  {
                    $checked_string = "checked";
                  }  // if ($row_lane['run_lane_sample_uid'] == $checkbox_uid)
                }  // foreach ($_SESSION['run_lane_sample_uid'] as...
              }  // if (isset($_SESSION['run_lane_sample_uid']))
            }  // if ($_POST['process'] == 1)
          }  // if (isset($_POST['process']))
          echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
               '<input type="checkbox" name="run_lane_sample_uid[]" ',
               $checked_string,
               ' value="',
               $row_lane['run_lane_sample_uid'],
               '" title="Select sample ',$row_lane['sample_name'],'."/></td>';
        } else {
          echo '<td class="tdBlueBorder" style="text-align:center">',
               '&nbsp;</td>';
        }  // if ($row_lane['run_lane_sample_uid'] > 0)
        if ($row_lane['primary_investigator_uid'] > 0)
        {
          echo '<td ',$td_lane_class,
               ' style="text-align:center"><a ',
               'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
               $row_lane['primary_investigator_uid'],'\');" ',
               $link_color,
               ' title="Display information on primary investigator ',
               $row_lane['primary_investigator'],'." >',
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
               $row_lane['contact'],'." >',
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
               $row_lane['sample_name'],'." >',
               td_ready ($row_lane['sample_name']),'</a></td>';
        } else {
          echo '<td>&nbsp;</td>';
        }  // if ($row_lane['sample_uid'] > 0)
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['barcode']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['barcode_index']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['load_concentration']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['batch_group']),'</td>';
        echo '<td ',
             $td_lane_class,
             ' style="text-align:center">',
             td_ready ($row_lane['insert_size']),'</td>';
        // You are only allowed to update comments if the lane has a sample.
        if ($row_lane['run_lane_sample_uid'] > 0)
        {
          echo '<td ',
               $td_lane_class,
               ' style="text-align:center">',
               '<div style="width: 250px; height: 30px; overflow: auto; ',
               'padding: 5px;"><font face="sylfaen">',
               td_ready ($row_lane['comments']),
               '</font></div></td>';
        } else {
          echo '<td ',
               $td_lane_class,
               ' style="text-align:center">',
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
      echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
      echo '</tr>';
  }  // if (!$result_run_lane)
  echo '</tbody>';
  echo '</table>';
  echo '<hr />';
  echo '<input type="submit" name="submit_show_all_samples" ',
       'value="Show All Samples" class="buttontext" ',
       'title="Show all samples not in the selected lane." />';
  // Make a table for the pull-down lists.
  echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
  echo '<tr>';
  // ****
  // This is the pull-down for Primary Investigator.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="headertext"><b>',
       'Primary Investigator</b><br />';
  $primary_investigator_uid_value = (isset ($_SESSION['choose_pi']) ?
   $_SESSION['choose_pi'] : "");
  echo drop_down_table ($dbconn, 'choose_pi', $primary_investigator_uid_value,
                        'inputrow', 'primary_investigator', 
                        'primary_investigator_uid', 'name',
                        'Query by primary investigator.');
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  $pi_condition = where_condition (
   "primary_investigator.primary_investigator_uid",
   $primary_investigator_uid_value);
  echo '</tr>';
  echo '</tbody></table>';
  echo '<h2 class="headertext">Active ',$_SESSION['run_type'],' Samples</h2>';
  echo '<table id="work_schedule_table" border="1" class="sortable">';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="sorttable_alpha" scope="col" width="200" ',
       'style="text-align:center" >',
       'Prepped and Ready</th>';
  echo '<th class="sorttable_alpha" scope="col" width="200" ',
       'style="text-align:center" >',
       'PI</th>';
  echo '<th class="sorttable_alpha" scope="col" width="200" ',
       'style="text-align:center" >',
       'Project</th>';
  echo '<th class="sorttable_numeric" scope="col" width="200" ',
       'style="text-align:center" >',
       'Number of Samples</th>';
  echo '<th class="sorttable_alpha" scope="col" width="200"',
       'style="text-align:center">',
       'Project Prep Comments</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  // Make a clause to query by pi as required.
  $where_addendum = "";
  if (strlen (trim ($pi_condition)) > 0)
  {
    $where_addendum = $where_addendum." AND ".$pi_condition;
  }  // if (strlen (trim ($pi_condition)) > 0)
  $result = pg_query($dbconn,"
   SELECT project.project_uid,
          CASE WHEN project.prepped_and_ready THEN '&#10003'
               ELSE '&nbsp;'
          END AS prepped_and_ready_check,
          primary_investigator.primary_investigator_uid,
          name,
          project_name,
          COUNT(1) AS sample_count,
          project_prep_comments
     FROM primary_investigator,
          project,
          sample
    WHERE UPPER (project.status) = 'ACTIVE' AND
          UPPER (sample.status) = 'ACTIVE' AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid ".
          $where_addendum." AND
          project.project_uid = sample.project_uid AND
          project.ref_run_type_uid = $ref_run_type_uid
    GROUP BY project.project_uid,
          prepped_and_ready,
          project_name,
          primary_investigator.primary_investigator_uid,
          name,
          project_prep_comments
    ORDER BY name, project_name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center"><b>',
           $row['prepped_and_ready_check'],
           '</b></td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row['name'],'.">',
           td_ready($row['name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="projectWindow(\'',
           $row['project_uid'],'\');" ',
           'title="Display information on project ',
           $row['project_name'],'.">',
           td_ready($row['project_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
       '<a href="manage_lane_project_active_samples.php?project_uid=',
       $row['project_uid'],
       '&sticky_lane=',
       $_SESSION['lane_number'],
       '" title="Link to active samples for project.">',
       $row['sample_count'],'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:left">
       <div style="width: 200px; height: 40px; overflow: auto; padding: 5px;">
       <font face="sylfaen">'.
       td_ready($row['project_prep_comments']).'</font></div></td>
       </tr>';
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
  }  // if (!$result)
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