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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Reference Barcode List, ',$abbreviated_app_name,'</title>';
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
<script src="library/sorttable.js" 
  language="javascript" 
    type="text/javascript"></script>
</head>

<body class="oneColElsLtHdr">
<div class="style1" id="container">
  <div id="header">
<?php
  echo '<h1 style="text-align: center"><span class="titletext">',
       'Reference Barcode List</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  $result = pg_query ($dbconn, "
   SELECT prep_type || 
           '$barcode_separator' ||
           barcode_number AS barcode_description,
          barcode_index
     FROM ref_prep_type,
          ref_barcode
    WHERE ref_prep_type.ref_prep_type_uid = ref_barcode.ref_prep_type_uid
    ORDER BY prep_type,
             barcode_number");
  if (! $result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    echo '<table id="barcode_table" border="1" class="sortable">';
    echo '<thead><tr>';
    echo '<th class="sorttable_alpha" scope="col" width="150" ',
         'style="text-align:center" >',
         'Barcode</th>';
    echo '<th class="sorttable_alpha" scope="col" width="150" ',
         'style="text-align:center">',
         'Barcode Index</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    // The undeclared barcode is the first row of the table.
    echo '<tr>';
    echo '<td class="tdBlueBorder" style="text-align:center">',
         td_ready($undeclared_barcode),'</td>';
    // The undeclared barcode has no index.
    echo '<td>&nbsp;</td>';
    echo '</tr>';
    for ($i=0;$i<pg_num_rows($result);$i++)
    {
      $row = pg_fetch_assoc ($result);
      echo '<tr>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['barcode_description']),'</td>';
      echo '<td class="tdBlueBorder" style="text-align:center">',
           td_ready($row['barcode_index']),
           '</td>';
        echo '</tr>';
    }  // for ($i=0;$i<pg_num_rows($result);$i++)
    echo '</tbody>';
    echo '</table>';
  }  // if (!$result)
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
