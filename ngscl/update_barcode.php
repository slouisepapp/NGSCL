<?php
session_start();
require_once('db_fns.php');
require_once('constants.php');
function populate_fields ($dbconn, $ref_barcode_uid)
{
  $result = pg_query ($dbconn, "
   SELECT barcode_index
     FROM ref_barcode
    WHERE ref_barcode_uid = $ref_barcode_uid");
  if (!$result)
  {
    return FALSE;
  } else {
    $row = pg_fetch_assoc ($result);
    $_SESSION['barcode_index'] = $row['barcode_index'];
    return TRUE;
  }  // if (!$result)
}  // function populate_fields
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_add_barcode"))
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
      header("location: process_update_barcode.php");
      exit;
    } elseif (isset($_POST['submit_reset'])) {
      if (!populate_fields ($dbconn, $_SESSION['ref_barcode_uid']))
        $_SESSION['errors'][] = 'Could not select barcode indexes.';
    } elseif (isset($_POST['submit_manage_barcodes'])) {
      header("location: manage_barcodes.php");
      exit;
    }  //if (isset($_POST['submit_save']))
  }  // if ($_POST['process'] ==1)
} else {
  if (isset ($_SESSION['prep_type']))
    unset ($_SESSION['prep_type']);
  if (isset ($_SESSION['barcode_number']))
    unset ($_SESSION['barcode_number']);
  // Find the barcode fields from the barcode uid.
  $result_barcode = pg_query ($dbconn, "
   SELECT prep_type,
          barcode_number,
          barcode_index
     FROM ref_barcode,
          ref_prep_type
    WHERE ref_prep_type.ref_prep_type_uid = " .
           $_SESSION['choose_prep_type'] . "AND
          ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid AND
          ref_barcode_uid = " .
           $_SESSION['ref_barcode_uid']);
  if (!$result_barcode)
  {
    $_SESSION['errors'][] = pg_last_error ($dbconn);
  } else {
    $row_barcode = pg_fetch_assoc ($result_barcode);
    $_SESSION['prep_type'] = $row_barcode['prep_type'];
    $_SESSION['barcode_number'] = $row_barcode['barcode_number'];
    // ****
    // If we are returning from an unsuccessful attempt to update
    // keep user entered value of barcode index.
    // ****
    if (!isset($_SESSION['errors']))
    {
      $_SESSION['barcode_index'] = $row_barcode['barcode_index'];
    }  // if (!isset($_SESSION['errors']))
  }  // if (!$result_barcode)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Update Barcode, ',$abbreviated_app_name,'</title>';
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
 onload="document.form_add_barcode.barcode_index.focus();" >
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center">',
       '<span class="titletext">Update Barcode - ',
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
 name="form_add_barcode">
<input type="hidden" name="process" value="1" />
<?php
 echo '<h2>Barcode:&nbsp;',
      $_SESSION['prep_type'],$barcode_separator,
      $_SESSION['barcode_number'],
      '</h2>';
  echo '<input type="submit" name="submit_save" value="Save" ',
       'title="Save new prep type." class="buttontext" />';
  echo '<input type="submit" name="submit_reset" value="Reset" ',
       'title="Restore to most recent saved changes." class="buttontext" />';
  echo '<input type="submit" name="submit_manage_barcodes" value="Quit" ',
       'onclick="return confirm(\'You will lose all unsaved changes. Continue?\');" ',
       'title="Return to Manage Barcodes page without saving." ',
       'class="buttontext" />';
  echo '<input type="button" value="See Barcode List" ',
       'title="Shows all the reference barcodes ',
       'and associated barcode indexes." ',
       'onclick="javascript:barcodeReferenceWindow()" /><br /><br />';
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // foreach ($_SESSION['errors'] as $error)
  }  // if (isset($_SESSION['errors']))
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext" >',
      '<b><i>* Required Fields</i></b></span></p>';
 echo '<p style="text-align: left; margin: 2px;"><span class="requiredtext">',
      '* Barcode Index</span>';
 echo '<input type="text" name="barcode_index" size="60" class="inputtext" ',
      'title="Only the characters A, C, G, and T are permitted." ',
      'value="',$_SESSION['barcode_index'],'" /></p>';
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
