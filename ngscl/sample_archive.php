<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_archive_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
// Initialize variables.
$sample_list = "";
$sample_like_condition = "";
$dbconn = database_connect();
// **************************************************************
// This function returns an output array based on the input
// select condition and an array of the archive names.
// **************************************************************
function create_array_archive_display ($dbconn,
 $where_addendum, $array_archive_name)
{
  // Initialize.
  $output_array = array();
  $num_archive = count ($array_archive_name);
  // Select the samples restricted by the input conditional.
  $result_sample = pg_query($dbconn,"
   SELECT sample.sample_uid,
          project.project_uid,
          project.project_name,
          primary_investigator.primary_investigator_uid,
          primary_investigator.name AS primary_investigator_name,
          sample_name,
          sample.status
     FROM project,
          primary_investigator,
          sample
    WHERE sample.project_uid = project.project_uid AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid ".
          $where_addendum.
  " ORDER BY primary_investigator_name,
             project.project_name,
             sample.sample_name");
  if (!$result_sample)
  {
    $output_array[0] = pg_last_error($dbconn);
  } else {
    // Loop through the sample query.
    for ($i=0; $i < pg_num_rows ($result_sample); $i++)
    {
      $row_sample = pg_fetch_assoc ($result_sample);
      // Loop through the archive name array.
      foreach ($array_archive_name as $rowkey => $rowvalue)
      {
        // Add a row to the output array.
        $output_array_index = $i * $num_archive + $rowkey;
        $output_array[$output_array_index]['sample_uid'] =
         $row_sample['sample_uid'];
        $output_array[$output_array_index]['project_uid'] =
         $row_sample['project_uid'];
        $output_array[$output_array_index]['project_name'] =
         $row_sample['project_name'];
        $output_array[$output_array_index]['primary_investigator_uid'] =
         $row_sample['primary_investigator_uid'];
        $output_array[$output_array_index]['primary_investigator_name'] =
         $row_sample['primary_investigator_name'];
        $output_array[$output_array_index]['sample_name'] =
         $row_sample['sample_name'];
        $output_array[$output_array_index]['status'] =
         $row_sample['status'];
        $output_array[$output_array_index]['ref_archive_name_uid'] =
         $rowvalue['ref_archive_name_uid'];
        $output_array[$output_array_index]['archive_name'] =
         $rowvalue['archive_name'];
        $output_array[$output_array_index]['freezer'] = "";
        $output_array[$output_array_index]['shelf_number'] = "";
        $output_array[$output_array_index]['box_number'] = "";
        $output_array[$output_array_index]['box_position'] = "";
        $output_array[$output_array_index]['comments'] = "";
      }  // foreach ($array_archive_name as $rowkey => $rowvalue)
    }  // for ($i=0; $i < pg_num_rows ($result_sample); $i++)
    $num_output_array = count ($output_array);
    // Select from the archive table.
    $result_archive = pg_query ($dbconn, "
     SELECT archive_uid,
            sample.sample_uid,
            archive.ref_archive_name_uid,
            ref_archive_name.archive_name,
            freezer,
            shelf_number,
            box_number,
            box_position,
            archive.comments
       FROM project,
            primary_investigator,
            sample,
            archive,
            ref_archive_name
      WHERE archive.ref_archive_name_uid =
             ref_archive_name.ref_archive_name_uid AND
            archive.sample_uid = sample.sample_uid AND
            sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid ".
            $where_addendum.
    " ORDER BY primary_investigator.name,
               project.project_name,
               sample.sample_name,
               ref_archive_name_uid");
    // Loop through the archive query.
    if (!$result_archive)
    {
      $output_array[0] = pg_last_error($dbconn);
    } else {
      // Initialize the output array index.
      $output_array_index = 0;
      for ($i=0; $i < pg_num_rows ($result_archive); $i++)
      {
        $row_archive = pg_fetch_assoc ($result_archive);
        // Loop through the output array starting at the output array index.
        for ($j=$output_array_index; $j < $num_output_array; $j++)
        {
          // ****
          // If the sample and archive name of the archive query
          // match the output array, then add the archive values
          // to the output array.
          // ****
          if (($row_archive['sample_uid'] ==
               $output_array[$j]['sample_uid']) &&
              ($row_archive['ref_archive_name_uid'] ==
               $output_array[$j]['ref_archive_name_uid']))
          {
            $output_array[$j]['freezer'] = 
             $row_archive['freezer'];
            $output_array[$j]['shelf_number'] =
             $row_archive['shelf_number'];
            $output_array[$j]['box_number'] =
             $row_archive['box_number'];
            $output_array[$j]['box_position'] =
             $row_archive['box_position'];
            $output_array[$j]['comments'] =
             $row_archive['comments'];
            $output_array_index = $j + 1;
            break;
          }  // if (($row_archive[''] = $output_array[$j]['']) &&...
        }  // for ($j=$output_array_index; $j < $num_output_array; $j++)
      }  // for ($i=0; $i < pg_num_rows ($result_archive); $i++)
    }  // if (!$result_archive)
  }  // if (!$result_sample)
  return $output_array;
}  // function create_array_archive_display
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_update_archive']))
    {
      header("location: update_sample_archive.php");
      exit;
    } elseif (isset($_POST['submit_search'])) {
      $sample_like_condition = strtolower ($_POST['sample_search']);
    }  // if (isset($_POST['submit_update_archive']))
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Sample Archive, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="library/actb.js">
  </script>
<script language="javascript" type="text/javascript" src="library/common.js">
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
  echo '<h1 style="text-align:center"><span class="titletext">Sample Archive - ',
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
  // Find the chosen ref_archive_name_uid and archive_name.
  $ref_archive_name_value = (isset (
   $_SESSION['choose_archive_archive_select']) ?
   $_SESSION['choose_archive_archive_select'] : 1);
  if ($ref_archive_name_value != "Show All")
  {
    $result_archive_name = pg_query ($dbconn, "
     SELECT archive_name
       FROM ref_archive_name
      WHERE ref_archive_name_uid = $ref_archive_name_value");
    if (!$result_archive_name)
    {
      echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
    } else {
      $archive_name = pg_fetch_result ($result_archive_name, 0, 0);
    }  // if (!$result_ref_archive)
    echo '<h3 class="grayed_out">Archive: ',
         $archive_name,'</h3>';
  }  // if ($ref_archive_name_value != "Show All")
  // If this is the first time through, create an array of the archive names.
  if (!isset($_POST['process']))
  {
    $result_ref_archive = pg_query ($dbconn, "
     SELECT ref_archive_name_uid,
            archive_name
       FROM ref_archive_name
      ORDER BY ref_archive_name_uid");
    if (!$result_ref_archive)
    {
      echo '<span>',pg_last_error ($dbconn),'</span>';
    } else {
      for ($i=0; $i < pg_num_rows($result_ref_archive); $i++)
      {
        $row_archive_name = pg_fetch_assoc ($result_ref_archive);
        $_SESSION['array_archive_name'][$i]['ref_archive_name_uid'] =
         $row_archive_name['ref_archive_name_uid'];
        $_SESSION['array_archive_name'][$i]['archive_name'] =
         $row_archive_name['archive_name'];
      }  // for ($i=0; $i < pg_num_rows($result_ref_archive); $i++)
    }  // if (!$result_ref_archive)
  }  // if (!isset($_POST['process']))
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_archive_select" >';
  echo '<input type="hidden" name="process" value="1" />';
  if ($ref_archive_name_value != "Show All")
  {
    echo '<input type="submit" name="submit_update_archive" ',
         'value="Update Archive Records" ',
         'title="Update Sample Archive Records." class="buttontext" />&nbsp;';
  } else {
    echo '<br />';
  }  // if ($ref_archive_name_value != "Show All")
  // ****
  // This is the Sample search.
  // ****
  echo '<p style="text-align:left; vertical-align:middle;" >',
       'Sample',
       '<input type="text" name="sample_search" id="sample_search" ',
       'size="40" autocomplete="off" class="inputtext" />',
       '<input type="submit" value="Search" name="submit_search" ',
       'title="Samples containing search string (not case-sensitive)." ',
       'class="buttontext" /></p>';
  $status_value = (isset (
   $_SESSION['choose_sample_status_archive_select']) ?
   $_SESSION['choose_sample_status_archive_select'] : "");
  $status_condition = where_condition ("sample.status", $status_value, 1);
  $primary_investigator_uid_value = (isset (
   $_SESSION['choose_pi_archive_select']) ?
   $_SESSION['choose_pi_archive_select'] : "");
  $pi_condition = where_condition (
   "project.primary_investigator_uid",
   $primary_investigator_uid_value);
  $project_uid_value = (isset (
   $_SESSION['choose_project_archive_select']) ?
   $_SESSION['choose_project_archive_select'] : "");
  $project_condition = where_condition (
   "sample.project_uid",
   $project_uid_value);
  // Get all the samples of the listed project, status, and
  // primary investigator.
  // Build where condition for primary investigator select.
  $where_addendum = "";
  $where_clause = "";
  if (strlen (trim ($project_condition)) > 0)
  {
    $where_addendum = $project_condition;
  }  // if (strlen (trim ($project_condition)) > 0)
  if (strlen (trim ($status_condition)) > 0)
  {
    if (strlen (trim  ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum . " AND " . $status_condition;
    } else {
      $where_addendum = $status_condition;
    }  // if (strlen (trim  ($where_addendum)) > 0)
  }  // if (strlen (trim ($status_condition)) > 0)
  if (strlen (trim ($pi_condition)) > 0)
  {
    if (strlen (trim  ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum . " AND " . $pi_condition;
    } else {
      $where_addendum = $pi_condition;
    }  // if (strlen (trim  ($where_addendum)) > 0)
  }  // if (strlen (trim ($pi_condition)) > 0)
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_clause = " AND " . $where_addendum;
  }  // if (strlen (trim ($where_addendum)) > 0)
  $result = pg_query($dbconn,"
   SELECT sample_name
     FROM sample, project
    WHERE sample.project_uid = project.project_uid".
   $where_clause.
   " ORDER BY sample_name");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $sample_name = htmlspecialchars (
                      pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $sample_list = $sample_list . ",'" . $sample_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from pi list and enclose list in double quotes.
    $sample_list = ltrim ($sample_list, ",");
  }  // if (!$result)
  // Now add the sample search to the where condition.
  if (strlen (trim ($sample_like_condition)) > 0)
  {
    if (strlen (trim ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum .
                        " AND lower (sample.sample_name) LIKE '%" .
                        $sample_like_condition . "%'";
    } else {
      $where_addendum = "lower (sample.sample_name) LIKE '%" .
                        $sample_like_condition . "%'";
    }  // if (strlen (trim ($status_condition)) > 0)
  }  // if (strlen (trim ($sample_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $sample_list; ?>);
  var obj = actb(document.getElementById("sample_search"),customarray);
</script>
<?php
  // Make a table for the pull-down lists.
  echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
  // ****
  // This is the pull-down for Sample Status.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext" ><b>',
       'Status</b><br />';
  echo drop_down_array ('choose_sample_status_archive_select', $status_value,
                        'inputrow', $array_sample_status_values,
                        'Query by sample status.');
  echo '</td>';
  // ****
  // This is the pull-down for Primary Investigator.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Primary Investigator</b><br />';
  $primary_investigator_uid_value = (isset (
   $_SESSION['choose_pi_archive_select']) ?
   $_SESSION['choose_pi_archive_select'] : "");
  echo drop_down_table ($dbconn, 'choose_pi_archive_select',
                        $primary_investigator_uid_value,
                        'inputrow', 'primary_investigator', 
                        'primary_investigator_uid', 'name',
                        'Query by primary investigator.');
  echo '</td>';
  // ****
  // This is the pull-down for Project.
  // ****
  $project_uid_value = (isset (
   $_SESSION['choose_project_archive_select']) ?
   $_SESSION['choose_project_archive_select'] : "");
  // Limit the projects to the selected pi.
  if (strlen (trim ($pi_condition)) > 0)
  {
    // Strip off the primary investigator table name.
    $pi_array = explode (".", $pi_condition);
    $where_clause = " WHERE ".$pi_array[1];
    // See if the project value is not Show All.
    if (trimmed_string_not_empty ($project_uid_value))
    {
      if ((strcasecmp (trim ($project_uid_value), trim ("Show All")) != 0) &&
          (strlen (trim ($project_uid_value)) > 0))
      {
      // If the project does not match the pi, set the project value to blank.
      $result_count = pg_query ($dbconn, "
       SELECT COUNT(1) AS row_count
         FROM project ".
       $where_clause.
       " AND project_uid = ".
       $project_uid_value);
      if (!$result_count)
      {
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        if ($line = pg_fetch_assoc ($result_count))
        {
           if ($line['row_count'] < 1)
           {
             $project_uid_value = "Show All";
           }  // if ($line['row_count'] < 1)
        } else {
          echo '<span>',pg_last_error($dbconn),'</span>';
          }  // if ($line = pg_fetch_assoc ($result_count))
        }  // if ((strcasecmp (trim ($project_uid_value), trim ("Show All"))...
      }  // if (!$result_count)
    }  // if (trimmed_string_not_empty ($project_uid_value))
  } else {
    $where_clause = "";
  }  // if (strlen (trim ($pi_condition)) > 0)
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Project</b><br />';
  echo drop_down_table ($dbconn, 'choose_project_archive_select',
                        $project_uid_value,
                        'inputrow', 'project', 
                        'project_uid', 'project_name',
                        'Query by project.',  $where_clause);
  echo '</td>';
  // ****
  // This is the pull-down for Archive.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Archive</b><br />';
  echo drop_down_table ($dbconn, 'choose_archive_archive_select',
                        $ref_archive_name_value,
                        'inputrow', 'ref_archive_name', 
                        'ref_archive_name_uid', 'archive_name',
                        'Query by archive type.');
  echo '</td>';
  echo '</tr>';
  echo '</tbody></table>';
  $archive_condition = where_condition ('archive.ref_archive_name_uid',
                                         $ref_archive_name_value);
  // Make a table for the population inputs.
  if ($ref_archive_name_value != "Show All")
  {
    echo '<table id="sample_table" name="sample_table"',
         'border="1" width="100%" class="sortable">';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="sorttable_nosort" scope="col" width="2%" ',
         'style="text-align:center;" > ',
         '<input type="checkbox" name="submit_check_all" ',
         'onclick="checkAllTable(\'sample_table\',this)" ',
         'title="Select all items." />',
         '</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center" >Sample</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center" >Project</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center" >PI</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center">Sample Status</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center">Freezer</th>';
    echo '<th class="sorttable_numeric" scope="col" ',
         'style="text-align:center">Shelf Number</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center">Box Number</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center">Box Position</th>';
    echo '<th class="sorttable_alpha" scope="col" ',
         'style="text-align:center">Archive Comments</th>';
  } else {
    echo '<table id="sample_table" name="sample_table" ',
         'border="1" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center" >Sample</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center" >Project</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center" >PI</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Sample Status</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Archive</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Freezer</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Shelf Number</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Box Number</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Box Position</th>';
    echo '<th class="thSmallerBlueBorder" scope="col" ',
         'style="text-align:center">Archive Comments</th>';
  }  // if ($ref_archive_name_value != "Show All")
?>
  </tr>
</thead>
<tbody>
<?php
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_clause = " AND " . $where_addendum;
  } else {
    $where_clause = "";
  }  // if (strlen (trim ($where_addendum)) > 0)
  if ($ref_archive_name_value != "Show All")
  {
    // Make a clause to query by archive type, as required.
    if ($ref_archive_name_value != "Show All")
    {
      $join_addendum = " AND ".$archive_condition;
    } else {
      $join_addendum = " ";
    }  // if ($ref_archive_name_value != "Show All")
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
         ON sample.sample_uid = archive.sample_uid ".
            $join_addendum.
    " WHERE sample.project_uid = project.project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid ".
            $where_clause.
    " ORDER BY primary_investigator_name,
               project_name,
               sample_name,
               ref_archive_name_uid");
    if (!$result_td)
    {
      echo '<tr>',pg_last_error ($dbconn),'</tr>';
    } else {
      for ($i=0;$i<pg_num_rows($result_td);$i++)
      {
        $row = pg_fetch_assoc ($result_td);
        echo '<tr>';
        $checked_string = "";
        // Determine whether the sample checkbox should be checked.
        $sample_uid = $row['sample_uid'];
        // If sample was previously selected, mark checkbox as checked.
        if (isset($_SESSION['sample_uid_archive'])) {
          foreach ($_SESSION['sample_uid_archive']
                    as $checkbox_sample_uid)
          {
            if ($sample_uid == $checkbox_sample_uid)
            {
              $checked_string = "checked";
            }  // if ($sample_uid == $sample) {
          }  // foreach ($_SESSION['sample_uid_archive'] as $sample) {
        }  // if (isset($_SESSION['sample_uid_archive'])) {
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             '<input type="checkbox" name="sample_uid_archive[]" ',
             $checked_string,
             ' title="Select sample ',
             $row['sample_name'],
             '." value="',
             $row['sample_uid'],
             '"/></td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             '<a href="javascript:void(0)" onclick="sampleWindow(\'',
             $row['sample_uid'],
             '\');" title="Display information on sample ',
             $row['sample_name'],'.">',
             td_ready($row['sample_name']),'</a></td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             '<a href="javascript:void(0)" onclick="projectWindow(\'',
             $row['project_uid'],'\');" ',
             'title="Display information on project ',
             $row['project_name'],'.">',
             td_ready($row['project_name']),'</a></td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center"><a ',
             'href="javascript:void(0)" ',
             'onclick="primary_investigatorWindow(\'',
             $row['primary_investigator_uid'],'\');" ',
             'title="Display information on primary investigator ',
             $row['primary_investigator_name'],'.">',
             td_ready($row['primary_investigator_name']),'</a></td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['status']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['freezer']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['shelf_number']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['box_number']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($row['box_position']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:left">',
             '<div style="width: 150px; height: 40px; overflow: ',
             'auto; padding: 5px;"><font face="sylfaen">',
             htmlentities ($row['comments']),
             '</font></div></td>';
        echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result_td);$i++)
      unset($_SESSION['sample_uid_archive']);
    }  // if (!$result_td)
  } else {
      $array_archive_name = $_SESSION['array_archive_name'];
      $array_archive_display = create_array_archive_display ($dbconn,
       $where_clause, $array_archive_name);
      // If archive is Show All then table is display only.
      foreach ($array_archive_display as $mykey => $myrowvalue)
      {
        echo '<tr>';
        $mykey_mod_3 = $mykey % 3;
        if ($mykey_mod_3  == 0)
        {
          echo '<td class="tdSmallerBlueBorder" style="text-align:center" ',
               'rowspan="3">',
               '<a href="javascript:void(0)" onclick="sampleWindow(\'',
               $myrowvalue['sample_uid'],
               '\');" title="Display information on sample ',
               $myrowvalue['sample_name'],'." >',
               td_ready($myrowvalue['sample_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center" ',
               'rowspan="3">',
               '<a href="javascript:void(0)" onclick="projectWindow(\'',
               $myrowvalue['project_uid'],'\');" ',
               'title="Display information on project ',
               $myrowvalue['project_name'],'.">',
               td_ready($myrowvalue['project_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center" ',
               'rowspan="3"><a href="javascript:void(0)" ',
               'onclick="primary_investigatorWindow(\'',
               $myrowvalue['primary_investigator_uid'],'\');" ',
               'title="Display information on primary investigator ',
               $myrowvalue['primary_investigator_name'],'.">',
               td_ready($myrowvalue['primary_investigator_name']),'</a></td>';
          echo '<td class="tdSmallerBlueBorder" style="text-align:center" ',
               'rowspan="3">',
               td_ready($myrowvalue['status']),'</td>';
        }  // if ($mykey_mod_3  == 0)
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($myrowvalue['archive_name']),'</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($myrowvalue['freezer']),
             '</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($myrowvalue['shelf_number']),
             '</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($myrowvalue['box_number']),
             '</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:center">',
             td_ready($myrowvalue['box_position']),
             '</td>';
        echo '<td class="tdSmallerBlueBorder" style="text-align:left">',
             '<div style="width: 150px; height: 40px; overflow: ',
             'auto; padding: 5px;"><font face="sylfaen">',
             htmlentities ($myrowvalue['comments']),
             '</font></div></td>';
      echo '</tr>';
      }  // foreach ($array_archive_display as $mykey => $myrowvalue)
  }  // if ($ref_archive_name_value != "Show All")
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
