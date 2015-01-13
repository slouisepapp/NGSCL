<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue)
{
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_manage_barcodes"))
  {
    $_SESSION[$thislabel] = $thisvalue;
  }
}  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
$array_error = array();
$array_barcode = array();
// *******************************************************************
// This function returns an array of barcode data.
// *******************************************************************
function array_of_barcode_data ($dbconn, $ref_prep_type_uid, $barcode_separator)
{
  $array_barcode_data = array();
  if (strlen (trim ($ref_prep_type_uid)) < 1)
  {
    return $array_barcode_data;
  } else {
    $result = pg_query ($dbconn, "
     SELECT ref_barcode.ref_barcode_uid,
            ref_prep_type.ref_prep_type_uid,
            ref_prep_type.prep_type,
            ref_barcode.barcode_number,
            ref_prep_type.prep_type ||
             '$barcode_separator' ||
             ref_barcode.barcode_number
             AS barcode_description,
            ref_barcode.barcode_index
       FROM ref_barcode,
            ref_prep_type
      WHERE ref_prep_type.ref_prep_type_uid = $ref_prep_type_uid AND
            ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid
      ORDER BY ref_prep_type.prep_type,
               ref_barcode.barcode_number");
    if (!$result)
    {
      return pg_last_error ($dbconn);
    } else {
      // Loop through the barcode data.
      $line_number = 0;
      for ($i=0; $i < pg_num_rows($result); $i++)
      {
        $row = pg_fetch_assoc ($result);
        $array_barcode_data[$line_number]['ref_barcode_uid'] =
         $row['ref_barcode_uid'];
        $array_barcode_data[$line_number]['ref_prep_type_uid'] =
         $row['ref_prep_type_uid'];
        $array_barcode_data[$line_number]['prep_type'] =
         $row['prep_type'];
        $array_barcode_data[$line_number]['barcode_number'] =
         $row['barcode_number'];
        $array_barcode_data[$line_number]['barcode_index'] =
         $row['barcode_index'];
        $array_barcode_data[$line_number]['display_name'] =
         $row['barcode_description'] . ' (' . $row['barcode_index'] . ')';
        $line_number++;
      }  //for ($i=0; $i < pg_num_rows($result); $i++)
      return $array_barcode_data;
    }  // if (!result)
  }  // if (strlen (trim ($ref_prep_type_uid)) < 1)
}  // function array_of_barcode_data
// *******************************************************************
// Determine what action brought us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] == 1)
  {
    if (isset($_POST['submit_add_prep_type'])) {
      // Move to add prep type page.
      unset($_SESSION['submit_add_prep_type']);
      header("location: add_prep_type.php");
      exit;
    } elseif (isset($_POST['submit_delete_prep_type'])) {
      // Delete the prep type if it has no barcodes.
      if ($_SESSION['choose_prep_type'] > 0)
      {
        $result_count_barcodes = pg_query ($dbconn, "
         SELECT COUNT(1)
           FROM ref_barcode
          WHERE ref_prep_type_uid = " .
         $_SESSION['choose_prep_type']);
        if (!$result_count_barcodes)
        {
          $array_error[] = pg_last_error ($dbconn);
        } elseif (pg_fetch_result ($result_count_barcodes, 0, 0) > 0) {
          $array_error[] = "Prep type cannot be deleted as it has barcodes.";
        } else {
          $result_delete_prep_type = pg_query ($dbconn, "
           DELETE
             FROM ref_prep_type
            WHERE ref_prep_type_uid = " .
           $_SESSION['choose_prep_type']);
          if (!$result_delete_prep_type)
          {
            $array_error[] = pg_last_error ($dbconn);
          }  // if (!$result_delete_prep_type)
        }  // if (!$result_count_barcodes)
      }  // if ($_SESSION['choose_prep_type'] > 0)
    } elseif (isset($_POST['submit_add_barcode'])) {
      // Move to add barcode page.
      unset($_SESSION['submit_add_barcode']);
      header("location: add_barcode.php");
      exit;
    } elseif (isset($_POST['submit_update_barcode'])) {
      // Check if a barcode has been selected.
      if (isset ($_POST['ref_barcode_uid']))
      {
        // Move to update barcode page.
        unset($_SESSION['submit_update_barcode']);
        header("location: update_barcode.php");
        exit;
      } else {
        $array_error[] = "No barcode selected.";
      }  // if (!isset ($_POST['ref_barcode_uid']))
    } elseif (isset($_POST['submit_remove_last_barcode'])) {
      // Remove the largest barcode number for this prep type.
      $result_max_barcode = pg_query ($dbconn,
       "SELECT max (barcode_number)
          FROM ref_barcode
         WHERE ref_prep_type_uid = " .
       $_SESSION['choose_prep_type']);
      if (!$result_max_barcode)
      {
        $array_error[] = pg_last_error ($dbconn);
      } else {
        $result_delete_barcode = pg_query ($dbconn,
         "DELETE FROM ref_barcode
           WHERE ref_prep_type_uid = " .
                  $_SESSION['choose_prep_type'] . " AND
                 barcode_number = " .
                  pg_fetch_result ($result_max_barcode, 0, 0));
        if (!$result_delete_barcode)
        {
          $array_error[] = pg_last_error ($dbconn);
        }  // if (!$result_delete_barcode)
      }  // if (!$result_max_barcode)
    }  // if (isset($_POST['submit_add_prep_type']))
  }  // if ($_POST['process'] == 1)
} else {
  if (! isset ($_SESSION['choose_prep_type']))
  {
    $_SESSION['choose_prep_type'] = 0;
  }  // if (! isset ($_SESSION['choose_prep_type']))
  if (isset ($_SESSION['ref_barcode_uid']))
  {
    unset ($_SESSION['ref_barcode_uid']);
  }  // if (isset ($_SESSION['ref_barcode_uid']))
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Manage Barcodes, ',$abbreviated_app_name,'</title>';
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
</head>

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" align="center">
<?php
  echo '<h1 align="center"><span class="titletext">Manage Barcodes - ',
       $app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  foreach ($array_error as $error)
  {
    if (strlen (trim ($error)) > 0)
    {
      echo '<span class="errortext">'.$error.'</span><br />';
    }  // if (strlen (trim ($error)) > 0)
  }  // foreach ($array_error as $error)
?>
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_manage_barcodes" >
<input type="hidden" name="process" value="1" />
<?php
  echo '<br />';
  echo '<h2>Prep Type</h2>';
  // Build the list of all barcodes.
  $select_prep_type_value = (isset (
   $_SESSION['choose_prep_type']) ?
   $_SESSION['choose_prep_type'] : "");
  $array_barcode = array_of_barcode_data (
                    $dbconn, $select_prep_type_value, $barcode_separator);
  echo drop_down_table ($dbconn, "choose_prep_type", $select_prep_type_value,
                        "inputtext", "ref_prep_type",
                        "ref_prep_type_uid", "prep_type",
                        "Choose prep type.",
                        " ", "None", "None" -1); 
  echo '</p>';
  echo '<input type="submit" name="submit_add_prep_type" ',
       'value="Add Prep Type" title="Add prep type." class="buttontext" />';
  if ($select_prep_type_value > 0)
  {
    echo '<input type="submit" name="submit_delete_prep_type" ',
         'value="Delete Prep Type" title="Delete selected prep type." ',
         'onclick="return confirm(\'Are you sure you want to delete ',
         'this prep type?\');" class="buttontext" />';
  }  // if ($select_prep_type_value > 0)
  echo '<input type="button" value="See Barcode List" ',
       'title="Shows all the reference barcodes ',
       'and associated barcode indexes." ',
       'onclick="javascript:barcodeReferenceWindow()" /><br /><br />';
  if ($_SESSION['choose_prep_type'] > 0)
  {
    echo '<hr />';
    echo '<h2>Barcode</h2>';
    $prep_type = (isset ($array_barcode[0]['prep_type']) ?
     $array_barcode[0]['prep_type'] : "");
    echo '<input type="submit" name="submit_add_barcode" ',
         'value="Add Barcode" ',
         'title="Add barcode number and index for ',
         $prep_type,
         ' prep type." class="buttontext" />';
    if (count ($array_barcode) > 0)
    {
      echo '<input type="submit" name="submit_update_barcode" ',
           'value="Update Barcode" ',
           'title="Update selected barcode." class="buttontext" />';
      echo '<input type="submit" name="submit_remove_last_barcode" ',
           'value="Remove Last" ',
           'onclick="return confirm(\'Are you sure you want to remove ',
           'the last barcode for this prep type?\');" ',
           'title="Remove last barcode number for ',
           $prep_type,
           ' prep type." class="buttontext" /><br /><br />';
      $barcode_options = "";
      foreach ($array_barcode as $barcodevalue)
      {
        $barcode_options .= '<option value="' .
                             $barcodevalue['ref_barcode_uid'] .
                             '" >' .
                             $barcodevalue['display_name'] .
                             '</option>';
      }  // foreach ($array_barcode as $barcodevalue)
      echo '<span class="optionaltext">Barcodes for ',
           $prep_type,'</span><br />';
      echo '<select id="ref_barcode_uid" name="ref_barcode_uid" ' .
           'class="inputtext" size="15">' .
           $barcode_options .
           '</select>';
    }  // if (count ($array_barcode) > 0)
  }  // if ($_SESSION['choose_prep_type'] > 0)
?>
</form>
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
