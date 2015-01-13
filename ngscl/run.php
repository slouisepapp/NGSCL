<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_run_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once 'db_fns.php';
require_once 'constants.php';
require_once 'run_functions.php';
require_once 'user_view.php';
// Initialize variables.
$run_list = "";
$run_number_name_like_condition = "";
$dbconn = database_connect();
$error = "";
// **************************************************************
// This function returns the FROM and WHERE clause
// for the query of run information, restricted by
// primary_investigator_uid and ref_run_type_uid.
// **************************************************************
function run_from_and_where_clause (
 $primary_investigator_uid, $ref_run_type_uid, $default_value="Show All")
{
  require 'user_view.php';
  $from_clause = 'FROM ' . $run_view . ', ref_run_type';
  $where_clause = ' WHERE ' .
                  $run_view .
                  '.ref_run_type_uid = ref_run_type.ref_run_type_uid';
  // Modify from and where clause based on value of ref_run_type_uid variable.
  if (strlen (trim ($ref_run_type_uid)) > 0)
  {
    // Condition string is empty if variable name is Show All or -1.
    if (strcasecmp (trim($ref_run_type_uid), trim($default_value)) != 0)
    {
       $where_clause .= ' AND ' .
                       $run_view . '.ref_run_type_uid = ' . $ref_run_type_uid;
    }  // if (strcasecmp (trim($ref_run_type_uid), trim($default_value)) != 0)
  }  // if (strlen (trim ($ref_run_type_uid)) > 0)
  // Condition string is empty if variable name is empty.
  if (strlen (trim ($primary_investigator_uid)) > 0)
  {
    // Condition string is empty if variable name is Show All or -1.
    if (strcasecmp (trim($primary_investigator_uid), trim($default_value)) != 0)
    {
      $from_clause .= ',' . $run_lane_view .
                      ',' . $run_lane_sample_view .
                      ',' . $sample_view .
                      ',' . $project_view;
      $where_clause .= ' AND ' .
                       $run_view . '.run_uid = ' .
                       $run_lane_view . '.run_uid AND ' .
                       $run_lane_view . '.run_lane_uid = ' .
                       $run_lane_sample_view . '.run_lane_uid AND ' .
                       $run_lane_sample_view . '.sample_uid = ' .
                       $sample_view . '.sample_uid AND ' .
                       $sample_view . '.project_uid = ' .
                       $project_view . '.project_uid AND ' .
                       'primary_investigator_uid = ' .
                       $primary_investigator_uid;
    } // if (strcasecmp (trim($primary_investigator_uid), trim("SHOW ALL"))...
  }  // if (strlen (trim ($primary_investigator_uid)) > 0)
  $clause_string = $from_clause . $where_clause;
  return $clause_string;
}  // function run_from_and_where_clause 
$run_uid = (isset ($_SESSION['run_uid']) ?
 $_SESSION['run_uid'] : 0);
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_view']))
    {
      if ($run_uid > 0)
      {
        header("location: run_details.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_lanes'])) {
      if ($run_uid > 0)
      {
        header("location: manage_lane_active_samples.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_new_run'])) {
      header("location: new_run.php");
      exit;
    } elseif (isset($_POST['submit_copy_run'])) {
      if ($run_uid > 0)
      {
        header("location: copy_run.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_delete_run'])) {
      if ($run_uid > 0)
      {
        // ****
        // Allow delete only if the run has no samples in any lane.
        // ****
        $result_run = pg_query ($dbconn, "
         SELECT COUNT(1) AS row_count
           FROM $run_lane_view,
                $run_lane_sample_view
          WHERE $run_lane_view.run_uid = $run_uid AND
                $run_lane_view.run_lane_uid =
                 $run_lane_sample_view.run_lane_uid");
        if (!$result_run)
        {
          $error = pg_last_error($dbconn);
        } else {
          if ($line_run = pg_fetch_assoc ($result_run))
          {
            if ($line_run['row_count'] < 1) 
            {
              // Delete run.
              header("location: process_delete_run.php");
              exit;
            } else {
              $error = 'Project cannot be deleted as there are '.
                       'some samples in the lanes.';
            }  // if ($line_run['row_count'] < 1) 
          } else {
            $error = pg_last_error ($dbconn);
          }  // if ($line_run = pg_fetch_assoc ($result_run))
        }  // if (!$result_run)
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_search'])) {
      $run_number_name_like_condition = strtoLower ($_POST['run_number_name_search']);
    } elseif (isset($_POST['submit_qa'])) {
      if ($run_uid > 0)
      {
        if(isset($_SESSION['missing_response']))
          unset($_SESSION['missing_response']);
        header("location: post_run_qa_data.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_notification'])) {
      if ($run_uid > 0)
      {
        $_SESSION['calling_page'] = 'run.php';
        header("location: run_notification.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_billing'])) {
      if ($run_uid > 0)
      {
        header("location: billing.php");
        exit;
      } else {
        $error = "No run selected.";
      }  // if ($run_uid > 0)
    }  // if (isset($_POST['submit_view']))
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
  $primary_investigator_uid = (isset ($_SESSION['primary_investigator_uid']) ?
   $_SESSION['primary_investigator_uid'] : 0);
  // If session run does not accord with the session pi, unset the run.
  if ($primary_investigator_uid > 0 && $run_uid > 0)
  {
    // Get the pi for all the samples in this run.
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM $run_view,
            $run_lane_view,
            $run_lane_sample_view,
            $sample_view,
            $project_view
      WHERE $run_view.run_uid = $run_uid AND
            $run_view.run_uid = $run_lane_view.run_uid AND
            $run_lane_view.run_lane_uid =
             $run_lane_sample_view.run_lane_uid AND
            $run_lane_sample_view.sample_uid = $sample_view.sample_uid AND
            $sample_view.project_uid = $project_view.project_uid AND
            primary_investigator_uid = $primary_investigator_uid");
    if (!$result)
    {
      $error = pg_last_error ($dbconn);
    } else {
      if ($line = pg_fetch_assoc ($result))
      { 
        if ($line['row_count'] < 1)
        {
          unset ($_SESSION['run_uid']);
        }  // if ($line['row_count'] < 1)
      }  // if ($line = pg_fetch_assoc ($result))
    }  // if (!$result)
  }  // if ($primary_investigator_uid > 0 && $run_uid > 0)
}  // if (isset($_POST['process']))
/* initialize_home(); */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo '<title>Run, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 style="text-align:center"><span class="titletext">Run - ',
       $app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
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
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_run_select" >
<input type="hidden" name="process" value="1" />
<?php
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // Check for runs with project that have the wrong run type.
    $mismatch_string = run_type_mismatch ($dbconn);
    if (strlen (trim ($mismatch_string)) > 0);
    {
      echo '<span class="errortext">',$mismatch_string,'</span><br />';
    }  // if (strlen (trim ($mismatch_string)) > 0);
    echo '<h2>Manage Run</h2>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<input type="submit" name="submit_view" value="Run Details" ',
       'title="View run details." class="buttontext" />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_lanes" value="Manage Lane Samples" ',
         'title="Add or remove samples from lanes." class="buttontext" />';
    echo '<input type="submit" name="submit_new_run" value="New Run" ',
         'title="Create new run." class="buttontext" />';
    echo '<input type="submit" name="submit_copy_run" value="Copy Run" ',
         'title="Copy selected run." class="buttontext" />';
    echo '<input type="submit" name="submit_delete_run" value="Delete Run" ',
         'onclick="return confirm(\'Are you sure you want to delete this run?\');" ',
         'title="Delete selected run." ',
         'class="buttontext" />';
    echo '<h2>Post-Run</h2>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<input type="submit" name="submit_qa" ',
       'value="QA Data" ',
       'title="Post-run QA data for samples and undetermined indices." ',
       'class="buttontext" />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<h2>Reports</h2>';
    echo '<input type="submit" name="submit_notification" ',
         'value="Notification" ',
         'title="Notify those users on the run mailing list." ',
         'class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '<input type="submit" name="submit_billing" ',
       'value="Billing" ',
       'title="Display billing information." ',
       'class="buttontext" />';
  // ****
  // This is the Run Number and Name search.
  // ****
  echo '<p style="text-align:left; vertical-align:middle;" >',
       'Run Number/Name',
       '<input type="text" name="run_number_name_search" ',
       'id="run_number_name_search" ',
       'size="40" autocomplete="off" class="inputtext" />',
       '<input type="submit" value="Search" name="submit_search" ',
       'title="Run number or run name containing search string ',
       '(not case-sensitive)."',
       'class="buttontext" /></p>';
  if ($_SESSION['app_role'] == 'pi_user')
  {
    $primary_investigator_uid_value = $_SESSION['search_pi_uid'];
  } else {
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_run_select']) ?
     $_SESSION['choose_pi_run_select'] : "");
  }  // if ($_SESSION['app_role'] == 'pi_user')
  $select_run_type_value = (isset (
   $_SESSION['choose_run_type_project_select']) ?
   $_SESSION['choose_run_type_project_select'] : "");
  $from_and_where_clause = run_from_and_where_clause (
   $primary_investigator_uid_value, $select_run_type_value);
  // Get all the runs which have samples of the primary investigator.
  $result = pg_query($dbconn,"
   SELECT run_number || '/' || run_name AS run_number_name " .
    $from_and_where_clause.
   " GROUP BY run_number, run_name ORDER BY run_number");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $run_number_name = htmlspecialchars (
                          pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $run_list = $run_list . ",'" . $run_number_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from run list and enclose list in double quotes.
    $run_list = ltrim ($run_list, ",");
  }  // if (!$result)
  // Now add the run number and name search to the where condition.
  if (strlen (trim ($run_number_name_like_condition)) > 0)
  {
    if (strrpos (strtoupper ($from_and_where_clause), "WHERE"))
    {
      $from_and_where_clause .= " AND lower (".
       "run_number || '/' || run_name) LIKE '%".
       $run_number_name_like_condition . "%'";
    } else {
      $from_and_where_clause .= " WHERE lower (".
       "run_number || '/' || run_name) LIKE '%".
       $run_number_name_like_condition . "%'";
    }  // if (strrpos (strtoupper ($from_and_where_clause), "WHERE"))
  }  // if (strlen (trim ($run_number_name_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $run_list; ?>);
  var obj = actb(document.getElementById("run_number_name_search"),customarray);
</script>
<?php
  // ****
  // This is the pull-down for Run Type.
  // ****
  echo '<table id="pull_down_table" class="tableNoBorder"><tbody><tr>';
  echo '<td style="text-align: left; margin: 2px;" class="smallertext" ><b>',
       'Run Type</b><br />';
  $select_run_type_value = (isset (
   $_SESSION['choose_run_type_project_select']) ?
   $_SESSION['choose_run_type_project_select'] : "");
  echo drop_down_table ($dbconn, "choose_run_type_project_select",
                        $select_run_type_value,
                        "inputrow", "ref_run_type",
                        "ref_run_type_uid", "run_type",
                        "Query by run type.");
  echo '</td>';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // ****
    // This is the pull-down for Primary Investigator.
    // ****
    echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
         'Primary Investigator</b><br />';
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_run_select']) ?
     $_SESSION['choose_pi_run_select'] : "");
    echo drop_down_table ($dbconn, 'choose_pi_run_select',
                          $primary_investigator_uid_value,
                          'inputrow', $primary_investigator_view, 
                          'primary_investigator_uid', 'name',
                          'Query for runs with samples of this '.
                          'primary investigator.');
    echo '</td>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '</tr></tbody></table>';
?>
<br />
<table id="run_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_nosort" scope="col" width="30" >&nbsp;
    </th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Run Number/Name</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Run Type</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Read Type</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Read 1</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Read 2</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Indexing Read Length</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Cluster Gen Began</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Sequencing Began</th>
  </tr>
</thead>
<tbody>
<?php
  $result = pg_query($dbconn,"
   SELECT DISTINCT $run_view.run_uid,
          run_number,
          run_number || '/' || run_name AS run_number_name,
          ref_run_type.run_type,
          read_type,
          read_1_length,
          read_2_length,
          read_length_indexing,
          cluster_gen_start_date,
          sequencing_start_date ".
     $from_and_where_clause.
    " ORDER BY run_number DESC");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    $set_project_in_query = 0;
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // If the project is set, check its radio.
      $checked_string = '';
      if ($run_uid > 0)
      {
        if ($run_uid == $row['run_uid'])
        {
          $checked_string = 'checked="checked"';
          $set_project_in_query = 1;
        }  // if ($run_uid == $row['run_uid'])
      }  // if ($run_uid > 0)
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<input type="radio" name="run_uid" value="',
           $row['run_uid'],
           '" title="Selects this project" ',
           $checked_string,
           ' /></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="runWindow(\'',
           $row['run_uid'],'\');" ',
           'title="Display information on run ',
           $row['run_number_name'],'.">',
           td_ready($row['run_number_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['run_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['read_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['read_1_length']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['read_2_length']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['read_length_indexing']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['cluster_gen_start_date']),
           '</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['sequencing_start_date']),
           '</td>';
      echo '</tr>';
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    if ($set_project_in_query == 0)
    {
      unset ($_SESSION['run_uid']);
    }  // if ($set_project_in_query == 0)
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
  echo '<p style="text-align:center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
