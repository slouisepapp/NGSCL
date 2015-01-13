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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Active Samples for Project, ',$abbreviated_app_name,'</title>';
?>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<link href="DAC_LIMS_styles.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<style type="text/css"> 
/* place css fixes for all versions of IE in this conditional comment */
.oneColElsLtHdr #mainContent { zoom: 1; padding-top: 15px; }
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

<body class="oneColElsLtHdr">
<div class="style1" id="container">
  <div id="header">
<?php
  echo '<h1 style="text-align: center"><span class="titletext">',
       'Active Samples for Project - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  if (isset ($_GET['project_uid']) &&
      trimmed_string_not_empty ($_GET['project_uid']))
  {
    $project_uid = $_GET['project_uid'];
    $result_name = pg_query ($dbconn, "
     SELECT project_name,
            name
       FROM project,
            primary_investigator
      WHERE project_uid = $project_uid AND
            project.primary_investigator_uid =
             primary_investigator.primary_investigator_uid");
    if (! $result_name)
    {
      $project_name = "";
      $pi_name = "";
    } else {
      $project_name = pg_fetch_result ($result_name, 0, 0);
      $pi_name = pg_fetch_result ($result_name, 0, 1);
    }  // if (!$result_name)
    echo '<h3 class="grayed_out">Project: ',$project_name,'</h3>';
    echo '<h3 class="grayed_out">Primary Investigator: ',$pi_name,'</h3>';
    echo '<table id="sample_table" border="1" class="sortable">';
    echo '<thead><tr>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center" >',
         'Sample</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Sample Description</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Barcode</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Species</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Type</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Batch Group</th>';
    echo '<th class="sorttable_alpha" scope="col" width="200" ',
         'style="text-align:center">',
         'Comments</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    $result = pg_query($dbconn,"
     SELECT sample_uid,
            sample.project_uid,
            project.primary_investigator_uid,
            sample_name,
            sample_description,
            barcode,
            species,
            sample_type,
            batch_group,
            sample.comments
       FROM sample,
            project,
            primary_investigator
      WHERE project.project_uid = $project_uid AND
            sample.project_uid = project.project_uid AND
            upper(sample.status) = 'ACTIVE' AND
             project.primary_investigator_uid =
            primary_investigator.primary_investigator_uid
      ORDER BY sample_name");
    if (!$result)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
      for ($i=0;$i<pg_num_rows($result);$i++)
      {
        $row = pg_fetch_assoc ($result);
        echo '<tr>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['sample_name']),'</td>';
        echo ' <td class="tdBlueBorder" style="text-align:left">',
         '<div style="width: 100px; height: 40px; overflow: auto; ',
         'padding: 5px;"><font face="sylfaen">',
         td_ready($row['sample_description']),
         '</font></div></td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['barcode']),
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
         echo '<td class="tdBlueBorder" style="text-align:left">',
              '<div style="width: 250px; height: 40px; overflow: ',
              'auto; padding: 5px;"><font face="sylfaen">',
              td_ready($row['comments']),
              '</font></div></td>';
         echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result);$i++)
    }  // if (!$result)
    echo '</tbody></table>';
  }  // if (isset ($_GET['project_uid']) &&...
?>
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
