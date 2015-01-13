<?php
session_start();
require_once 'db_fns.php';
require_once 'project_functions.php';
require_once 'constants.php';
require_once  'user_view.php';
// Determine what action brougt us here and process accordingly.
if (isset($_POST['process']))
{
  if ($_POST['process'] ==1)
  {
    if (isset($_POST['submit_update']))
    {
      // Put everything from post into session.
      foreach ($_POST as $thislabel => $thisvalue)
      {
        if (($thislabel != "PHPSESSID") &&
            ($thislabel != "form_project_info"))
        {
          $_SESSION[$thislabel] = $thisvalue;
        }
      }  // foreach ($_POST as $thislabel => $thisvalue)
      header("location: update_project_info.php");
      exit;
    }  //if (isset($_POST['submit_update']))
  }  // if ($_POST['process'] ==1)
}  // if (isset($_POST['process']))
$dbconn = database_connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
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
<form method="post" action="<?php $_SERVER['PHP_SELF']; ?>"
 name="form_project_info">
<input type="hidden" name="process" value="1" />
<?php
$project_uid = (isset ($_SESSION['project_uid']) ?
 $_SESSION['project_uid'] : 0);
if ($project_uid > 0)
{
  $result = pg_query($dbconn,"
   SELECT project_name,
          $project_view.primary_investigator_uid,
          $primary_investigator_view.name as pi_name,
          ref_run_type.run_type,
          $project_view.status,
          creation_date,
          project_description,
          analysis_notes,
          admin_comments,
          COALESCE (contact_uid, -1) AS contact_uid
     FROM $project_view,
          $primary_investigator_view,
          ref_run_type
    WHERE project_uid = $project_uid AND
          $project_view.primary_investigator_uid =
           $primary_investigator_view.primary_investigator_uid AND
          $project_view.ref_run_type_uid = ref_run_type.ref_run_type_uid");
  if (!$result)
  {
    echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
  } else {
    $row_meta = pg_fetch_assoc ($result);
    $contact_name = "";
    if ($row_meta['contact_uid'] > 0)
    {
      $result_contact = pg_query($dbconn, "
       SELECT name
         FROM $contact_view
        WHERE contact_uid = ".$row_meta['contact_uid']);
      if (!$result_contact)
      {
        echo '<span class="errortext">',pg_last_error($dbconn),'</span><br />';
      } else {
        $contact_name = pg_fetch_result ($result_contact, 0, 0);
      }  // if (!$result_contact)
    }  // if ($row_meta['contact_uid'] > 0)
    $run_list = run_list_for_project ($dbconn, $project_uid);
    echo '<br /><br />';
    echo '<span align="center" class="displaytext">Primary Investigator: ',
         $row_meta['pi_name'],
         '</span><br />';
    echo '<span align="center" class="displaytext">Project Name: ',
         $row_meta['project_name'],
         '</span><br />';
    echo '<span align="center" class="displaytext">Run Type: ',
         $row_meta['run_type'],
         '</span><br />';
    echo '<span align="center" class="displaytext">Status: ',
         $row_meta['status'],
         '</span><br />';
    echo '<span align="center" class="displaytext">Creation Date: ',
         $row_meta['creation_date'],
         '</span><br />';
    echo '<span align="center" class="displaytext">Contact: ',
         $contact_name,
         '</span><br />';
    echo '<span align="center" class="grayed_out">Run List: ',
         $run_list,
         '</span><br /><br />';
    echo '<div align="center" style="font-family: times, serif; color: blue; ',
         'width: 450px; height: 30px; ',
         'overflow: auto; padding: 5px;">',
         '<b>Description:</b> ',
         td_ready($row_meta['project_description']),
         '</div><br />';
    echo '<div align="center" style="font-family: times, serif; color: blue; ',
         'width: 450px; height: 30px; ',
         'overflow: auto; padding: 5px;">',
         '<b>Analysis Notes:</b> ',
         td_ready($row_meta['analysis_notes']),
         '</div>';
    if ($_SESSION['app_role'] != 'pi_user')
    {
      echo '<br />';
      // Determine whether to display the administrator comments.
      if (isset($_POST['submit_admin_comments_on']))
      {
        echo '<div align="center" style="font-family: times, serif; ',
             'color: blue; width: 450px; height: 30px; ',
             'overflow: auto; padding: 5px;">',
             '<b>Administrator Comments:</b> ',
             td_ready($row_meta['admin_comments']),
             '</div>';
        echo '<br />';
        echo '<input type="submit" value="Admin Comments Off" ',
             'name="submit_admin_comments_off" class="buttontext" ',
             'title="Remove the administrator comments from the display." />';
      } else {
        echo '<br />';
        echo '<input type="submit" value="Display Admin Comments" ',
             'name="submit_admin_comments_on" class="buttontext" ',
             'title="Display the administrator comments." />';
      }  // if (isset($_POST['submit_admin_comments_on']))
    }  // if ($_SESSION['app_role'] != 'pi_user')
  }  // if (!$result)
} else {
  echo '<span class="errortext">No project.</span><br />';
}  if ($project_uid > 0)
  if ($_SESSION['app_role'] != 'pi_user')
  {
    echo '<input type="submit" name="submit_update" value="Update" ',
         'title="Update values of project information." class="buttontext"/>';
  }  // if ($_SESSION['app_role'] != 'pi_user')
  if (isset($_SESSION['errors']))
  {
    echo '<br />';
    foreach ($_SESSION['errors'] as $error)
      echo '<span class="errortext">'.$error.'</span><br />';
  }  // if (isset($_SESSION['errors']))
?>
</form>
</body>
</html>
