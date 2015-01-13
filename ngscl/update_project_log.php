<?php
require_once('db_fns.php');
require_once('project_functions.php');
require_once('constants.php');
$array_error = array();
session_start();
// Unset the check boxes.
if (! isset($_SESSION['errors']))
{
  unset ($_SESSION['core_director_approval_box']);
  unset ($_SESSION['pi_approval_box']);
} else {
  $_SESSION['core_director_approval'] = (
   isset ($_SESSION['core_director_approval_box']) ? 'TRUE' : 'FALSE');
  $_SESSION['primary_investigator_approval'] = (
   isset ($_SESSION['pi_approval_box']) ? 'TRUE' : 'FALSE');
}  // if (! isset($_SESSION['errors']))
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_project_log")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
$dbconn = database_connect();
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
$core_disabled_boolean = (
 isset ($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'dac_grants' ?
 0 : 1);
$pi_disabled_boolean = (
 isset ($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'pi_admin' ?
 0 : 1);
$label_class = 'optionaltext';
$th_class = 'thFullSizeBlueBorder';
$td_class = 'tdBlueBorder';
$num_cols = 80;
$num_rows = 2;
$mid_num_rows = 5;
$large_num_rows = 10;
$my_TextField = new TextField();
$my_TableTextField = new TableTextField();
$my_TextAreaField = new TextAreaField();
$my_CheckBox = new CheckBox();
$my_DateField = new DateField();
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_update_project_log.php");
      exit;
    } elseif (isset($_POST['submit_return'])) {
      header("location: project_log.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      $my_log = new ProjectLog (
       $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
      if (trimmed_string_not_empty ($my_log->db_error))
      {
        $array_error[] = $my_log->db_error;
      } else {
        $project_log_uid_array = $my_log->project_log_uid;
        $my_log->populate_session ($dbconn);
        if (trimmed_string_not_empty ($my_log->db_error))
        {
          $array_error[] = $my_log->db_error;
        }  // if (trimmed_string_not_empty ($my_log->db_error))
      }  // if (trimmed_string_not_empty ($my_log->db_error))
    } // if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] == 1)
} else {
  $my_log = new ProjectLog (
   $dbconn, $_SESSION['project'], $_SESSION['project_log'], $project_uid);
  if (trimmed_string_not_empty ($my_log->db_error))
  {
    $array_error[] = $my_log->db_error;
  } else {
    $project_log_uid_array = $my_log->project_log_uid;
    if (! isset($_SESSION['errors']))
    {
      $my_log->populate_session ($dbconn);
      if (trimmed_string_not_empty ($my_log->db_error))
      {
        $array_error[] = $my_log->db_error;
      }  // if (trimmed_string_not_empty ($my_log->db_error))
    }  //if (! isset($_SESSION['errors']))
  }  // if (trimmed_string_not_empty ($my_log->db_error))
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Project Log, ',$abbreviated_app_name,'</title>';
?>
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
  <script src="javascript_source.js" 
  language="javascript" 
    type="text/javascript"></script>
<script src="library/calendar.js"
 language="javascript"
 type="text/javascript"></script>
<?php
  readfile("text_styles.css");
?>
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Project Log - ',
       $app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<br />';
  $project_name = "";
  if ($project_uid > 0)
  {
    $project_table = $_SESSION['project'];
    $result_puid = pg_query ($dbconn, "
     SELECT project_name
       FROM $project_table
      WHERE project_uid = $project_uid");
    if (!$result_puid)
    {
      $array_error[] = pg_last_error ($dbconn);
    } else {
      $project_name = pg_fetch_result ($result_puid, 0, 0);
    }  // if (!$result_puid)
  }  // if ($project_uid > 0)
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
?>
 <div id="mainContent">
  <?php
    echo '<h3 class="grayed_out">Project: ',
         $my_log->project_name['value'],
         '</h3>';
    foreach ($array_error as $error_value)
    {
       echo '<span class="errortext">'.$error_value.'</span><br />';
    }  // if (count ($array_error) > 0)
    echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
         'name="form_project_log" >';
    echo '<input type="hidden" name="process" value="1" />';
    echo '<input type="hidden" name="project_log_uid" value="',
         $_SESSION['project_log_uid'],'" />';
    echo '<input type="submit" name="submit_save" ',
         'value="Save" ',
         'title="Save project log." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_reset" ',
         'value="Reset" ',
         'title="Restore to most recent saved changes." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_return" ',
         'value="Quit" ',
         'title="Return to Project Log page without saving." ',
         'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
         'class="buttontext" />';
    if (isset($_SESSION['errors']))
    {
      echo '<br />';
      foreach ($_SESSION['errors'] as $error)
        echo '<span class="errortext">'.htmlentities($error, ENT_NOQUOTES).
        '</span><br />';
      unset($_SESSION['errors']);
    }  // if (isset($_SESSION['errors']))
    $my_TextField->makeInput ($_SESSION['name_of_experimenter'],
     $my_log->name_of_experimenter['label'],$label_class,
     'name_of_experimenter',50,
     $project_log_hints['name_of_experimenter']);
    $my_TextAreaField->makeInput ($_SESSION['experiment_title'],
     $my_log->experiment_title['label'],$label_class,'experiment_title',
     $num_cols,$num_rows,
     $project_log_hints['experiment_title']);
    if (isset ($_SESSION['core_director_approval']) &&
        standardize_boolean ($_SESSION['core_director_approval']) == 'TRUE')
    {
      $core_checked_string = 'checked="checked"';
    } else {
      $core_checked_string = '';
    }  // if (isset ($_SESSION['core_director_approval']) &&...
    $my_CheckBox->makeInput ($core_checked_string,
     $my_log->core_director_approval['label'], $label_class,
     'core_director_approval_box',
     $project_log_hints['core_director_approval'],
     'switchDateTodayOrNull(\'core_director_approval_date\')',
     $core_disabled_boolean);
    $my_DateField->makeInput ($_SESSION['core_director_approval_date'],
     $my_log->core_director_approval_date['label'],$label_class,
     'core_director_approval_date',
     $project_log_hints['core_director_approval_date'],
     $core_disabled_boolean);
    if (isset ($_SESSION['primary_investigator_approval']) &&
     standardize_boolean ($_SESSION['primary_investigator_approval']) == 'TRUE')
    {
      $pi_checked_string = 'checked="checked"';
    } else {
      $pi_checked_string = '';
    }  // if (isset ($_SESSION['primary_investigator_approval']) &&...
    $my_CheckBox->makeInput ($pi_checked_string,
     $my_log->primary_investigator_approval['label'],
     $label_class,'pi_approval_box',
     $project_log_hints['primary_investigator_approval'],
     'switchDateTodayOrNull(\'primary_investigator_approval_date\')',
     $pi_disabled_boolean);
    $my_DateField->makeInput ($_SESSION['primary_investigator_approval_date'],
     $my_log->primary_investigator_approval_date['label'],$label_class,
     'primary_investigator_approval_date',
     $project_log_hints['primary_investigator_approval_date'],
     $pi_disabled_boolean);
    $my_TextField->makeInput ($_SESSION['run_number'],
     $my_log->run_number['label'],$label_class,'run_number',4,
     $project_log_hints['run_number']);
    $my_TextField->makeInput ($_SESSION['lanes'],
     $my_log->lanes['label'],$label_class,'lanes',10,
     $project_log_hints['lanes']);
    $my_DateField->makeInput ($_SESSION['run_date'],
     $my_log->run_date['label'],$label_class,'run_date','Run date');
    $my_TextField->makeInput ($_SESSION['experiment_type'],
     $my_log->experiment_type['label'],$label_class,'experiment_type',25,
     $project_log_hints['experiment_type']);
    $my_TextAreaField->makeInput ($_SESSION['results_summary'],
     $my_log->results_summary['label'],$label_class,'results_summary',
     $num_cols,$num_rows,
     $project_log_hints['results_summary']);
    $my_TextField->makeInput ($_SESSION['account_number'],
     $my_log->account_number['label'],$label_class,
     'account_number', 30,
     $project_log_hints['account_number']);
    $my_TextField->makeInput ($_SESSION['estimated_price'],
     $my_log->estimated_price['label'],$label_class,
     'estimated_price', 10,
     $project_log_hints['estimated_price']);
    $my_TextAreaField->makeInput ($_SESSION['estimated_price_comments'],
     $my_log->estimated_price_comments['label'],$label_class,
     'estimated_price_comments', $num_cols,$num_rows,
     $project_log_hints['estimated_price_comments']);
    echo '<br /><hr /><span class="',
         $label_class,
         '" ><b>EXPERIMENTAL CONDITIONS</b></span>';
    echo '<p>',$experimental_conditions_hint,'</p>';
    $my_TextAreaField->makeInput ($_SESSION['hypothesis'],
     $my_log->hypothesis['label'],$label_class,'hypothesis',
     $num_cols,$large_num_rows,'Questions asked/Hypothesis');
    $my_TextAreaField->makeInput ($_SESSION['cells_used'],
     $my_log->cells_used['label'],$label_class,'cells_used',
     $num_cols,$mid_num_rows,$project_log_hints['cells_used']);
    $my_TextAreaField->makeInput ($_SESSION['experimental_methods'],
     $my_log->experimental_methods['label'],$label_class,
     'experimental_methods',$num_cols,$mid_num_rows,
     $project_log_hints['experimental_methods']);
    $my_TextAreaField->makeInput ($_SESSION['time_course'],
     $my_log->time_course['label'],$label_class,'time_course',
     $num_cols,$num_rows,
     $project_log_hints['time_course']);
    $my_TextAreaField->makeInput ($_SESSION['experimental_category'],
     $my_log->experimental_category['label'],$label_class,
     'experimental_category',$num_cols,$num_rows,
     $project_log_hints['experimental_category']);
    $my_TextAreaField->makeInput ($_SESSION['conditions_comments'],
     $my_log->conditions_comments['label'],$label_class,'conditions_comments',
     $num_cols,$num_rows,
     $project_log_hints['conditions_comments']);
    echo '<br /><hr /><br />';
    $my_TextAreaField->makeInput ($_SESSION['batching_instructions'],
     $my_log->batching_instructions['label'],$label_class,
     'batching_instructions',$num_cols,$mid_num_rows,
     $project_log_hints['batching_instructions']);
    echo '<br /><hr /><span class="',
         $label_class,
         '" ><b>LIBRARY PREP</b></span><br />';
    $my_TextField->makeInput ($_SESSION['sample_purification_method'],
     $my_log->sample_purification_method['label'],$label_class,
     'sample_purification_method',10,
     $project_log_hints['sample_purification_method']);
    $my_TextField->makeInput ($_SESSION['kits_used'],
     $my_log->kits_used['label'],$label_class,'kits_used',30,
     $project_log_hints['kits_used']);
    $my_TextField->makeInput ($_SESSION['amplification_method'],
     $my_log->amplification_method['label'],$label_class,
     'amplification_method',30,
     $project_log_hints['amplification_method']);
    $my_TextField->makeInput ($_SESSION['barcoding_used'],
     $my_log->barcoding_used['label'],$label_class,'barcoding_used',10,
     $project_log_hints['barcoding_used']);
    $my_TextAreaField->makeInput ($_SESSION['new_method_or_comparison_study'],
     $my_log->new_method_or_comparison_study['label'],$label_class,
     'new_method_or_comparison_study',$num_cols,$mid_num_rows,
     $project_log_hints['new_method_or_comparison_study']);
    $my_TextAreaField->makeInput ($_SESSION['library_prep_comments'],
     $my_log->library_prep_comments['label'],$label_class,
     'library_prep_comments',$num_cols,$num_rows,
     $project_log_hints['library_prep_comments']);
    echo '<hr />';
    $my_TextField->makeInput ($_SESSION['read_length'],
     $my_log->read_length['label'],$label_class,'read_length',15,
     $project_log_hints['read_length']);
    $my_TextAreaField->makeInput ($_SESSION['adapter_sequences'],
     $my_log->adapter_sequences['label'],$label_class,'adapter_sequences',
     $num_cols,$num_rows,
     $project_log_hints['adapter_sequences']);
    $my_TextAreaField->makeInput ($_SESSION['barcode_sequences'],
     $my_log->barcode_sequences['label'],$label_class,'barcode_sequences',
     $num_cols,$num_rows,
     $project_log_hints['barcode_sequences']);
    echo '<input type="submit" name="submit_save" ',
         'value="Save" ',
         'title="Save project log." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_reset" ',
         'value="Reset" ',
         'title="Reset project log." ',
         'class="buttontext" />';
    echo '<input type="submit" name="submit_return" ',
         'value="Quit" ',
         'title="Return to Project Log page without saving." ',
         'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
         'class="buttontext" />';
    echo '</form>';
  ?>
  <!-- end #mainContent -->
  </div>
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
   <div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
