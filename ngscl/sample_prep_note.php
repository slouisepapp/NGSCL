<?php
session_start();
// Put everything from post into session.
foreach ($_POST as $thislabel => $thisvalue) {
  if (($thislabel != "PHPSESSID") &&
      ($thislabel != "form_prep_note_select")) {
    $_SESSION[$thislabel] = $thisvalue;
    }
  }  // foreach ($_POST as $thislabel => $thisvalue)
require_once('db_fns.php');
require_once('constants.php');
$dbconn = database_connect();
// If this is the first time through, get the sample name.
$sample_uid = (isset($_SESSION['sample_uid']) ? $_SESSION['sample_uid'] : 0);
if (!isset($_POST['process']))
{
  if ($sample_uid > 0)
  {
    $result_name = pg_query ($dbconn, "
     SELECT sample_name
       FROM sample
      WHERE sample_uid = ".$sample_uid);
    if (!$result_name)
    {
      $_SESSION['sample_name'] = "";
    } else {
      $_SESSION['sample_name'] = pg_fetch_result($result_name, 0, 0);
    }  // if (!$result_name)
  }  // if ($sample_uid > 0)
}  // if (!isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Prep Notes for Sample, ',$abbreviated_app_name,'</title>';
?>
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

<body class="twoColElsLtHdr">
<div class="style1" id="container">
  <div id="header" style="text-align:center">
<?php
  echo '<h1 style="text-align:center"><span class="titletext">',
       'Prep Notes for Sample - ',$app_name,'</span></h1>';
?>
<!-- end #header --></div>
<?php
  if ($_SESSION['app_role'] == 'dac_grants')
  {
    $my_sidebar = new NgsclAdminSidebar ($_SESSION['user']);
  } elseif ($_SESSION['app_role'] == 'pi_user') {
    $my_sidebar = new NgsclPiSidebar ($_SESSION['user']);
  }  // if ($_SESSION['app_role'] == 'dac_grants')
  echo $my_sidebar->makeSidebar();
  echo '<div id="mainContent">';
  echo '<h3 class="grayed_out">Sample: ',$_SESSION['sample_name'],'</h3>';
  if ($sample_uid > 0)
  {
    echo '<form method="post" action="',$_SERVER['PHP_SELF'],
         '"name="form_prep_note_select" >',
         '<input type="hidden" name="process" value="1" />';
    echo '<table id="prep_note_table" border="1" >';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="thBlueBorder" scope="col" width="200" ',
         'style="text-align:center" >Library Prep Note</th>';
    echo '<th class="thBlueBorder" scope="col" width="200" ',
         'style="text-align:center">',
         'Creation Date</th>';
    echo '<th class="thBlueBorder" scope="col" width="200" ',
         'style="text-align:center">Comments</th>';
    echo '</tr>',
         '</thead>';
    echo '<tbody>';
    $result = pg_query($dbconn,"
     SELECT library_prep_note.library_prep_note_uid,
            library_prep_note_name,
            creation_date,
            comments
       FROM library_prep_note,
            library_prep_note_sample
      WHERE sample_uid = $sample_uid AND
            library_prep_note.library_prep_note_uid = 
             library_prep_note_sample.library_prep_note_uid 
      ORDER BY to_char (creation_date, 'YYYY-MM-DD'), library_prep_note_name");
    if (!$result)
    {
      echo '<tr>',pg_last_error ($dbconn),'</tr>';
    } else {
      for ($i=0;$i<pg_num_rows($result);$i++)
      {
        $row = pg_fetch_assoc ($result);
        $library_prep_note_uid = $row['library_prep_note_uid'];
        echo '<tr>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             '<a href="javascript:void(0)" ',
             'onclick="view_prep_noteWindow(\'',$library_prep_note_uid,'\');" ',
             'title="View library prep note." >',
             td_ready($row['library_prep_note_name']),
             '</a></td>';
        echo '<td class="tdBlueBorder" style="text-align:center">',
             td_ready($row['creation_date']),
             '</td>';
       echo '<td class="tdBlueBorder" style="text-align:left">',
            '<div style="width: 200px; height: 40px; overflow: ',
            'auto; padding: 5px;"><font face="sylfaen">',
            td_ready($row['comments']),
            '</font></div></td>';
        echo '</tr>';
      }  // for ($i=0;$i<pg_num_rows($result);$i++)
    }  // if (!$result)
    echo '</tbody>';
    echo '</table>';
    echo '</form>';
  }  // if ($sample_uid > 0)
?>
</div>
  <!-- end #mainContent -->
  <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
<?php
  echo '<p style="text-align:center">',$footer_text,'</p>';
?>
  <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
