<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_add_prep_type"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_save']))
    {
      header("location: process_new_prep_type.php");
      exit;
    } elseif (isset($_POST['submit_clear'])) {
      unset($_POST['prep_type']);
      if (isset ($_SESSION['prep_type']))
        unset ($_SESSION['prep_type']);
    } elseif (isset($_POST['submit_manage_barcodes'])) {
      if (isset ($_SESSION['prep_type']))
        unset ($_SESSION['prep_type']);
      header("location: manage_barcodes.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Add Prep Type, ',$abbreviated_app_name,'</title>';
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
 onload="document.form_add_prep_type.prep_type.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center">',
       '<span class="titletext">Add Prep Type - ',
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
 name="form_add_prep_type">
<input type="hidden" name="process" value="1" />
<?php
  echo '<input type="submit" name="submit_save" value="Save" ',
       'title="Save new prep type." class="buttontext" />';
  echo '<input type="submit" name="submit_clear" value="Clear" ',
       'title="Clear fields." class="buttontext" />';
  echo '<input type="submit" name="submit_manage_barcodes" value="Quit" ',
       'onclick="return confirm(\'A new prep type will not be created. Continue?\');" ',
       'title="Return to Manage Barcodes page without saving." ',
       'class="buttontext" />';
  echo '<input type="button" value="See Barcode List" ',
       'title="Shows all the reference barcodes ',
       'and associated barcode indexes." ',
       'onclick="javascript:barcodeReferenceWindow()" /><br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  } elseif (!isset($_POST['process'])) {
    if (isset ($_SESSION['prep_type']))
      unset ($_SESSION['prep_type']);
  }  // if (isset($_SESSION['errors']))
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '<b><i>* Required Fields</i></b></span></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Prep Type</span>';
 $input_prep_type = (isset ($_SESSION['prep_type']) ?
  input_ready ($_SESSION['prep_type']) : "" );
 echo '<input type="text" name="prep_type" size="60" class="inputtext" ',
      'title="Only alphanumeric characters and underscore allowed." ',
      ' value="',$input_prep_type,'" /></p>';
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
