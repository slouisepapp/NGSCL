<html>
<head><title>File to Upload to Library Prep Note</title></head>
</html>
<body>
<?php
session_start();
require_once('constants.php');
require_once('db_fns.php');
require_once('prep_note_functions.php');
$dbconn = database_connect();
$library_prep_note_uid = $_GET['library_prep_note_uid'];
$max_in_megs = $max_prep_note_file_size / 1048576;
echo '<h3>Please choose a file to upload.</h3>';
echo '<span>Valid file types: png, jpg, gif, and bitmap.  ',
     'Maximum file size: ',$max_in_megs,' megabytes.</span>';
echo '<form enctype="multipart/form-data" action="image_to_prep_note.php" ',
     'method="POST">';
echo '<input type="hidden" name="library_prep_note_uid" value="',$library_prep_note_uid,'" />';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="',
     $max_prep_note_file_size,'" />';
echo 'File: <input name="userfile" type="file" size="25" ',
     'title="Choose an image file." />&nbsp;';
if (prep_note_exists ($dbconn, $library_prep_note_uid))
{
  echo '<input type="submit" name="submit_replace" value="Replace" ',
       'title="Upload file, replacing the images ',
       'for this library prep note."  />&nbsp;';
  echo '<input type="submit" name="submit_append" value="Append" ',
       'title="Upload file, adding to the images ',
       'for this library prep note."  />';
} else {
  echo '<input type="submit" name="submit_new" value="Upload" ',
       'title="Upload image for this library prep note." />';
}  // if (prep_note_exists ($dbconn, $library_prep_note_uid))
?>
</form>

</body>
</html>
