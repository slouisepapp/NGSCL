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
    if (isset($_POST['submit_manage_samples']))
    {
      header("location: manage_prep_note_samples.php");
      exit;
    }  // if (isset($_POST['submit_manage_samples']))
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Library Prep Note Details, ',$abbreviated_app_name,'</title>';
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
  <div id="header">
<?php
  echo '<h1 style="text-align: center"><span class="titletext">',
       'Library Prep Note Details - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
$library_prep_note_uid = (isset ($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
echo '<div id="mainContent">';
if ($library_prep_note_uid < 1)
  echo '<span class="errortext">No library prep note.</span><br />';
?>
  <iframe src="prep_note_info.php" width="80%" 
   style="border: 2px solid blue; height: 190px; " >
   <p>Your browser does not support iframes.</p>
  </iframe><br /><br />
  <iframe src="prep_note_image.php" width="45%" 
   style="border: 2px solid blue; height: 40px; " >
   <p>Your browser does not support iframes.</p>
  </iframe><br /><br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_samples" >
<input type="hidden" name="process" value="1" />
<input type="submit" name="submit_manage_samples" value="Manage Samples"
 title="Manage samples for library prep_note." class="buttontext" />
<br /><br />
<table id="sample_table" border="1" class="sortable">
<thead>
  <tr>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Project Name</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    PI</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Sample Status</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
    Barcode</th>
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center" >
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
    <th class="sorttable_alpha" scope="col" width="200"
     style="text-align:center">
    Comments</th>
  </tr>
</thead>
<tbody>
<?php
if ($library_prep_note_uid > 0)
{
  $result = pg_query ($dbconn, "
   SELECT library_prep_note_sample.sample_uid,
          sample.project_uid,
          project.primary_investigator_uid,
          sample_name,
          project_name,
          name AS primary_investigator_name,
          sample.status,
          barcode,
          barcode_index,
          species,
          sample_type,
          batch_group,
          sample.comments
     FROM library_prep_note_sample,
          sample,
          project,
          primary_investigator
    WHERE library_prep_note_uid = $library_prep_note_uid AND
          library_prep_note_sample.sample_uid = sample.sample_uid AND
          sample.project_uid = project.project_uid AND
          project.primary_investigator_uid =
           primary_investigator.primary_investigator_uid
    ORDER BY primary_investigator_name, project_name, sample_name");
  if (!$result)
  {
    echo '<tr><td class="tdError">',pg_last_error($dbconn),'</td></tr>';
  } else {
    for ($i=0; $i < pg_num_rows($result); $i++)
    {
      $row_sample = pg_fetch_assoc ($result);
      $sample_uid = $row_sample['sample_uid'];
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           '<a href="javascript:void(0)" onclick="sampleWindow(\'',
           $row_sample['sample_uid'],'\');" ',
           'title="Display information on sample ',
           $row_sample['sample_name'],'.">',
           td_ready($row_sample['sample_name']),'</a></td>';
             echo '<td class="tdBlueBorder" style="text-align:center">',
                  '<a href="javascript:void(0)" onclick="projectWindow(\'',
                  $row_sample['project_uid'],'\');" ',
                  'title="Display information on project ',
                  $row_sample['project_name'],'.">',
                  td_ready($row_sample['project_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center"><a ',
           'href="javascript:void(0)" onclick="primary_investigatorWindow(\'',
           $row_sample['primary_investigator_uid'],'\');" ',
           'title="Display information on primary investigator ',
           $row_sample['primary_investigator_name'],'.">',
           td_ready($row_sample['primary_investigator_name']),'</a></td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['status']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['barcode_index']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['species']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['sample_type']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row_sample['batch_group']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:left">',
           '<div style="width: 150px; height: 40px; overflow: ',
           'auto; padding: 5px;"><font face="sylfaen">',
           td_ready($row_sample['comments']),
           '</font></div></td>';
      echo '</tr>';
    }  // for ($i=0; $i < pg_num_rows($result); $i++)
  }  // if (!$result)
}  // if ($library_prep_note_uid > 0)
echo '</tbody>';
echo '</table></form><br />';
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
