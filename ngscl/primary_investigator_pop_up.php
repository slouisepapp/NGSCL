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
  echo '<title>Primary Investigator Information, ',$abbreviated_app_name,'</title>';
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
       'Primary Investigator Information - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  if (isset ($_GET['primary_investigator_uid']))
  {
    $_SESSION['primary_investigator_uid'] = $_GET['primary_investigator_uid'];
    $primary_investigator_uid = $_SESSION['primary_investigator_uid'];
    $result_info = pg_query($dbconn,"
     SELECT primary_investigator_uid,
            name AS primary_investigator_name,
            status,
            email_address,
            phone_number,
            comments
          FROM $primary_investigator_view
         WHERE primary_investigator_uid = $primary_investigator_uid");
    if (!$result_info)
    {
      echo 'Error selecting primary_investigator information: ';
      echo pg_last_error ($dbconn);
    } else {
      $row_info = pg_fetch_assoc ($result_info);
      echo '<p style="text-align:left;" class="displaytext"><b>',
           'Primary Investigator:</b> ',
           $row_info['primary_investigator_name'],
           '<br />';
      echo '<b>Status:</b> ',
           $row_info['status'],
           '<br />';
      echo '<b>Email:</b> ',
           $row_info['email_address'],
           '<br />';
      echo '<b>Phone:</b> ',
           $row_info['phone_number'],
           '<br />';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 60px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Comments:</b></span><br />',
           td_ready($row_info['comments']),
           '</div>';
    }  // if (!$result_info)
  } else {
    echo '<span class="errortext">No primary investigator selected.</span>';
  }  // if (isset ($_SESSION['primary_investigator_uid']))
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
