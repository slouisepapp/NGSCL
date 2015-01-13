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
require_once('run_functions.php');
$dbconn = database_connect();
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_table']))
    {
      $run_uid = (isset ($_SESSION['run_uid']) ?
                  $_SESSION['run_uid'] : 0);
      // Update the lane information.
      for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
      {
        $post_key = $lane_number - 1;
        if (isset($_POST['total_reads'][$lane_number]))
        {
          $total_reads = ddl_ready ($_POST['total_reads'][$lane_number]);
          // If run lane exists, update.
          if ($_POST['run_lane_uid'][$post_key] > 0)
          {
            $result_lane_update = pg_query ($dbconn, "
               UPDATE run_lane
                  SET total_reads = '$total_reads'
                WHERE run_uid = $run_uid AND
                      lane_number = $lane_number");
            if (!$result_lane_update)
              $array_error[] = '<p>Lane '.$post_key.':'.
                               pg_last_error ($dbconn).'</p>';
          } else {
            // If run lane does not exist, create it.
            if ($run_uid > 0)
            {
              $result_lane_insert = pg_query ($dbconn, "
               INSERT INTO run_lane
                (run_uid, lane_number, total_reads)
               VALUES
                ($run_uid, $lane_number, '$total_reads')");
              if (!$result_lane_insert)
              {
                $array_error[] = '<p>Lane '.$lane_number.': '.
                                 pg_last_error ($dbconn).'</p>';
              }  // if (!$result_lane_insert)
            }  // if ($run_uid > 0)
          }  // if ($_POST['run_lane_uid'][$post_key] > 0)
        }  // if (isset($_POST['total_reads'][$lane_number]))
      }  // for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
      // Update the sample information.
      // Update lane sample comments.
      foreach ($_POST['comments'] as $run_lane_sample_uid => $sample_comment)
      {
        $ready_sample_comment = ddl_ready ($sample_comment);
        $result_lane_update = pg_query ($dbconn, "
         UPDATE run_lane_sample
            SET comments = '$ready_sample_comment'
          WHERE run_lane_sample_uid = $run_lane_sample_uid");
        if (!$result_lane_update)
          $array_error[] = pg_last_error ($dbconn);
      }  // foreach ($_POST['comments'] as $run_lane_sample_uid =>...
      // Update insert size.
      foreach ($_POST['insert_size'] as $run_lane_sample_uid => $insert_size)
      {
        $ready_insert_size = ddl_ready ($insert_size);
        $result_lane_update = pg_query ($dbconn, "
         UPDATE run_lane_sample
            SET insert_size = '$ready_insert_size'
          WHERE run_lane_sample_uid = $run_lane_sample_uid");
        if (!$result_lane_update)
          $array_error[] = pg_last_error ($dbconn);
      }  // foreach ($_POST['insert_size'] as $run_lane_sample_uid =>...
      // Update load concentration.
      foreach ($_POST['load_concentration'] as
                $run_lane_sample_uid => $load_concentration)
      {
        if (strlen (trim ($load_concentration)) > 0)
        {
          if (is_numeric ($load_concentration))
          {
            $result_lane_update = pg_query ($dbconn, "
             UPDATE run_lane_sample
                SET load_concentration = '$load_concentration'
              WHERE run_lane_sample_uid = $run_lane_sample_uid");
            if (!$result_lane_update)
              $array_error[] = pg_last_error ($dbconn);
          }  // if (strlen (trim ($load_concentration])) > 0)
        } else {
          $result_lane_update = pg_query ($dbconn, "
           UPDATE run_lane_sample
              SET load_concentration = NULL
            WHERE run_lane_sample_uid = $run_lane_sample_uid");
          if (!$result_lane_update)
            $array_error[] = pg_last_error ($dbconn);
        }  // if (strlen (trim ($load_concentration)) > 0)
      }  // foreach ($_POST['load_concentration'] as
      // Return to the run details page.
      header("location: run_details.php");
      exit;
    } elseif (isset($_POST['submit_run_details'])) {
        header("location: run_details.php");
        exit;
    }  // if (isset($_POST['submit_table']))
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
  echo '<title>Run Details, ',$abbreviated_app_name,'</title>';
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
       'Run Details - ',$app_name,'</span></h1>';
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
  <iframe src="run_info.php" width="550px;" 
   style="border: 2px solid blue; height: 360px;" >
   <p>Your browser does not support iframes.</p>
  </iframe><br /><br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_lane_samples" >
<input type="hidden" name="process" value="1" />
<?php
  // Check for samples in the run with the wrong run type.
  $mismatch_string = run_sample_type_mismatch ($dbconn, $_SESSION['run_uid']);
  if (strlen (trim ($mismatch_string)) > 0);
  {
    echo '<span class="errortext">',$mismatch_string,'</span><br />';
  }  // if (strlen (trim ($mismatch_string)) > 0);
  echo '<input type="submit" name="submit_table" value="Save" ',
   'title="Save updated lane and sample information." ',
   'class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
   'title="Reset lane and sample information to original values." ',
   'class="buttontext" />';
  echo '<input type="submit" name="submit_run_details" value="Quit" ',
   'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
   'title="Return to Run Details page without saving." class="buttontext" />';
?>
<p class="updateabletext"><i>* Updateable Fields</i></p>
<table id="sample_table" border="1" width="100%" class="nosort">
<thead>
  <tr>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Flowcell Lane</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    # of Samples</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Total Reads</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Project</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Primary Investigator</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Contact</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Sample Type</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Species</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Sample</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Barcode</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Barcode Index</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Batch Group</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Insert Size</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Load Concentration</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Lane Comment</th>
  </tr>
</thead>
<tbody>
<?php
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
if ($run_uid > 0)
{
  // Loop through all the run lanes.
  for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
  {
    $key_number = $lane_number - 1;
    // ****
    // Select for the sample lane information using a UNION.
    // The select returns:
    //  1) Samples with fields in all the tables of the FROM clause.
    //  2) Samples belonging to a project that does not have a contact.
    //  3) A row if the lane has no samples.
    // ****
    $result_lane = pg_query ($dbconn, "
      SELECT run_lane.run_lane_uid,
             run_lane_sample.run_lane_sample_uid,
             run_lane.total_reads,
             project.project_uid,
             project.project_name,
             primary_investigator.primary_investigator_uid,
             primary_investigator.name AS pi_name,
             contact.contact_uid,
             contact.name AS contact_name,
             sample.sample_type,
             sample.species,
             sample.sample_uid,
             sample.sample_name,
             sample.barcode,
             sample.barcode_index,
             sample.batch_group,
             insert_size,
             load_concentration,
             run_lane_sample.comments
        FROM run,
             run_lane,
             run_lane_sample,
             sample,
             project,
             primary_investigator,
             contact
       WHERE lane_number = $lane_number AND
             run_lane.run_uid = $run_uid AND
             run.run_uid = run_lane.run_uid AND
             run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
             run_lane_sample.sample_uid = sample.sample_uid AND
             sample.project_uid = project.project_uid AND
             project.primary_investigator_uid =
              primary_investigator.primary_investigator_uid AND
             project.contact_uid = contact.contact_uid
       UNION
      SELECT run_lane.run_lane_uid,
             run_lane_sample.run_lane_sample_uid,
             run_lane.total_reads,
             project.project_uid,
             project.project_name,
             primary_investigator.primary_investigator_uid,
             primary_investigator.name AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             sample.sample_type,
             sample.species,
             sample.sample_uid,
             sample.sample_name,
             sample.barcode,
             sample.barcode_index,
             sample.batch_group,
             run_lane_sample.insert_size,
             run_lane_sample.load_concentration,
             run_lane_sample.comments
        FROM run,
             run_lane,
             run_lane_sample,
             sample,
             project,
             primary_investigator
       WHERE lane_number = $lane_number AND
             run_lane.run_uid = $run_uid AND
             run.run_uid = run_lane.run_uid AND
             run_lane.run_lane_uid = run_lane_sample.run_lane_uid AND
             run_lane_sample.sample_uid = sample.sample_uid AND
             sample.project_uid = project.project_uid AND
             project.primary_investigator_uid =
              primary_investigator.primary_investigator_uid AND
             project.contact_uid IS NULL
       UNION
      SELECT run_lane.run_lane_uid,
             -999 AS run_lane_sample_uid,
             run_lane.total_reads,
             0   AS project_uid,
             ' ' AS project_name,
             0   AS primary_investigator_uid,
             ' ' AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             ' ' AS sample_type,
             ' ' AS species,
             0   AS sample_uid,
             ' ' AS sample_name,
             ' ' AS barcode,
             ' ' AS barcode_index,
             ' ' AS batch_group,
             ' ' AS insert_size,
             0 AS load_concentration,
             ' ' AS comments
        FROM run_lane
       WHERE lane_number = $lane_number AND
             run_lane.run_uid = $run_uid AND
             run_lane_uid 
      NOT IN (SELECT run_lane_uid
                FROM run_lane_sample
               WHERE run_uid = $run_uid)
       ORDER BY project_name, sample_name");
    if (!$result_lane)
    {
      echo '<tr><td class="tdError">',pg_last_error ($dbconn),'</td></tr>';
    } else {
      $rowspan = pg_num_rows ($result_lane);
      // Create a row if the lane has database values.
      if ($rowspan > 0)
      {
        for ($i=0; $i < $rowspan; $i++)
        {
          // Fetch the query result.
          $row_lane = pg_fetch_assoc ($result_lane);
          echo '<tr>';
          // Lane information only if this is the first sample.
          if ($i == 0)
          {
            // Calculate number of samples.
            if (strlen (trim ($row_lane['pi_name'])) > 0)
            {
              $num_lane_samples = $rowspan;
            } else {
              $num_lane_samples = 0;
            }  // if (strlen (trim ($row_lane['pi_name'])) > 0)
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 '<input type="hidden" name="run_lane_uid[',$key_number,']" ',
                 'value="',$row_lane['run_lane_uid'],'" />',
                 $lane_number,'</td>';
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 td_ready($num_lane_samples),'</td>';
            echo '<td class="tdBlueBorder" style="text-align:center" ',
                 'rowspan="',
                 $rowspan,'">',
                 '<textarea name="total_reads[',
                 $lane_number,
                 ']" title="Total reads applies to the lane." ',
                 'cols="10" rows="2" class="inputseriftext">',
                 htmlentities ($row_lane['total_reads'], ENT_NOQUOTES),
                 '</textarea></td>';
          }  // if ($i == 0)
          if (strlen (trim ($row_lane['project_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="projectWindow(\'',
                 $row_lane['project_uid'],'\');" ',
                 'title="Display information on primary investigator ',
                 $row_lane['project_name'],'.">',
                 td_ready($row_lane['project_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['project_name'])) > 0)
          if (strlen (trim ($row_lane['pi_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="primary_investigatorWindow(\'',
                 $row_lane['primary_investigator_uid'],'\');" ',
                 'title="Display information on primary investigator ',
                 $row_lane['pi_name'],'.">',
                 td_ready($row_lane['pi_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['pi_name'])) > 0)
          if (strlen (trim ($row_lane['contact_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center"><a ',
                 'href="javascript:void(0)" ',
                 'onclick="contactWindow(\'',
                 $row_lane['contact_uid'],'\');" ',
                 'title="Display information on contact ',
                 $row_lane['contact_name'],'.">',
                 td_ready($row_lane['contact_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['contact_name'])) > 0)
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['sample_type']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['species']),'</td>';
          if (strlen (trim ($row_lane['sample_name'])) > 0)
          {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '<a href="javascript:void(0)" onclick="sampleWindow(\'',
                 $row_lane['sample_uid'],'\');" ',
                 'title="Display information on sample ',
                 $row_lane['sample_name'],'.">',
                 td_ready($row_lane['sample_name']),'</a></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if (strlen (trim ($row_lane['sample_name'])) > 0)
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['barcode']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['barcode_index']),'</td>';
          echo '<td class="tdBlueBorder" style="text-align:center">',
               td_ready($row_lane['batch_group']),'</td>';
          // Fields you are only allowed to update if the lane has a sample.
          if ($row_lane['run_lane_sample_uid'] > 0)
          {
            $run_lane_sample_uid = $row_lane['run_lane_sample_uid'];
            echo '<td class="tdBlueBorder" style="text-align:center" >',
                 '<input name="insert_size[',
                 $run_lane_sample_uid,
                 ']" type="text" ',
                 'class="inputrow" size="20" value="',
                 htmlentities ($row_lane['insert_size']),'" /></td>';
            echo '<td class="tdBlueBorder" style="text-align:center" >',
                 '<input name="load_concentration[',
                 $run_lane_sample_uid,
                 ']" type="text" ',
                 'onblur="testPosRealField(this);" ',
                 'title="Must be a positive number." ',
                 'class="inputrow" size="5" value="',
                 $row_lane['load_concentration'],'" /></td>';
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '<textarea name="comments[',
                 $run_lane_sample_uid,
                 ']" title="Comment applies to the sample." ',
                 'cols="40" rows="2" class="inputseriftext">',
                 htmlentities ($row_lane['comments'], ENT_NOQUOTES),
                 '</textarea></td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if ($row_lane['run_lane_sample_uid'] > 0)
          echo '</tr>';
        }  // for ($i=0; $i < $rowspan; $i++)
    } else {
        // Create a row where the lane has no database values.
        echo '<tr>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             '<input type="hidden" name="run_lane_uid[',$key_number,']" ',
             'value="-999" />',
             $lane_number,'</td>';
        echo '<td class="tdBlueBorder" style="text-align:center">0</td>';
        echo '<td class="tdBlueBorder" style="text-align:center" >',
             '<textarea name="total_reads[',
             $lane_number,
             ']" title="Cluster density applies to the lane." ',
             'cols="10" rows="2" class="inputseriftext">',
             '</textarea></td>';
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
        echo '<td class="tdBlueBorder" style="text-align:center">&nbsp;</td>';
        echo '</tr>';
      }  // if ($rowspan > 0)
    }  // if (!$result_lane)
  }  // for ($lane_number = 1; $lane_number <= num_run_lanes; $lane_number++)
}  // if ($run_uid > 0)
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
