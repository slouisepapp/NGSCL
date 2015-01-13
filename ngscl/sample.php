<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_sample_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
require_once 'user_view.php';
// Initialize variables.
$sample_list = "";
$sample_like_condition = "";
$prep_notes_title = 'List the library prep notes for the selected sample.';
$dbconn = database_connect();
$error = "";
$info_message = "";
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_prep_notes']))
    {
      if (isset ($_SESSION['sample_uid']))
      {
        $result_note_count = pg_query ($dbconn, "
         SELECT COUNT(1) AS row_count
           FROM library_prep_note_sample
          WHERE sample_uid = ".
                 $_SESSION['sample_uid']);
        if (!$result_note_count)
        {
          $error = pg_last_error ($dbconn);
        } else {
          if ($line = pg_fetch_assoc ($result_note_count))
          {
             if ($line['row_count'] < 1)
             {
               $info_message = 'This sample has no library prep notes.';
             } else {
               header("location: sample_prep_note.php");
               exit;
             }  // if ($line['row_count'] < 1)
          } else {
            $error = pg_last_error($dbconn);
          }  // if ($line = pg_fetch_assoc ($result_note_count))
        }  // if (!$result_note_count)
      } else {
        $error = 'No sample selected.';
      }  // if (isset ($_SESSION['sample_uid']))
    } elseif (isset($_POST['submit_search'])) {
      $sample_like_condition = strtolower ($_POST['sample_search']);
    }  // if (isset($_POST['submit_prep_notes']))
  } else {
    unset ($_SESSION['sample_uid']);
  }  // if ($_POST['process'] == 1)
} else {
  unset ($_SESSION['sample_uid']);
}  // if (isset($_POST['process']))
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Sample, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 style="text-align:center"><span class="titletext">Sample - ',
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
  if (strlen (trim ($error)) > 0)
  {
    echo '<span class="errortext">',$error,'</span><br />';
  }  // if (strlen (trim ($error)) > 0)
  if (strlen (trim ($info_message)) > 0)
  {
    echo '<span class="errortext">',$info_message,'</span><br />';
  }  // if (strlen (trim ($info_message)) > 0)
  echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
       'name="form_sample_select" >';
  echo '<input type="hidden" name="process" value="1" />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_prep_notes" ',
         'value="List Prep Notes" title="',
         $prep_notes_title,
         '" class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
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
   $_SESSION['choose_sample_status_sample_select']) ?
   $_SESSION['choose_sample_status_sample_select'] : "");
  $status_condition = where_condition ("$sample_view.status", $status_value, 1);
  if ($_SESSION['app_role'] == 'pi_user')
  {
    $primary_investigator_uid_value = $_SESSION['search_pi_uid'];
  } else {
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_sample_select']) ?
     $_SESSION['choose_pi_sample_select'] : "");
  }  // if ($_SESSION['app_role'] == 'pi_user')
  $pi_condition = where_condition (
   "$project_view.primary_investigator_uid",
   $primary_investigator_uid_value);
  $project_uid_value = (isset (
   $_SESSION['choose_project_sample_select']) ?
   $_SESSION['choose_project_sample_select'] : "");
  $project_condition = where_condition (
   "$sample_view.project_uid",
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
  // Now add the sample search to the where condition.
  if (strlen (trim ($sample_like_condition)) > 0)
  {
    if (strlen (trim ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum .
                        " AND lower ($sample_view.sample_name) LIKE '%" .
                        $sample_like_condition . "%'";
    } else {
      $where_addendum = "lower ($sample_view.sample_name) LIKE '%" .
                        $sample_like_condition . "%'";
    }  // if (strlen (trim ($status_condition)) > 0)
  }  // if (strlen (trim ($sample_like_condition)) > 0)
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_clause = " AND " . $where_addendum;
  }  // if (strlen (trim ($where_addendum)) > 0)
  $sample_query = "
   SELECT sample_uid,
          $sample_view.project_uid,
          $project_view.primary_investigator_uid,
          sample_name,
          project_name,
          name,
          $sample_view.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          concentration,
          volume,
          rna_integrity_number
     FROM $sample_view,
          $project_view,
          $primary_investigator_view
    WHERE $sample_view.project_uid = $project_view.project_uid AND
          $project_view.primary_investigator_uid =
           $primary_investigator_view.primary_investigator_uid " .
          $where_clause.
    " ORDER BY sample_name, project_name";
  $result_name = pg_query($dbconn, $sample_query);
  if (!$result_name)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result_name);$i++)
    {
      $row_sample = pg_fetch_assoc ($result_name);
      $sample_name = htmlspecialchars (
                      $row_sample['sample_name'], ENT_QUOTES);
      $sample_list = $sample_list . ",'" . $sample_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result_name);$i++)
    // Remove first comma from pi list and enclose list in double quotes.
    $sample_list = ltrim ($sample_list, ",");
  }  // if (!$result_name)
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
  $status_value = (isset (
   $_SESSION['choose_sample_status_sample_select']) ?
   $_SESSION['choose_sample_status_sample_select'] : "");
  echo drop_down_array ('choose_sample_status_sample_select', $status_value,
                        'inputrow', $array_sample_status_values,
                        'Query by sample status.');
  echo '</td>';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // ****
    // This is the pull-down for Primary Investigator.
    // ****
    echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
         'Primary Investigator</b><br />';
    echo drop_down_table ($dbconn, 'choose_pi_sample_select',
                          $primary_investigator_uid_value,
                          'inputrow', $primary_investigator_view,
                          'primary_investigator_uid', 'name',
                          'Query by primary investigator.');
    echo '</td>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  // ****
  // This is the pull-down for Project.
  // ****
  $project_uid_value = (isset (
   $_SESSION['choose_project_sample_select']) ?
   $_SESSION['choose_project_sample_select'] : "");
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
           FROM $project_view ".
         $where_clause.
         " AND project_uid = ".
         $project_uid_value);
        if (!$result_count)
        {
          echo '<span class="errortext">',pg_last_error($dbconn),
               '</span><br />';
        } else {
          if ($line = pg_fetch_assoc ($result_count))
          {
             if ($line['row_count'] < 1)
             {
               $project_uid_value = "Show All";
             }  // if ($line['row_count'] < 1)
          } else {
            echo '<span class="errortext">',pg_last_error($dbconn),
                 '</span><br />';
          }  // if ($line = pg_fetch_assoc ($result_count))
        }  // if (!$result_count)
      }  // if ((strcasecmp (trim ($project_uid_value), trim ("Show All"))...
    }  // if (trimmed_string_not_empty ($project_uid_value))
  } else {
    $where_clause = "";
  }  // if (strlen (trim ($pi_condition)) > 0)
  echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
       'Project</b><br />';
  echo drop_down_table ($dbconn, 'choose_project_sample_select',
                        $project_uid_value,
                        'inputrow', $project_view, 
                        'project_uid', 'project_name',
                        'Query by project.', $where_clause);
  echo '</td>';
  $project_condition = where_condition (
   "project.project_uid",
   $project_uid_value);
  echo '</tr></tbody></table>';
  echo '<br />';
  echo '<table id="sample_table" border="1" class="sortable" >',
       '<thead>',
       '<tr>';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<th class="sortable_nosort" scope="col" width="200" ',
         'style="text-align:center" >&nbsp;</th>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
?>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Project</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    PI</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Status</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Barcode</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
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
    echo '<th class="sorttable_numeric" scope="col" width="200" ',
         'style="text-align:center">',
         'Concentration</th>';
    echo '<th class="sorttable_numeric" scope="col" width="200" ',
         'style="text-align:center">',
         'Volume</th>';
    echo '<th class="sorttable_numeric" scope="col" width="200" ',
         'style="text-align:center">',
         'RIN</th>';
  }  // if ($use_sample_bonus_columns)
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
  $set_sample_in_query = 0;
  $result_sample = pg_query($dbconn, $sample_query);
  if (!$result_sample)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0;$i<pg_num_rows($result_sample);$i++)
    {
      $row = pg_fetch_assoc ($result_sample);
      // If the project is set, check its radio.
      $checked_string = '';
      if (isset ($_SESSION['sample_uid']))
      {
        if ($_SESSION['sample_uid'] == $row['sample_uid'])
        {
          $checked_string = 'checked="checked"';
          $set_sample_in_query = 1;
        }  // if ($_POST['sample_uid']))
      }  // if ($_SESSION['sample_uid'] == $row['sample_uid'])
      echo '<tr>';
      if ($_SESSION['app_role'] != 'pi_user')
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             '<input type="radio" name="sample_uid" value="',
             $row['sample_uid'],
             '" title="Selects this sample." ',
             $checked_string,
             ' /></td>';
      }  // if ($_SESSION['app_role'] != 'pi_user')
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="sampleWindow(\'',
           $row['sample_uid'],'\');" ',
           'title="Display information on sample ',
           $row['sample_name'],'.">',
           td_ready($row['sample_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="projectWindow(\'',
           $row['project_uid'],'\');" ',
             'title="Display information on project ',
           $row['project_uid'],'.">',
           td_ready($row['project_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row['name'],'.">',
           td_ready($row['name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['status']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['barcode']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['barcode_index']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['species']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['sample_type']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['batch_group']),
           '</td>';
      if ($use_sample_bonus_columns)
      {
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['concentration']),
             '</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['volume']),
             '</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['rna_integrity_number']),
             '</td>';
      }  // if ($use_sample_bonus_columns)
      echo '</tr>';
    }  // for ($i=0;$i<pg_num_rows($result_sample);$i++)
  }  // if (!$result_sample)
  // If the session sample is not in the table, unset it.
  if ($set_sample_in_query == 0)
  {
    unset ($_SESSION['sample_uid']);
  }  // if ($set_sample_in_query == 0)
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
