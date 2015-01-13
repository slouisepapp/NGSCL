<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_update']))
    {
      header("location: update_work_schedule.php");
      exit;
    } elseif (isset($_POST['project_uid'])) {
      header("location: project_details.php");
      exit;
    }  // if (isset($_POST['submit_update']))
  }  // if ($_POST['process'] == 1)
} else {
  unset ($_SESSION['project_uid']);
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
  echo '<title>Work Schedule, ',$abbreviated_app_name,'</title>';
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
       'Work Schedule - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
?>
  <div id="mainContent">
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_work_schedule">
<input type="hidden" name="process" value="1" />
<input type="submit" name="submit_update" value="Update Work Schedule"
 title="Update project prep comments." class="buttontext" /><br />
<h2> Active Projects with Active Samples</h2>
<table id="work_schedule_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_nosort" scope="col" width="200" 
     style="text-align:center" >
    Project Details</th>
    <th class="sorttable_alpha" scope="col" width="200" 
     style="text-align:center" >
    Prepped and Ready</th>
    <th class="sorttable_alpha" scope="col" width="200" 
     style="text-align:center" >
    Run Type</th>
    <th class="sorttable_alpha" scope="col" width="200" 
     style="text-align:center" >
    Assigned To</th>
    <th class="sorttable_alpha" scope="col" width="200" 
     style="text-align:center" >
    PI</th>
    <th class="sorttable_alpha" scope="col" width="200" 
     style="text-align:center" >
    Project</th>
    <th class="sorttable_numeric" scope="col" width="200" 
     style="text-align:center" >
    Number of Samples</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Project Prep Comments</th>
  </tr>
</thead>
<tbody>
<?php
  $result = pg_query($dbconn,"
   SELECT project.project_uid,
          CASE WHEN project.prepped_and_ready THEN '&#10003'
               ELSE '&nbsp;'
          END AS prepped_and_ready_check,
          ref_run_type.run_type,
          project.assigned_to,
          primary_investigator.primary_investigator_uid,
          name,
          project_name,
          COUNT(1) AS sample_count,
          project_prep_comments
     FROM primary_investigator,
          project,
          sample,
          ref_run_type
    WHERE UPPER (project.status) = 'ACTIVE' AND
          UPPER (sample.status) = 'ACTIVE' AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid AND
          project.project_uid = sample.project_uid AND
          project.ref_run_type_uid =
           ref_run_type.ref_run_type_uid
    GROUP BY project.project_uid,
          prepped_and_ready,
          run_type,
          project.assigned_to,
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
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<input type="radio" onclick="this.form.submit();" ',
           'name="project_uid" value="',
           $row['project_uid'],
           '" title="Manage project details for ',
           $row['project_name'],
           '." </td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><b>',
           $row['prepped_and_ready_check'],
           '</b></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['run_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['assigned_to']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row['name'],'.">',
           td_ready($row['name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['project_name']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
       '<a href="javascript:void(0)" onclick="activeSamplesWindow(\'',
       $row['project_uid'],'\');" ',
       'title="Active samples for project.">',
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
