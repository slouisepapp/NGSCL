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
require_once 'project_functions.php';
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
  echo '<title>Project Information, ',$abbreviated_app_name,'</title>';
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
       'Project Information - ',$app_name,'</span></h1>';
  echo '<!-- end #header --></div>';
  echo '<div id="mainContent">';
  // Set project_uid.
  if (isset ($_GET['project_uid']) &&
      trimmed_string_not_empty ($_GET['project_uid']))
  {
    $project_uid = $_GET['project_uid'];
  } elseif (isset ($_POST['project_uid'])) {
    $project_uid = $_POST['project_uid'];
  } else {
    $project_uid = -1;
  }  // if (isset ($_GET['project_uid']) &&...
  if ($project_uid > 0)
  {
    $result_info = pg_query($dbconn,"
     SELECT project_name,
            $primary_investigator_view.primary_investigator_uid,
            $primary_investigator_view.name AS primary_investigator_name,
            ref_run_type.run_type,
            $project_view.status,
            creation_date,
            project_description,
            analysis_notes,
            admin_comments,
            project_prep_comments,
            CASE WHEN prepped_and_ready THEN 'Yes'
                 ELSE 'No'
            END AS prepped_and_ready_string,
            COALESCE (contact_uid, -1) AS contact_uid
       FROM $project_view,
            $primary_investigator_view,
            ref_run_type
      WHERE project_uid = $project_uid AND
            $project_view.primary_investigator_uid =
             $primary_investigator_view.primary_investigator_uid AND
            $project_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
    if (!$result_info)
    {
      echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
    } else {
      $row_info = pg_fetch_assoc ($result_info);
      // Find the contact name.
      if ($row_info['contact_uid'] > 0)
      {
        $contact_uid = $row_info['contact_uid'];
        $result_contact = pg_query($dbconn,"
         SELECT name
           FROM $contact_view
          WHERE contact_uid = $contact_uid");
        if (!$result_contact)
        {
          echo '<span class="errortext">',
               pg_last_error($dbconn),'</span><br />';
        } else {
          $contact_name = pg_fetch_result ($result_contact, 0, 0);
        }  // if (!$result_contact)
      } else {
        $contact_name = "";
      }  // if ($row_info['contact_uid' > 0)
      // Get every run which includes samples from this project.
      $run_list = run_list_for_project ($dbconn, $project_uid);
      echo '<p style="text-align:left;" class="displaytext"><b>Project:</b> ',
           $row_info['project_name'],
           '<br />';
      echo '<b>Primary Investigator:</b> ',
           $row_info['primary_investigator_name'],
           '<br />';
      echo '<b>Run Type:</b> ',
           $row_info['run_type'],
           '<br />';
      echo '<b>Status:</b> ',
           $row_info['status'],
           '<br />';
      echo '<b>Creation Date:</b> ',
           $row_info['creation_date'],
           '<br />';
      echo '<b>Contact:</b> ',
           $contact_name,
           '<br />';
      echo '<b>Prepped and Ready:</b> ',
           $row_info['prepped_and_ready_string'],
           '<br />';
      echo '<b>Run List:</b> ',
           $run_list,
           '<br /></p>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 75px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Description:</b></span><br />',
           td_ready($row_info['project_description']),
           '</div>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 75px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Analysis Notes:</b></span><br />',
           td_ready($row_info['analysis_notes']),
           '</div>';
      echo '<div align="left" style="font-family: times, serif; color: blue; ',
           'width: 400px; height: 75px; ',
           'overflow: auto; padding: 5px;">',
           '<span style="font-family: arial, sans">',
           '<b>Project Prep Comments:</b></span><br />',
           td_ready($row_info['project_prep_comments']),
           '</div>';
      if ($_SESSION['app_role'] != 'pi_user')
        {
        // Determine whether to display the administrator comments.
        if (isset($_POST['submit_admin_comments_on']))
        {
          echo '<div align="left" style="font-family: times, serif; color: blue; ',
               'width: 400px; height: 50px; ',
               'overflow: auto; padding: 5px;">',
               '<span style="font-family: arial, sans">',
               '<b>Administrator Comments:</b></span><br />',
               td_ready($row_info['admin_comments']),
               '</div>';
          echo '<br />';
          echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
               'name="form_admin_comments_setting" >';
          echo '<input type="hidden" name="project_uid" value="',
               $project_uid,'" />';
          echo '<input type="submit" value="Admin Comments Off" ',
               'name="submit_admin_comments_off" class="buttontext" ',
               'title="Remove the administrator comments from the display." />';
          echo '</form>';
        } else {
          echo '<br />';
          echo '<form method="post" action="',$_SERVER['PHP_SELF'],'" ',
               'name="form_admin_comments_setting" >';
          echo '<input type="hidden" name="project_uid" value="',
               $project_uid,'" />';
          echo '<input type="submit" value="Display Admin Comments" ',
               'name="submit_admin_comments_on" class="buttontext" ',
               'title="Display the administrator comments." />';
          echo '</form>';
        }  // if (isset($_POST['submit_admin_comments_on']))
      }  // if ($_SESSION['app_role'] != 'pi_user')
    }  // if (!$result_info)
    echo '</tbody>';
    echo '</table>';
  } else {
    echo '<span class="errortext">No project selected.</span>';
  }  // if ($project_uid > 0)
?>
  <br />
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
