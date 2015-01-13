<?php
session_start();
require_once('db_fns.php');
require_once('run_functions.php');
require_once('constants.php');
// Put everything from post into session.
$previous_run_type = (isset ($_SESSION['choose_run_type']) ?
 $_SESSION['choose_run_type'] : 0);
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_run_info"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_new_run.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_POST['run_number']);
      unset($_POST['run_name']);
      unset($_POST['choose_run_type']);
      unset($_POST['read_type']);
      unset($_POST['read_1_length']);
      unset($_POST['read_2_length']);
      unset($_POST['read_length_indexing']);
      unset($_POST['hi_seq_slot']);
      unset($_POST['cluster_gen_start_date']);
      unset($_POST['sequencing_start_date']);
      unset($_POST['truseq_cluster_gen_kit']);
      unset($_POST['flow_cell_hs_id']);
      unset($_POST['sequencing_kits']);
      unset($_POST['comments']);
      clear_run_vars();
    } elseif (isset($_POST['submit_run'])) {
      clear_run_vars();
      header("location: run.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Create Run Info, ',$abbreviated_app_name,'</title>';
?>
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
<body class="twoColElsLtHdr"
 onload="document.form_run_info.run_name.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center">',
       '<span class="titletext">Create Run - ',
       $app_name,'</span></h1>';
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
 name="form_run_info">
<input type="hidden" name="process" value="1" />
<?php
 echo '<input type="submit" name="submit_save" value="Save" ',
  'title="Save new run." class="buttontext" />';
 echo '<input type="submit" name="submit_clear" value="Clear" ',
  'title="Clear fields." class="buttontext" />';
 echo '<input type="submit" name="submit_run" value="Quit" ',
   'onclick="return confirm(\'New run will not be created. Continue?\');" ',
  'title="Return to Run page without saving." class="buttontext" />';
  echo '<br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    clear_run_vars();
  }  // if (isset($_SESSION['errors']))
  $select_run_type_value = (isset ($_SESSION['choose_run_type']) ?
   $_SESSION['choose_run_type'] : 0);
  if ($previous_run_type == $select_run_type_value)
  {
    $_SESSION['run_number'] = (isset ($_SESSION['run_number']) ?
     $_SESSION['run_number'] : 0);
  } else {
    $_SESSION['run_number'] = next_run_number (
     $dbconn, $select_run_type_value);
  }  // if ($previous_run_type == $select_run_type_value)
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
       '<b><i>* Required Fields</i></b></span></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
       '* Run Number</span>';
  echo '<input type="text" name="run_number" size="4" class="inputtext" ',
       'value="',
       $_SESSION['run_number'],
       '" onblur="testPosIntField(this);" ',
       'title="Run number must be a positive integer" ',
       '/>&nbsp;&nbsp;';
  echo '<span class="optionaltext">Run Name</span>';
  $run_name = (isset ($_SESSION['run_name']) ?
   $_SESSION['run_name'] : "");
  echo '<input type="text" name="run_name" size="40" class="inputtext" ',
       ' value="',$run_name,'" /></p>';
  $checked_single = "";
  $checked_paired = "";
  if (!isset ($_SESSION['read_type']))
  {
    $_SESSION['read_type'] = 'Single';
  }  // if (!isset ($_SESSION['read_type']))
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
    $read_2_disabled = '';
    $read_2_input_class = 'class="inputtext"';
  } else {
    $read_2_text_class = "";
    $read_2_input_class = "";
    $read_2_disabled = "";
  }  // if ($_SESSION['read_type'] == 'Single')
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="requiredtext">* Run Type</span>&nbsp;';
  echo drop_down_table ($dbconn, "choose_run_type", $select_run_type_value,
                        "inputtext", "ref_run_type",
                        "ref_run_type_uid", "run_type",
                        "Choose run type.", " ",
                        "None", -1);
  echo '</p>';
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="requiredtext" >',
       '<b><i>* Read Type</i></b></span></p>';
  echo '<input type="radio" name="read_type" value="Single" ',
       $checked_single,
       ' onclick="this.form.submit();" />Single&nbsp;';
  echo '<input type="radio" name="read_type" value="Paired End" ',
       $checked_paired,
       ' onclick="this.form.submit();" />Paired End';
  echo '<br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
       '* Read 1 Length</span>';
  $read_1_length = (isset ($_SESSION['read_1_length']) ?
   $_SESSION['read_1_length'] : "");
  echo '<input type="text" name="read_1_length" size="3" class="inputtext" ',
       'value="',
       $read_1_length,
       '" onblur="testPosIntField(this);" ',
       'title="Read 1 length must be a positive integer" ',
       '/></p>';
  echo '<p style="text-align: left; margin: 2px;"><span ',
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
       ' title="Read 2 length must be a positive integer" ',
       '/></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
       '* Indexing Read Length</span>';
  $read_length_indexing = (isset ($_SESSION['read_length_indexing']) ?
   $_SESSION['read_length_indexing'] : "");
  echo '<input type="text" name="read_length_indexing" size="3" ',
       'class="inputtext" value="',
       $read_length_indexing,
       '" onblur="testPosIntField(this);" ',
       'title="Indexing Read Length must be a positive integer" ',
       '/></p>';
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
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="optionaltext" >',
       '<b><i>Hi Seq Slot</i></b></span></p>';
  echo '<input type="radio" name="hi_seq_slot" value="A" ',
       $checked_a,
       '/>A&nbsp;';
  echo '<input type="radio" name="hi_seq_slot" value="B" ',
       $checked_b,
       '/>B';
  echo '<br />';
  // If a cluster gen start date has already been entered, display it.
  //  Otherwise use current date.
  $cluster_gen_start_date = (isset ($_SESSION['cluster_gen_start_date']) ?
   $_SESSION['cluster_gen_start_date'] : date("Y-m-d"));
  // If a sequencing start date has already been entered, display it.
  //  Otherwise use current date.
  $sequencing_start_date = (isset ($_SESSION['sequencing_start_date']) ?
   $_SESSION['sequencing_start_date'] : $sequencing_start_date = date("Y-m-d"));
  echo '<p style="text-align: left; margin: 2px;">',
      '<span class="optionaltext">Cluster Gen Start Date</span>';
  echo '<input type="text" name="cluster_gen_start_date" ',
       'id="cluster_gen_start_date" size="10" ',
       'onclick="fPopCalendar(\'cluster_gen_start_date\')" ',
       'class="inputtext" value="',$cluster_gen_start_date,'" /></p>';
  echo '<p style="text-align: left; margin: 2px;">',
      '<span class="optionaltext">Sequencing Start Date</span>';
  echo '<input type="text" name="sequencing_start_date" ',
       'id="sequencing_start_date" size="10" ',
       'onclick="fPopCalendar(\'sequencing_start_date\')" ',
       'class="inputtext" value="',$sequencing_start_date,'" /></p>';
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="optionaltext">TruSeq Cluster Gen Kit</span>';
  $truseq_cluster_gen_kit = (isset ($_SESSION['truseq_cluster_gen_kit']) ?
   $_SESSION['truseq_cluster_gen_kit'] : "");
  echo '<input type="text" name="truseq_cluster_gen_kit" size="40" ',
       'class="inputtext" ',
       ' value="',$truseq_cluster_gen_kit,'" /></p>';
  $flow_cell_hs_id = (isset ($_SESSION['flow_cell_hs_id']) ?
   $_SESSION['flow_cell_hs_id'] : "");
  echo '<p style="text-align: left; margin: 2px;">',
       '<span class="optionaltext">FlowCel HS ID</span>',
       '<input type="text" name="flow_cell_hs_id" size="40" ',
       'class="inputtext" ',
       ' value="',$flow_cell_hs_id,'" /></p><br />';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Sequencing Kits:</span><br />';
  $sequencing_kits = (isset ($_SESSION['sequencing_kits']) ?
   $_SESSION['sequencing_kits'] : "");
  echo '<textarea name="sequencing_kits" cols="60" rows="2" ',
       'class="inputseriftext">',$sequencing_kits,
       '</textarea></p>';
  echo '<p style="text-align: left; margin: 2px;"><span class="optionaltext">',
       'Comments:</span><br />';
  $comments = (isset ($_SESSION['comments']) ?
   $_SESSION['comments'] : "");
  echo '<textarea name="comments" cols="60" rows="2" ',
       'class="inputseriftext">',$comments,
       '</textarea></p>';
 echo '</form>';
if (isset($_SESSION['errors']))
  unset($_SESSION['errors']);
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
