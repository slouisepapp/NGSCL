<?php
// **************************************************************
// This class defines the samples.
// **************************************************************
class SampleTable
 extends ArrayObject
{
  function __construct (
  $dbconn, $sample_view, $project_view="", $project_uid=0)
  {
    $sample_array = array();
    if ($project_uid > 0)
    {
      // Select values from sample table for project.
      $result = pg_query ($dbconn, "
       SELECT sample_uid,
              sample_name,
              sample_description,
              $sample_view.status,
              barcode,
              barcode_index,
              species,
              sample_type,
              batch_group,
              $sample_view.comments
            FROM $sample_view,
                 $project_view
           WHERE $sample_view.project_uid = $project_uid AND
                 $sample_view.project_uid = $project_view.project_uid
           ORDER BY sample_name");
    } else {
      // Select values from sample table for all projects.
    }  // if ($project_uid > 0)
    if (!$result)
    {
    } else {
      for ($i=0; $i<pg_num_rows($result); $i++)
      {
        $sample_array[] = pg_fetch_assoc ($result);
      }  // for ($i=0; $i<pg_num_rows($result); $i++)
    }  // if (!$result)
    parent::__construct ($sample_array);
  }  // function __construct
  function report_custom_barcode ()
  {
    $report_string = "sample_name\tsample_description\t" .
     "status\tbarcode\tbarcode_index\t" .
     "species\tsample_type\tbatch_group\tcomments\r\n";
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $report_string .= $iterator->current()['sample_name'] .
       "\t\"" .
       $iterator->current()['sample_description'] .
       "\"\t\t\t\t\"" .
       $iterator->current()['species'] .
       "\"\t\t\"" .
       $iterator->current()['batch_group'] .
       "\t\r\n";
    }  // for ($iterator = $this->getIterator();
    return ($report_string);
  }  // function report_custom_barcode ()
  function display ()
  {
    $display_string = '<table border="1"><thead><tr>' .
     '<th>Batch Group</th><th>Sample Name</th><th>Sample Description</th>' .
     '<th>Species</th></tr></thead><tbody>';
    for ($iterator = $this->getIterator();
         $iterator->valid(); $iterator->next())
    {
      $display_string .= '<tr><td>' .
       td_ready ($iterator->current()['batch_group']) . '</td><td>' .
       td_ready ($iterator->current()['sample_name']) . '</td><td>' .
       td_ready ($iterator->current()['sample_description']) . '</td><td>' .
       td_ready ($iterator->current()['species']) .
       '</td></tr>';
    }  // for ($iterator = $this->getIterator();
    $display_string .= '</tbody></table>';
    return ($display_string);
  }  // function display ()
}  // class SampleTable
?>
