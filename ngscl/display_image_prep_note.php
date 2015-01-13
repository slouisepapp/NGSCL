<?php
session_start();
require_once('db_fns.php');
require_once('prep_note_functions.php');
require_once('constants.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Library Prep Note, ',$abbreviated_app_name,'</title>';
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
<?php
  readfile("text_styles.css");
?>

</head>
<body>
<?php
$library_prep_note_uid = $_GET['library_prep_note_uid'];
$dbconn = database_connect();
// See if there is an image for this library_prep_note_uid.
if (prep_note_exists ($dbconn, $library_prep_note_uid))
{
  // Loop through every image for this library prep note.
  $result_image_number = pg_query ($dbconn, "
   SELECT image_number
     FROM library_prep_note_image
    WHERE library_prep_note_uid = $library_prep_note_uid
    ORDER BY image_number");
  if (!$result_image_number)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    for ($i=0; $i<pg_num_rows($result_image_number); $i++)
    {
      $row_image_number = pg_fetch_assoc ($result_image_number);
      $image_number = $row_image_number ['image_number'];
      $temp_file = $large_object_dir . '/tmp' . $image_number . '.jpg';
      $result_export = pg_query ($dbconn, "
       SELECT export_prep_note_image (
        $library_prep_note_uid, $image_number, '$temp_file')");
      if (!$result_export)
      {
        echo 'Form:',$image_number,'.  ',pg_last_error();
      } elseif (pg_fetch_result ($result_export, 0, 0) == 't') {
        echo '<p align="center">';
        while ($line = pg_fetch_array($result_export))
        {
          echo '<IMG SRC=show_prep_note.php?image_number=',
               $image_number,'>';
        }  // while ($line = pg_fetch_array($result_export)) 
        echo '</p>';
      } else {
        echo 'Form:',$image_number,'. Could not retrieve image.';
      }  // if ($result_export)
    }  // for ($i=0; $i<pg_num_rows($result_image_number); $i++)
  }  // if (!$result_image_number)
} else {
  echo '<h2>This library prep note has no images.</h2>';
}  // if (prep_note_exists ($dbconn, $library_prep_note_uid))
pg_close ($dbconn);
?>
</body>
</html>
