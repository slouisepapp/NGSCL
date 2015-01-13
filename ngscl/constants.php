<?php
require_once ('configure.php');
// Basic Application Constants
$session_file_name = "session.txt";
$app_name = "TEST NGS Core LIMS";
$abbreviated_app_name = "TEST NGSCL";
$org_name = "NGS Core";
$db_name = "ngscl";
$schema_name = "test_illumina";
$website_directory = "test_illumina";
$document_root_dir = $apache_root_dir . "/" . $website_directory;
$footer_text = $app_name." &copy;2010";
// Upload File Constants
$max_prep_note_file_size = 5242880;
$max_text_file_size = 5242880;
// General Report Constants
$csv_separator = "\",\"";
$export_comment_symbol = "##";
// Primary Investigator Constants
$array_primary_investigator_status_values = array(0 => 'Active',
                                                  1 => 'Archive');
// Project Constants
$array_project_status_values = array(0 => 'Active',
                                     1 => 'Completed',
                                     2 => 'Holding');
// Run Constants
$num_run_lanes = 8;
$expected_num_stats_upload_cols = 15;
// Sample Constants
$array_sample_status_values = array(0 => 'Active',
                              1 => 'Holding',
                              2 => 'Archive',
                              3 => 'Trash');
$undeclared_barcode = "TBD";
$barcode_separator = "_BC";
$max_batch_group_length = 15;
$max_sample_name_length = 8;
$update_sample_name_length = 37;
$sample_format_msg = 'Sample names must be no more than ' .
 $max_sample_name_length .
 ' characters. Alphanumeric characters, hyphens, ' .
 'and underscores ONLY. This is an Illumina software requirement.';
// Regional Constants
$currency = '$';
// Primary Investigator User Constants
$pi_user_view_array = array (
 0 => array ("table_name" => 'contact',
             "view_only" => TRUE),
 1 => array ("table_name" => 'primary_investigator',
             "view_only" => TRUE),
 2 => array ("table_name" => 'project',
             "view_only" => TRUE),
 3 => array ("table_name" => 'project_log',
             "view_only" => FALSE),
 4 => array ("table_name" => 'project_log_run_lane',
             "view_only" => FALSE),
 5 => array ("table_name" => 'run',
             "view_only" => TRUE),
 6 => array ("table_name" => 'run_lane',
             "view_only" => TRUE),
 7 => array ("table_name" => 'run_lane_sample',
             "view_only" => TRUE),
 8 => array ("table_name" => 'sample',
             "view_only" => TRUE)
);
// Sample fields used only by some configurations.
$use_sample_bonus_columns = TRUE;
// Mouseover titles.
$sample_mouseover = "Sample name must be no more than ".
                    $max_sample_name_length .
                    " characters. " .
                    "Only alphanumeric characters and underscore allowed. ";
// Post-run QA data.
$array_require_qa_fields = array (
 'lane_number' => array (
  'header' => 'Lane',
  'position' => -1),
 'sample_name' => array (
  'header' => 'Sample ID',
  'position' => -1),
 'species' => array (
  'header' => 'Sample Ref',
  'position' => -1),
 'barcode_index' => array (
  'header' => 'Index',
  'position' => -1),
 'barcode' => array (
  'header' => 'Description',
  'position' => -1),
 'project_name' => array (
  'header' => 'Project',
  'position' => -1),
 'yield' => array (
  'header' => 'Yield (Mbases)',
  'position' => -1),
 'percent_pf' => array (
  'header' => '% PF',
  'position' => -1),
 'num_reads' => array (
  'header' => '# Reads',
  'position' => -1),
 'percent_raw_clusters' => array (
  'header' => '% of raw clusters per lane',
 'position' => -1),
 'percent_ge_q30_bases' => array (
  'header' => '% of >= Q30 Bases (PF)',
  'position' => -1),
 'mean_quality_score' => array (
  'header' => 'Mean Quality Score (PF)',
  'position' => -1));
?>
