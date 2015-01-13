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
$error = "";
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_table']))
    {
      // Update the billing information.
      foreach ($_POST['billing'] as $run_lane_sample_uid => $sample_billing)
      {
        $ready_sample_billing = ddl_ready ($sample_billing);
        $result_lane_update = pg_query ($dbconn, "
         UPDATE run_lane_sample
            SET billing = '$ready_sample_billing'
          WHERE run_lane_sample_uid = $run_lane_sample_uid");
        if (!$result_lane_update)
          $error = pg_last_error ($dbconn);
      }  // foreach ($_POST['billing'] as $run_lane_sample_uid =>...
      // Update the paid information.
      foreach ($_POST['paid'] as $run_lane_sample_uid => $sample_paid)
      {
        $ready_sample_paid = ddl_ready ($sample_paid);
        $result_lane_update = pg_query ($dbconn, "
         UPDATE run_lane_sample
            SET paid = $ready_sample_paid
          WHERE run_lane_sample_uid = $run_lane_sample_uid");
        if (!$result_lane_update)
          $error = pg_last_error ($dbconn);
      }  // foreach ($_POST['paid'] as $run_lane_sample_uid =>...
      // Return to the run billing page.
      header("location: billing.php");
      exit;
    } elseif (isset($_POST['submit_exit'])) {
      header("location: billing.php");
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
  echo '<title>Run Billing, ',$abbreviated_app_name,'</title>';
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
       'Run Billing - ',$app_name,'</span></h1>';
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
    echo '<span class="errortext">',$error,'</span><br />';
?>
  <iframe src="run_info_display.php" width="550px;" 
   style="border: 2px solid blue; height: 320px;" >
   <p>Your browser does not support iframes.</p>
  </iframe><br /><br />
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_lane_samples" >
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_table" value="Save" ',
       'title="Save updated billing information." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
       'title="Restore to most recent saved changes." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_exit" value="Quit" ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
       'title="Return to Run Billing page without saving." ',
       'class="buttontext" />';
?>
<p class="updateabletext"><i>* Updateable Field</i></p>
<table id="sample_table" border="1" width="100%">
<thead>
  <tr>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Flowcell Lane</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    # of Samples</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Total Load Concentration</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Total Reads</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Billing</th>
    <th class="thSmallerGreenBlueBorder" scope="col" style="text-align:center"
     width="200" >
    Paid</th>
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
    Sample</th>
    <th class="thSmallerBlueBorder" scope="col" style="text-align:center"
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
    // Get the lane information, if it exists.
    $result_lane = pg_query ($dbconn, "
      SELECT run_lane.run_lane_uid,
             run_lane_sample.run_lane_sample_uid,
             run_lane.total_reads,
             billing,
             CASE WHEN run_lane_sample.paid THEN 'TRUE'
                  ELSE 'FALSE'
             END AS paid,
             primary_investigator.primary_investigator_uid,
             primary_investigator.name AS pi_name,
             contact.contact_uid,
             contact.name AS contact_name,
             sample.sample_type,
             sample.sample_uid,
             sample.sample_name,
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
             billing,
             CASE WHEN run_lane_sample.paid THEN 'TRUE'
                  ELSE 'FALSE'
             END AS paid,
             primary_investigator.primary_investigator_uid,
             primary_investigator.name AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             sample.sample_type,
             sample.sample_uid,
             sample.sample_name,
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
             ' ' AS billing,
             'FALSE' AS paid,
             0   AS primary_investigator_uid,
             ' ' AS pi_name,
             0   AS contact_uid,
             ' ' AS contact_name,
             ' ' AS sample_type,
             0   AS sample_uid,
             ' ' AS sample_name,
             ' ' AS comments
        FROM run_lane
       WHERE lane_number = $lane_number AND
             run_lane.run_uid = $run_uid AND
             run_lane_uid 
      NOT IN (SELECT run_lane_uid
                FROM run_lane_sample
               WHERE run_uid = $run_uid)
       ORDER BY sample_name");
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
          $run_lane_uid = $row_lane['run_lane_uid'];
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
            // Calculate the total load concentration for the lane.
            $result_concentration = pg_query ($dbconn, "
             SELECT coalesce (sum (load_concentration), 0)
               FROM run_lane_sample
              WHERE run_lane_uid = $run_lane_uid");
            if (!$result_concentration)
            {
              $total_concentration = pg_last_error ($dbconn);
            } else {
              $total_concentration = pg_fetch_result ($result_concentration,
                                                      0, 0);
            }  // if (!$result_concentration)
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 '<input type="hidden" name="run_lane_uid[',$key_number,']" ',
                 'value="',$row_lane['run_lane_uid'],'" />',
                 $lane_number,'</td>';
            echo '<td class="tdBlueBorder" style="text-align:center" rowspan="',
                 $rowspan,'">',
                 td_ready($num_lane_samples),'</td>';
            if ($total_concentration > 0)
            {
              echo '<td class="tdBlueBorder" style="text-align:center" ',
                   'rowspan="',
                   $rowspan,'">',
                   $total_concentration,'</td>';
            } else {
              echo '<td class="tdBlueBorder" style="text-align:center" ',
                   'rowspan="',
                   $rowspan,'">',
                   '&nbsp;</td>';
            }  // if ($total_concentration > 0)
            $div_height = $rowspan * 30;
            echo '<td class="tdBlueBorder" style="text-align:left" rowspan="',
                 $rowspan,'">',
                 '<div style="width: 150px; height: ',
                 $div_height,
                 'px; overflow: auto; padding: 5px;"><font face="sylfaen">',
                 td_ready($row_lane['total_reads']),
                 '</font></div></td>';
          }  // if ($i == 0)
          // You may only update billing if the lane has a sample.
          if ($row_lane['run_lane_sample_uid'] > 0)
          {
            $run_lane_sample_uid = $row_lane['run_lane_sample_uid'];
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '<textarea name="billing[',
                 $run_lane_sample_uid,
                 ']" title="Billing for the sample." ',
                 'cols="40" rows="2" class="inputseriftext">',
                 htmlentities ($row_lane['billing'], ENT_NOQUOTES),
                 '</textarea></td>';
            // Prepare drop-down box for paid value.
            if ($row_lane['paid'] == 'FALSE')
            {
              $true_selected = '';
              $false_selected = 'selected="selected"';
            } else {
              $true_selected = 'selected="selected"';
              $false_selected = '';
            }  // if ($row['paid_string'] == 'FALSE')
            $paid_select_name = 'paid[' . $run_lane_sample_uid . ']';
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 drop_down_boolean ($paid_select_name, $true_selected,
                                    $false_selected, "inputrow"),
                 '</td>';
          } else {
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center">',
                 '&nbsp;</td>';
          }  // if ($row_lane['run_lane_sample_uid'] > 0)
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
          echo '<td class="tdBlueBorder" style="text-align:left">',
               '<div style="width: 150px; height: 30px; ',
               'overflow: auto; padding: 5px;"><font face="sylfaen">',
               td_ready($row_lane['comments']),
               '</font></div></td>';
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
  }  // for ($lane_number=1; $lane_number <= $num_run_lanes; $lane_number++)
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
