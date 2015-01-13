<?php
session_start();
require_once('db_fns.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
<?php
echo '<form method="post" action="" name="form_prep_note_image">';
$library_prep_note_uid = (isset ($_SESSION['library_prep_note_uid']) ?
 $_SESSION['library_prep_note_uid'] : 0);
if ($library_prep_note_uid > 0)
{
  echo '<p style="margin: 2px;">',
       '<input type="button" value="View Prep Note" ',
       'style="float: left" class="buttontext" ',
       'title="View library prep note." ',
       'onclick="javascript:view_prep_noteWindow(\'',$library_prep_note_uid,'\')" /></p>';
  echo '<p style="margin: 2px;">',
       '<input type="button" value="Upload Prep Note" ',
       'style="float: left" class="buttontext" ',
       'title="Upload an image file for the library prep note." ',
       'onclick="javascript:upload_prep_noteWindow(\'',$library_prep_note_uid,'\')" /></p>';
} else {
  echo '<span class="errortext">No library prep note</span>';
}  // if ($library_prep_note_uid > 0)
echo '</form>';
?>
</body>
</html>
