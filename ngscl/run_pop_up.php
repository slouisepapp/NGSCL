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
require_once 'user_view.php';
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Run Information, ',$abbreviated_app_name,'</title>';
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

</head>

<body class="oneColElsLtHdr">
<div class="style1" id="container">
  <div id="header">
<?php
  echo '<h1 style="text-align: center"><span class="titletext">',
       'Run Information - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  if (isset ($_GET['run_uid']))
  {
    $_SESSION['run_uid'] = $_GET['run_uid'];
    $run_uid = $_SESSION['run_uid'];
    $result_info = pg_query($dbconn,"
        SELECT run_uid,
               run_number,
               run_name,
               run_type,
               read_type,
               read_1_length,
               read_2_length,
               read_length_indexing,
               hi_seq_slot,
               cluster_gen_start_date,
               sequencing_start_date,
               truseq_cluster_gen_kit,
               flow_cell_hs_id,
               sequencing_kits,
               comments
          FROM $run_view,
               ref_run_type
         WHERE run_uid = $run_uid AND
               $run_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
    if (!$result_info)
    {
      echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
    } else {
      $row_info = pg_fetch_assoc ($result_info);
      // Get every project with samples in this run.
      $project_list = "";
      $result_project = pg_query ($dbconn, "
       SELECT DISTINCT project_name
         FROM $run_view,
              $run_lane_view,
              $run_lane_sample_view,
              $sample_view,
              $project_view
        WHERE $run_view.run_uid = $run_uid AND
              $run_view.run_uid = $run_lane_view.run_uid AND
              $run_lane_view.run_lane_uid =
               $run_lane_sample_view.run_lane_uid AND
              $sample_view.sample_uid = $run_lane_sample_view.sample_uid AND
              $sample_view.project_uid = $project_view.project_uid
        ORDER BY project_name");
      if (!$result_project)
      {
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        for ($i=0; $i < pg_num_rows ($result_project); $i++)
        {
          $project_list .= ", " . pg_fetch_result ($result_project, $i, 0);
        }  // for ($i=0; $i < pg_num_rows ($result_project); $i++)
        $project_list = ltrim ($project_list, ", ");
      }  // if (!$result_project)
      echo '<p style="text-align:left;" class="displaytext"><b>',
           'Run Number:</b> ',
           $row_info['run_number'],
           '<br />';
      echo '<b>Run Name:</b> ',
           $row_info['run_name'],
           '<br />';
      echo '<b>Run Type:</b> ',
           $row_info['run_type'],
           '<br />';
      echo '<b>Read Type:</b> ',
           $row_info['read_type'],
           '<br />';
      echo '<b>Read 1:</b> ',
           $row_info['read_1_length'],
           '<br />';
      echo '<b>Read 2:</b> ',
           $row_info['read_2_length'],
           '<br />';
      echo '<b>Indexing Read Length:</b> ',
           $row_info['read_length_indexing'],
           '<br />';
      echo '<b>Hi Seq Slot:</b> ',
           $row_info['hi_seq_slot'],
           '<br />';
      echo '<b>TruSeq Cluster Gen Kit:</b> ',
           $row_info['truseq_cluster_gen_kit'],
           '<br />';
      echo '<b>FlowCel HS ID:</b> ',
           $row_info['flow_cell_hs_id'],
           '<br />';
      echo '<b>Cluster Gen Began:</b> ',
           $row_info['cluster_gen_start_date'],
           '<br />';
      echo '<b>Sequencing Began:</b> ',
           $row_info['sequencing_start_date'],
           '<br />';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 60px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Sequencing Kits:</b></span><br />',
           td_ready($row_info['sequencing_kits']),
           '</div>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 60px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Project List:</b></span><br />',
           td_ready($project_list),
           '</div>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 60px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Comments:</b></span><br />',
           td_ready($row_info['comments']),
           '</div>';
    }  // if (!$result_info)
  } else {
    echo '<span class="errortext">No run selected.</span>';
  }  // if (isset ($_SESSION['run_uid']))
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
