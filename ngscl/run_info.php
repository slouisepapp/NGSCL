<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
require_once 'user_view.php';
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_update']))
    {
      // Put everything from post into session.
      foreach ($_POST as $thislabel => $thisvalue)
      {
        if (($thislabel != "PHPSESSID") &&
            ($thislabel != "form_run_info"))
        {
          $_SESSION[$thislabel] = $thisvalue;
        }
      }  // foreach ($_POST as $thislabel => $thisvalue)
      header("location: update_run_info.php");
      exit;
    }  //if (isset($_POST['submit_update']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
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
</head>
<body>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_run_info">
<input type="hidden" name="process" value="1" />
<?php
$run_uid = (isset ($_SESSION['run_uid']) ? $_SESSION['run_uid'] : 0);
if ($run_uid > 0)
{
  $result = pg_query($dbconn,"
   SELECT run_number,
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
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    $row_meta = pg_fetch_assoc ($result);
    echo '<div class="displaytext" style="padding: 5px; text-align:left;">',
         '<b>Run Number: </b>',
         $row_meta['run_number'],
         '<br /><b>Run Name: </b> ',
         $row_meta['run_name'],
         '<br />';
    echo '<b>Run Type: </b>',
         $row_meta['run_type'],
         '</span><br />';
    echo '<b>Read Type: </b>',
         $row_meta['read_type'],
         '</span><br />';
    echo '<b>Read 1 Length: </b>',
         $row_meta['read_1_length'],
         '<br />';
    echo '<b>Read 2 Length: </b>',
         $row_meta['read_2_length'],
         '<br />';
    echo '<b>Indexing Read Length: </b>',
         $row_meta['read_length_indexing'],
         '<br />';
    echo '<b>Hi Seq Slot: </b>',
         $row_meta['hi_seq_slot'],
         '<br />';
    echo '<b>Cluster Gen Start Date: </b>',
         $row_meta['cluster_gen_start_date'],
         '<br />';
    echo '<b>Sequencing Start Date: </b>',
         $row_meta['sequencing_start_date'],
         '<br />';
    echo '<b>TruSeq Cluster Gen Kit: </b>',
         $row_meta['truseq_cluster_gen_kit'],
         '<br />';
    echo '<b>FlowCel HS ID: </b>',
         $row_meta['flow_cell_hs_id'],
         '</div>';
    echo '<div align="left" style="font-family: times, serif; color:blue; ',
         'font-size:small; width: 510px; height: 40px; ',
         'overflow: auto; padding: 5px;">',
         '<b>Sequencing Kits:</b> ',
         td_ready ($row_meta['sequencing_kits']),
         '</div>';
    echo '<div align="left" style="font-family: times, serif; color:blue; ',
         'font-size:small; width: 510px; height: 40px; ',
         'overflow: auto; padding: 5px;">',
         '<b>Comments:</b> ',
         td_ready ($row_meta['comments']),
         '</div>';
    if ($_SESSION['app_role'] != 'pi_user')
    {
         echo '<input type="submit" name="submit_update" ',
              'value="Update Run Info" ',
              'title="Update values of run information." ',
              'class="buttontext"/>';
    }  // if ($_SESSION['app_role'] != 'pi_user')
  }  // if (!$result)
}  // if ($run_uid > 0)
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  }  // if (isset($_SESSION['errors']))
?>
</form>
</body>
</html>
