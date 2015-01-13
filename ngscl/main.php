<?php
session_start();
$insecureSID = session_id();
session_unset();
require_once('db_fns.php');
require_once('constants.php');
$filename = $upload_dir . "/" . $session_file_name;
if (write_private_file ($filename, $insecureSID))
{
  $error = "";
} else {
  $error = "Problem writing the session.";
}  // if (! write_private_file ($filename, $insecureSID))
// If user hit the submit button, log on and continue to next page.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>',$abbreviated_app_name,'</title>';
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
.style2 {color: #999999}
a:link {
  color: #0000FF;
}
a:visited {
  color: #000080;
}
-->
</style>
<?php
  readfile("text_styles.css");
  echo '</head>';
  echo '<body class="twoColElsLtHdr" ',
       'onload="document.Login.submit_login.focus();">';
  echo '<div class="style1" id="container">';
  echo '<div id="header" align="center">';
  echo '<hi><img src="images/NGSCL_logo.png" width="350" height="25" /></h1>';
  echo '<h1 align="center"><span class="titletext">',
       $app_name,'</span></h1>';
?>
  </div>
    <div align="center">
      <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
<?php
  echo '<form id="Login" name="Login" method="post" ',
       'action="https://',$website,':',$secure_port,'/',
       $website_directory,
       '/login.php">';
  echo '<br />';
  echo '<p><input type="submit" name="submit_login" value="Login" ',
       'title="Log on" /></p>';
  if (strlen (trim ($error)) > 0)
  {
    echo '<span class="errortext">'.$error.'</span><br />';
  }  // if (strlen (trim ($error)) > 0)
?>
</form>
</div>
<br class="clearfloat" />
<br />
<div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
<!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
