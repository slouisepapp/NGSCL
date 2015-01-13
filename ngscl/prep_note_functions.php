<?php
// **************************************************************
// This function returns a one if there is an image for
// for the input library prep note and zero otherwise.
// **************************************************************
function prep_note_exists ($dbconn, $library_prep_note_uid)
{
  $result = pg_query ($dbconn, "
   SELECT COUNT(1) AS row_count
     FROM library_prep_note_image
    WHERE library_prep_note_uid = $library_prep_note_uid");
  if (!$result)
  {
    return FALSE;
  } elseif ($line = pg_fetch_assoc($result)) {
    if ($line['row_count'] > 0)
    {
      return TRUE;
    } else {
      return FALSE;
    }  // if ($line['row_count'] > 0)
  }  // if (!$result)
}  // function prep_note_exists
?>
