<?php
session_start();
require_once('db_fns.php');
function populate_fields ($dbconn, $run_uid)
{
  $result = pg_query($dbconn,"
   SELECT run_number,
          run_name,
          ref_run_type.ref_run_type_uid,
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
     FROM run,
          ref_run_type
    WHERE run_uid = $run_uid AND
          run.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    return FALSE;
  } elseif (pg_num_rows ($result) < 1) {
    return FALSE;
  } else {
    $row = pg_fetch_assoc ($result);
    $_SESSION['run_number'] = $row['run_number'];
    $_SESSION['run_name'] = $row['run_name'];
    $_SESSION['choose_run_type'] = $row['ref_run_type_uid'];
    $_SESSION['read_type'] = $row['read_type'];
    $_SESSION['read_1_length'] = $row['read_1_length'];
    $_SESSION['read_2_length'] = $row['read_2_length'];
    $_SESSION['read_length_indexing'] = $row['read_length_indexing'];
    $_SESSION['hi_seq_slot'] = $row['hi_seq_slot'];
    $_SESSION['cluster_gen_start_date'] = $row['cluster_gen_start_date'];
    $_SESSION['sequencing_start_date'] = $row['sequencing_start_date'];
    $_SESSION['truseq_cluster_gen_kit'] = $row['truseq_cluster_gen_kit'];
    $_SESSION['flow_cell_hs_id'] = $row['flow_cell_hs_id'];
    $_SESSION['sequencing_kits'] = $row['sequencing_kits'];
    $_SESSION['comments'] = $row['comments'];
    return TRUE;
  }  // if (!$result)
}  // function populate_fields
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
$error_message = "";
$run_uid = (isset ($_SESSION['run_uid']) ?
 $_SESSION['run_uid'] : 0);
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_update_run_info.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
     if ($run_uid > 0)
     {
        if (!populate_fields ($dbconn, $run_uid))
          $error_message = pg_last_error ($dbconn);
     } else {
       $error_message = "No run selected.";
     }  // if ($run_uid > 0)
    } elseif (isset($_POST['submit_run_info'])) {
      header("location: run_info.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script type="text/javascript">
 var sundayFirst = true;
</script>
<script src="library/calendar.js"
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
  if ($run_uid > 0)
  {
    echo '<input type="hidden" name="run_uid" value="',
         $run_uid,'" />';
  } else {
    $error_message = "No run selected.";
  }  // if ($run_uid > 0)
  echo '<input type="submit" name="submit_save" value="Save" ',
   'title="Save changes to run information." ',
   'class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
       'title="Restore to most recent saved changes." ',
       'class="buttontext" />';
  echo '<input type="submit" name="submit_run_info" value="Quit" ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
       'title="Return to run info without saving." class="buttontext" />';
  echo '<br/>';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    if ($run_uid > 0)
    {
      if (!populate_fields ($dbconn, $run_uid))
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
    } else {
      echo '<span class="errortext">No run selected.</span><br />';
    }  // if ($run_uid > 0)
  }  // if (isset($_SESSION['errors']))
if ($run_uid > 0)
{
  echo '<div class="optionaltext" style="padding: 5px; text-align: left">';
  echo '<span class="requiredtext" >',
       '* Required Fields</span><br />';
  echo '<span class="requiredtext">',
       '* Run Number</span>';
  echo '<input type="text" name="run_number" size="2" class="inputtext" ',
       'value="',
       $_SESSION['run_number'],
       '" onblur="testPosIntField(this);" ',
       'title="Run number must be a positive integer" ',
       '/>&nbsp;&nbsp;';
  echo '<span class="optionaltext">Run Name</span>';
  echo '<input type="text" name="run_name" size="40" class="inputtext" ',
       ' value="',htmlentities ($_SESSION['run_name'], ENT_COMPAT),'" /><br />';
  $checked_single = "";
  $checked_paired = "";
  if (isset ($_SESSION['read_type']))
  {
    if ($_SESSION['read_type'] == 'Single')
    {
      $checked_single = 'checked="checked"';
      $read_2_text_class = 'class="disabledtext"';
      $read_2_input_class = 'class="inputdisabledtext"';
      $read_2_disabled = 'disabled="disabled"';
      unset ($_SESSION['read_2_length']);
    } elseif ($_SESSION['read_type'] == 'Paired End') {
      $checked_paired = 'checked="checked"';
      $read_2_text_class = 'class="requiredtext"';
      $read_2_input_class = 'class="inputtext"';
      $read_2_disabled = "";
    } else {
      $read_2_text_class = "";
      $read_2_input_class = "";
      $read_2_disabled = "";
    }  // if ($_SESSION['read_type'] == 'Single')
  }  // if (isset ($_SESSION['read_type']))
  $select_run_type_value = (isset ($_SESSION['choose_run_type']) ?
   $_SESSION['choose_run_type'] : "");
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="requiredtext">* Run Type</span>&nbsp;';
  $result_sample_count = pg_query ($dbconn, "
   SELECT count(1)
     FROM run_lane,
          run_lane_sample
    WHERE run_lane.run_uid = $run_uid AND
          run_lane.run_lane_uid = run_lane_sample.run_lane_uid");
  if (!$result_sample_count)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    $run_sample_count = pg_fetch_result ($result_sample_count, 0, 0);
    if ($run_sample_count > 0)
    {
      $run_type_disabled = 'disabled="disabled"';
      $run_type_title = 'Run type cannot be changed as run has ' .
                        'samples assigned to it.';
    } else {
      $run_type_disabled = "";
      $run_type_title = 'Choose run type.';
    }  // if ($run_sample_count > 0)
  }  // if (!$result_sample_count)
  echo drop_down_table ($dbconn, "choose_run_type", $select_run_type_value,
                        "inputtext", "ref_run_type",
                        "ref_run_type_uid", "run_type",
                        $run_type_title, " ",
                        "None",
                        -1, 0, 1, $run_type_disabled); 
  echo '<br />';
  echo '<span class="requiredtext" >',
       '* Read Type</span><br />';
  echo '<input type="radio" name="read_type" value="Single" ',
       $checked_single,
       ' onclick="this.form.submit();" />Single&nbsp;';
  echo '<input type="radio" name="read_type" value="Paired End" ',
       $checked_paired,
       ' onclick="this.form.submit();" />Paired End';
  echo '<br />';
  echo '<span class="requiredtext">',
       '* Read 1 Length</span>';
  echo '<input type="text" name="read_1_length" size="3" class="inputtext" ',
       'value="',
       $_SESSION['read_1_length'],
       '" onblur="testPosIntField(this);" ',
       'title="Read 1 length must be a positive integer" ',
       '/>';
  echo '<br />';
  echo '<span class="requiredtext"',
       $read_2_text_class,
       '>* Read 2 Length</span>';
  $read_2_length = (isset ($_SESSION['read_2_length']) ?
   $_SESSION['read_2_length'] : "");
  echo '<input type="text" name="read_2_length" size="3" ',
       'value="',
       $read_2_length,
       '" onblur="testPosIntField(this);" ',
       $read_2_disabled,' ',
       $read_2_input_class,
       'title="Read 2 length must be a positive integer" ',
       '/>';
  echo '<br />';
  echo '<span class="requiredtext">',
       '* Indexing Read Length</span>';
  echo '<input type="text" name="read_length_indexing" size="3" ',
       'class="inputtext" value="',
       $_SESSION['read_length_indexing'],
       '" onblur="testPosIntField(this);" ',
       'title="Indexing Read Length must be a positive integer" ',
       '/>';
  $checked_a = "";
  $checked_b = "";
  if (isset ($_SESSION['hi_seq_slot']))
  {
    if ($_SESSION['hi_seq_slot'] == 'A')
    {
      $checked_a = 'checked="checked"';
    } elseif ($_SESSION['hi_seq_slot'] == 'B') {
      $checked_b = 'checked="checked"';
    }  // if ($_SESSION['hi_seq_slot'] == 'A')
  }  // if (isset ($_SESSION['hi_seq_slot']))
  echo '<br />';
  echo 'Hi Seq Slot';
  echo '<br />';
  echo '<input type="radio" name="hi_seq_slot" value="A" ',
       $checked_a,
       '/>A&nbsp;';
  echo '<input type="radio" name="hi_seq_slot" value="B" ',
       $checked_b,
       '/>B';
  echo '<br />';
  // If a cluster gen Start Date has already been entered, display it.
  //  Otherwise use current date.
  if (!isset($_SESSION['cluster_gen_start_date']))
  {
    $cluster_gen_start_date = "";
  } else {
    $cluster_gen_start_date = $_SESSION['cluster_gen_start_date'];
  }  // if (isset($_SESSION['cluster_gen_start_date']))
  // If a sequencing Start Date has already been entered, display it.
  //  Otherwise use current date.
  if (!isset($_SESSION['sequencing_start_date']))
  {
    $sequencing_start_date = "";
  } else {
    $sequencing_start_date = $_SESSION['sequencing_start_date'];
  }  // if (isset($_SESSION['sequencing_start_date']))
  echo 'Cluster Gen Start Date';
  echo '<input type="text" name="cluster_gen_start_date" ',
       'id="cluster_gen_start_date" size="10" ',
       ' onclick="fPopCalendar(\'cluster_gen_start_date\')" ',
       'class="inputtext" value="',$cluster_gen_start_date,'" />';
  echo '<br />';
  echo 'Sequencing Start Date';
  echo '<input type="text" name="sequencing_start_date" ',
       'id="sequencing_start_date" size="10" ',
       ' onclick="fPopCalendar(\'sequencing_start_date\')" ',
       'class="inputtext" value="',$sequencing_start_date,'" />';
  echo '<br />';
  echo 'TruSeq Cluster Gen Kit';
  echo '<input type="text" name="truseq_cluster_gen_kit" size="40" ',
       'class="inputtext" ',
       ' value="',
       htmlentities ($_SESSION['truseq_cluster_gen_kit'], ENT_COMPAT),'" />';
  echo '<br />';
  echo 'FlowCel HS ID';
  echo '<input type="text" name="flow_cell_hs_id" size="40" ',
       'class="inputtext" ',
       ' value="',
       htmlentities ($_SESSION['flow_cell_hs_id'], ENT_COMPAT),'" /></p>';
  echo 'Sequencing Kits';
  echo '<br />';
  echo '<textarea name="sequencing_kits" cols="60" rows="2" ',
       'class="inputseriftext">',$_SESSION['sequencing_kits'],
       '</textarea>';
  echo '<br />';
  echo 'Comments:';
  echo '<br />';
  echo '<textarea name="comments" cols="60" rows="2" ',
       'class="inputseriftext">',$_SESSION['comments'],
       '</textarea>';
  echo '</div>';
}  // if ($run_uid > 0)
 echo '</form>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
?>
</body>
</html>
