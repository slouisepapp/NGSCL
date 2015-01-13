<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_project_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once 'db_fns.php';
require_once 'constants.php';
require 'user_view.php';
// Initialize variables.
$project_list = "";
$project_like_condition = "";
$selected_project_uid = 0;
$dbconn = database_connect();
$error = "";
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    $selected_project_uid = (isset ($_SESSION['project_uid']) ?
     $_SESSION['project_uid'] : 0);
    if (isset($_POST['submit_view']))
    {
      if (isset ($_POST['project_uid']))
      {
        header("location: project_details.php");
        exit;
      } else {
        $error = "No project selected.";
      }  // if (isset ($_POST['project_uid']))
    } elseif (isset($_POST['submit_log'])) {
      if (isset ($_POST['project_uid']))
      {
        header("location: project_log.php");
        exit;
      } else {
        $error = "No project selected.";
      }  // if (isset ($_POST['project_uid']))
    } elseif (isset($_POST['submit_new_project'])) {
      header("location: new_project.php");
      exit;
    } elseif (isset($_POST['submit_delete_project'])) {
      if ($selected_project_uid > 0)
      {
        // ****
        // Allow delete only if the project has no archived samples and
        // is not part of a run.
        // ****
        // Check if project is part of a run.
        $result_run = pg_query ($dbconn, "
         SELECT COUNT(1) AS row_count
           FROM sample,
                run_lane_sample
          WHERE sample.project_uid = $selected_project_uid AND
                sample.sample_uid = run_lane_sample.sample_uid");
        if (!$result_run)
        {
          $error = pg_last_error ($dbconn);
        } else {
          if ($line_run = pg_fetch_assoc ($result_run))
          {
            if ($line_run['row_count'] < 1) 
            {
              // Check if project has archived samples.
              $result_archive = pg_query ($dbconn, "
               SELECT COUNT(1) AS row_count
                 FROM sample s,
                      archive a
                WHERE s.project_uid = $selected_project_uid AND
                      s.sample_uid = a.sample_uid");
              if (!$result_archive)
              {
                $error = pg_last_error ($dbconn);
              } else {
                if ($line_archive = pg_fetch_assoc ($result_archive))
                {
                  if ($line_archive['row_count'] < 1) 
                  {
                    // Delete project.
                    header("location: process_delete_project.php");
                    exit;
                  } else {
                    $error = 'Project cannot be deleted as some samples '.
                             'are archived.';
                  }  // if ($line_archive['row_count'] < 1) 
                } else {
                  $error = pg_last_error ($dbconn);
                }  // if ($line_archive = pg_fetch_assoc ($result_archive))
              }  // if (!$result_archive)
            } else {
              $error = 'Project cannot be deleted as some samples '.
                       'are part of a run.';
            }  // if ($line_run['row_count'] < 1) 
          } else {
            $error = pg_last_error ($dbconn);
          }  // if ($line_run = pg_fetch_assoc ($result_run))
        }  // if (!$result_run)
      } else {
        $error = "No project selected.";
      }  // if ($selected_project_uid > 0)
    } elseif (isset($_POST['submit_search'])) {
      $project_like_condition = strtolower ($_POST['project_search']);
    }  // if (isset($_POST['submit_view']))
  } else {
    $selected_project_uid = (isset ($_SESSION['project_uid']) ?
     $_SESSION['project_uid'] : 0);
  }  // if ($_POST['process'] == 1)
} else {
  clear_drop_down_vars();
  // If session project does not accord with the session pi, unset the project.
  $primary_investigator_uid = (isset ($_SESSION['primary_investigator_uid']) ?
   $_SESSION['primary_investigator_uid'] : 0);
  $selected_project_uid = (isset ($_SESSION['project_uid']) ?
   $_SESSION['project_uid'] : 0);
  if ($primary_investigator_uid > 0 && $selected_project_uid > 0)
  {
    $result = pg_query ($dbconn, "
     SELECT COUNT(1) AS row_count
       FROM $project_view
      WHERE project_uid = $selected_project_uid AND
            primary_investigator_uid = $primary_investigator_uid");
    if (!$result)
    {
      $error = pg_last_error ($dbconn);
    } else {
      if ($line = pg_fetch_assoc ($result))
      { 
        if ($line['row_count'] < 1)
        {
          unset ($_SESSION['project_uid']);
        }  // if ($line['row_count'] < 1)
      }  // if ($line = pg_fetch_assoc ($result))
    }  // if (!$result)
  }  // if (isset ($_SESSION['primary_investigator_uid']) &&...
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
echo '<title>Project, ',$abbreviated_app_name,'</title>';
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
  echo '<h1 style="text-align:center"><span class="titletext">Project - ',
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
 name="form_project_select" >
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_view" value="Project Details" ',
       'title="View project detail and project samples." class="buttontext" />';
  echo '<input type="submit" name="submit_log" value="Project Log" ',
       'title="Manage project log." class="buttontext" />';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_new_project" value="New Project" ',
         'title="Create new project." class="buttontext" />';
    echo '<input type="submit" name="submit_delete_project" value="Delete Project" ',
         'onclick="return confirm(\'Are you sure you want to delete this project?\');" ',
         'title="Delete project and all associated samples." ',
         'class="buttontext" />';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  // ****
  // This is the Project search.
  // ****
  echo '<p style="text-align:left; vertical-align:middle;" >',
       'Project',
       '<input type="text" name="project_search" id="project_search" ',
       'size="40" autocomplete="off" class="inputtext" />',
       '<input type="submit" value="Search" name="submit_search" ',
       'title="Projects containing search string (not case-sensitive)."',
       'class="buttontext" /></p>';
  // Create where clause for run type.
  $select_run_type_value = (isset (
   $_SESSION['choose_run_type_project_select']) ?
   $_SESSION['choose_run_type_project_select'] : "");
  $run_type_condition = where_condition (
   "$project_view.ref_run_type_uid", $select_run_type_value);
  // Create where clause for status.
  $status_value = (isset (
   $_SESSION['choose_project_status_project_select']) ?
   $_SESSION['choose_project_status_project_select'] : "");
  $status_condition = where_condition (
   "$project_view.status", $status_value, 1);
  // Create where clause for status.
  $status_value = (isset (
   $_SESSION['choose_project_status_project_select']) ?
   $_SESSION['choose_project_status_project_select'] : "");
  $status_condition = where_condition (
   "$project_view.status", $status_value, 1);
  // Create where clause for primary investigator.
  if ($_SESSION['app_role'] == 'pi_user')
  {
    $primary_investigator_uid_value = $_SESSION['search_pi_uid'];
  } else {
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_project_select']) ?
     $_SESSION['choose_pi_project_select'] : "");
  }  // if ($_SESSION['app_role'] != 'pi_user')
  $pi_condition = where_condition (
   "$project_view.primary_investigator_uid",
   $primary_investigator_uid_value);
  // Get all the projects of the listed status and primary investigator.
  // Build where condition for primary investigator select.
  $where_addendum = "";
  $where_clause = "";
  if (strlen (trim ($pi_condition)) > 0)
  {
    $where_addendum = $pi_condition;
  }  // if (strlen (trim ($pi_condition)) > 0)
  // Add the run type where clause to the where addendum.
  if (strlen (trim ($run_type_condition)) > 0)
  {
    if (strlen (trim  ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum . " AND " . $run_type_condition;
    } else {
      $where_addendum = $run_type_condition;
    }  // if (strlen (trim  ($where_addendum)) > 0)
  }  // if (strlen (trim ($status_condition)) > 0)
  // Add the status where clause to the where addendum.
  if (strlen (trim ($status_condition)) > 0)
  {
    if (strlen (trim  ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum . " AND " . $status_condition;
    } else {
      $where_addendum = $status_condition;
    }  // if (strlen (trim  ($where_addendum)) > 0)
  }  // if (strlen (trim ($status_condition)) > 0)
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_clause = " WHERE " . $where_addendum;
  }  // if (strlen (trim ($where_addendum)) > 0)
  $result = pg_query($dbconn,"
   SELECT project_name
     FROM $project_view ".
   $where_clause.
   " ORDER BY project_name");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error ($dbconn),'</span><br />';
  } else {
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $project_name = htmlspecialchars (
                       pg_fetch_result ($result, $i, 0), ENT_QUOTES);
      $project_list = $project_list . ",'" . $project_name . "'";
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    // Remove first comma from pi list and enclose list in double quotes.
    $project_list = ltrim ($project_list, ",");
  }  // if (!$result)
  // Now add the project search to the where condition.
  if (strlen (trim ($project_like_condition)) > 0)
  {
    if (strlen (trim ($where_addendum)) > 0)
    {
      $where_addendum = $where_addendum .
                        " AND lower ($project_view.project_name) LIKE '%" .
                        $project_like_condition . "%'";
    } else {
      $where_addendum = " lower ($project_view.project_name) LIKE '%" .
                        $project_like_condition . "%'";
    }  // if (strlen (trim ($status_condition)) > 0)
  }  // if (strlen (trim ($project_like_condition)) > 0)
?>
<script language="javascript" type="text/javascript">
  var customarray = new Array(<?php echo $project_list; ?>);
  var obj = actb(document.getElementById("project_search"),customarray);
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
  // ****
  // This is the pull-down for Project Status.
  // ****
  echo '<td style="text-align: left; margin: 2px;" class="smallertext" ><b>',
       'Project Status</b><br />';
  $status_value = (isset (
   $_SESSION['choose_project_status_project_select']) ?
   $_SESSION['choose_project_status_project_select'] : "");
  echo drop_down_array ('choose_project_status_project_select', $status_value,
                        'inputrow', $array_project_status_values,
                        'Query by project status.');
  echo '</td>';
  if ($_SESSION['app_role'] != 'pi_user')
  {
    // ****
    // This is the pull-down for Primary Investigator.
    // ****
    echo '<td style="text-align: left; margin: 2px;" class="smallertext"><b>',
         'Primary Investigator</b><br />';
    $primary_investigator_uid_value = (isset (
     $_SESSION['choose_pi_project_select']) ?
     $_SESSION['choose_pi_project_select'] : "");
    echo drop_down_table ($dbconn, 'choose_pi_project_select',
                          $primary_investigator_uid_value,
                          'inputrow', $primary_investigator_view, 
                          'primary_investigator_uid', 'name',
                          'Query by primary investigator.');
    echo '</td>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  echo '</tr></tbody></table>';
?>
<br />
<table id="project_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_nosort" scope="col" width="30" >&nbsp;
    </th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Project</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Run Type</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Primary Investigator</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Project Status</th>
    <th class="sorttable_date" scope="col" width="200"
     style="text-align:center">
    Creation Date</th>
  </tr>
</thead>
<tbody>
<?php
  if (strlen (trim ($where_addendum)) > 0)
  {
    $where_addendum = " AND " . $where_addendum;
  }  // if (strlen (trim ($where_addendum)) > 0)
  $result = pg_query($dbconn,"
   SELECT project_uid,
          $primary_investigator_view.primary_investigator_uid,
          name,
          project_name,
          ref_run_type.run_type,
          $project_view.status,
          creation_date
     FROM $project_view,
          $primary_investigator_view,
          ref_run_type
    WHERE $project_view.primary_investigator_uid =
           $primary_investigator_view.primary_investigator_uid AND
          $project_view.ref_run_type_uid = ref_run_type.ref_run_type_uid ".
          $where_addendum.
  " ORDER BY name, creation_date, project_name");
  if (!$result)
  {
    echo '<tr>',pg_last_error ($dbconn),'</tr>';
  } else {
    $set_project_in_query = 0;
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      // If the project is set, check its radio.
      $checked_string = '';
      if (($selected_project_uid > 0) &&
          ($selected_project_uid == $row['project_uid']))
      {
        $checked_string = 'checked="checked"';
        $set_project_in_query = 1;
      }  // if (($selected_project_uid > 0) &&...
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<input type="radio" name="project_uid" value="',
           $row['project_uid'],
           '" title="Selects this project" ',
           $checked_string,
           ' /></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="projectWindow(\'',
           $row['project_uid'],'\');" ',
           'title="Display information on project ',
           $row['project_name'],'.">',
           td_ready($row['project_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['run_type']),
           '</td>';
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
           td_ready($row['creation_date']),
           '</td>';
      echo '</tr>';
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    if ($set_project_in_query == 0)
    {
      unset ($_SESSION['project_uid']);
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
