<?php
  session_start();
  require_once('db_fns.php');
  require_once('prep_note_functions.php');
  require_once('constants.php');
  $uploadfile = $upload_dir . "/" . basename($_FILES['userfile']['name']);
  if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    if (! copy($_FILES['userfile']['tmp_name'], $uploadfile)) {
      $upload_message = "File could not be moved to ".$upload_dir.".";
      echo '<span class="errortext">',$upload_message,'</span>';
      exit;
      }  // if (! copy($_FILES['userfile']['tmp_name'], $uploadfile))
  } else {
    $upload_message = "File upload unsuccessful.";
    echo '<span class="errortext">',$upload_message,'</span>';
    exit;
    }  // if (is_uploaded_file...
  if (isset($_POST['library_prep_note_uid']))
  {
    $library_prep_note_uid = $_POST['library_prep_note_uid'];
    $dbconn = database_connect();
    if (!$dbconn)
     { echo "Can't connect.  "; }
    // Get the library prep note identifier.
    // Determine whether the file should be an upload, replace, or append.
    $prep_note_number = "1";
    if (isset($_POST['submit_replace']))
    {
      // Replace
      // Delete library prep note images for this library prep note.
      $result_del = pg_query($dbconn,"
                 DELETE FROM library_prep_note_image
                  WHERE library_prep_note_uid = $library_prep_note_uid");
      if (!$result)
        echo '<span class="errortext">',pg_last_error($dbconn),
             '</span><br />';
    } elseif (isset($_POST['submit_append'])) {
      // Append
      // The upload image number is one more than the largest for this prep note.
      $result_prep_note_num = pg_query($dbconn,"
       SELECT coalesce (max (image_number), 0) + 1
         FROM library_prep_note_image
        WHERE library_prep_note_uid = $library_prep_note_uid");
      if (!$result_prep_note_num)
      {
        $prep_note_number = "";
      } else {
        $prep_note_number = pg_fetch_result ($result_prep_note_num, 0, 0);
        echo '<span class="errortext">',pg_last_error($dbconn),
             '</span><br />';
      }  // if (!$result_prep_note_num)
    }  // if (isset($_POST['submit_replace']))
    $result = pg_query($dbconn, "
     SELECT import_prep_note_image (
      $library_prep_note_uid, $prep_note_number, '$uploadfile')");
    if (!$result)
    {
      echo '<span class="errortext">Insert failed: ',
           pg_last_error($dbconn),'</span><br />';
    } elseif (pg_fetch_result ($result, 0, 0) == 't') {
      unlink($uploadfile);
      pg_close($dbconn);
      header("location: display_image_prep_note.php?library_prep_note_uid=".$library_prep_note_uid);
      exit;
    } else {
      echo '<span class="errortext">Insert failed.</span><br />';
    }  // if (!$result)
  } else {
    echo 'No library prep note identifier.';
  }  if (isset($_POST['library_prep_note_uid']))
  unlink($uploadfile);
  pg_close($dbconn);
?>
