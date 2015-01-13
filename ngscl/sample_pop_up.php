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
require_once 'db_fns.php';
require_once 'constants.php';
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
  echo '<title>Sample Information, ',$abbreviated_app_name,'</title>';
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
       'Sample Information - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  if (isset ($_GET['sample_uid']) &&
      trimmed_string_not_empty ($_GET['sample_uid']))
  {
    $_SESSION['sample_uid'] = $_GET['sample_uid'];
    $sample_uid = $_SESSION['sample_uid'];
    $result_info = pg_query($dbconn,"
     SELECT sample_uid,
            sample_name,
            sample_description,
            $project_view.project_uid,
            project_name,
            name as primary_investigator_name,
            $sample_view.status,
            barcode,
            barcode_index,
            species,
            sample_type,
            batch_group,
            concentration,
            volume,
            coalesce (concentration, 0)*coalesce (volume, 0) AS amount,
            rna_integrity_number,
            $sample_view.comments
          FROM $sample_view,
               $project_view,
               $primary_investigator_view
         WHERE $sample_view.sample_uid = $sample_uid AND
               $sample_view.project_uid = $project_view.project_uid AND
               $project_view.primary_investigator_uid =
                $primary_investigator_view.primary_investigator_uid");
    if (!$result_info)
    {
      echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
    } else {
      // Get every run which includes samples from this project.
      $run_list = "";
      $result_run = pg_query ($dbconn, "
       SELECT DISTINCT run_number
         FROM $run_view,
              $run_lane_view,
              $run_lane_sample_view
        WHERE $run_lane_sample_view.sample_uid = $sample_uid AND
              $run_view.run_uid = $run_lane_view.run_uid AND
              $run_lane_view.run_lane_uid = $run_lane_sample_view.run_lane_uid
        ORDER BY run_number");
      if (!$result_run)
      {
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        for ($i=0; $i < pg_num_rows ($result_run); $i++)
        {
          $run_list .= ", " . pg_fetch_result ($result_run, $i, 0);
        }  // for ($i=0; $i < pg_num_rows ($result_run); $i++)
        $run_list = ltrim ($run_list, ", ");
      }  // if (!$result_run)
      $row_info = pg_fetch_assoc ($result_info);
      echo '<p style="text-align:left;" class="displaytext"><b>Sample:</b> ',
           $row_info['sample_name'],
           '<br />';
      echo '<b>Sample Description:</b> ',
           $row_info['sample_description'],
           '<br />';
      echo '<b>Project:</b> ',
           $row_info['project_name'],
           '<br />';
      echo '<b>Primary Investigator:</b> ',
           $row_info['primary_investigator_name'],
           '<br />';
      echo '<b>Status:</b> ',
           $row_info['status'],
           '<br />';
      echo '<b>Barcode:</b> ',
           $row_info['barcode'],
           '<br />';
      echo '<b>Barcode Index:</b> ',
           $row_info['barcode_index'],
           '<br />';
      echo '<b>Species:</b> ',
           $row_info['species'],
           '<br />';
      echo '<b>Sample Type:</b> ',
           $row_info['sample_type'],
           '<br />';
      echo '<b>Batch Group:</b> ',
           $row_info['batch_group'],
           '<br />';
      if ($use_sample_bonus_columns)
      {
        echo '<b>Concentration:</b> ',
             $row_info['concentration'],
             '<br />';
        echo '<b>Volume:</b> ',
             $row_info['volume'],
             '<br />';
        $amount = ($row_info['amount'] > 0 ? $row_info['amount'] : "");
        echo '<b>Amount:</b> ',
             $amount,
             '<br />';
        echo '<b>RIN score:</b> ',
              $row_info['rna_integrity_number'],
             '<br />';
      }  // if ($use_sample_bonus_columns)
      echo '<b>Run List:</b> ',
           $run_list,
           '<br /></p>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 60px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Comments:</b></span><br />',
           td_ready($row_info['comments']),
           '</div>';
    }  // if (!$result_info)
    if ($_SESSION['app_role'] != 'pi_user')
    {
      echo '<br />';
      echo '<table id="archive_table" border="1">';
      echo '<thead><tr>';
      echo '<th class="thBlueBorder" scope="col" style="text-align:center;">',
           'Archive</th>';
      echo '<th class="thBlueBorder" scope="col" style="text-align:center;">',
           'Freezer</th>';
      echo '<th class="thBlueBorder" scope="col" style="text-align:center;">',
           'Shelf Number</th>';
      echo '<th class="thBlueBorder" scope="col" style="text-align:center;">',
           'Box Number</th>';
      echo '<th class="thBlueBorder" scope="col" style="text-align:center;">',
           'Box Position</th>';
      echo '</tr></thead>';
      echo '<tbody>';
      // Select all the archive types from the reference table.
      $result_arch_type = pg_query ($dbconn, "
       SELECT ref_archive_name_uid,
              archive_name
         FROM ref_archive_name
        ORDER BY ref_archive_name_uid");
      if (!$result_arch_type)
      {
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        for ($i=0; $i<pg_num_rows ($result_arch_type); $i++)
        {
          echo '<tr>';
          $row_arch_type = pg_fetch_assoc ($result_arch_type);
          $ref_archive_name_uid = $row_arch_type['ref_archive_name_uid'];
          $archive_name = $row_arch_type['archive_name'];
          // Select the sample record for this archive type.
          $result_arch = pg_query ($dbconn, "
           SELECT freezer,
                  shelf_number,
                  box_number,
                  box_position
             FROM archive
            WHERE sample_uid = $sample_uid AND
                  ref_archive_name_uid = $ref_archive_name_uid");
          if (!$result_arch)
          {
            // If there is no record for this archive type, write a blank row.
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch_type['archive_name']),
                 '</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 '&nbsp;</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 '&nbsp;</td>';
          } else {
            // Write the sample row for this archive type.
            $row_arch = pg_fetch_assoc ($result_arch);
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch_type['archive_name']),
                 '</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch['freezer']),
                 '</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch['shelf_number']),
                 '</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch['box_number']),
                 '</td>';
            echo '<td class="tdBlueBorder" style="text-align:center;">',
                 td_ready($row_arch['box_position']),
                 '</td>';
          }  // if (!$result_arch)
          echo '</tr>';
        }  // for ($i=0; $i<pg_num_rows ($result_arch_type); $i++)
      }  //if (!$result_arch_type)
      echo '</tbody>';
      echo '</table>';
    }  // if ($_SESSION['app_role'] != 'pi_user')
  } else {
    echo '<span class="errortext">No sample selected.</span>';
  }  // if (isset ($_GET['sample_uid']) &&...
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
