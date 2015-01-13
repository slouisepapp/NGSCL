<?php
require_once('constants.php');
require_once('db_fns.php');
$message = "";
// If not logged into unsecure http port.
if ($_SERVER['SERVER_PORT'] != $app_port)
{
  $filename = $upload_dir . "/" . $session_file_name;
  $insecureSID = file_get_contents ($filename);
  if (! $insecureSID)
  {
    $message = "Error reading session.";
  } else {
    session_id ($insecureSID);
  }  // if (! $insecureSID)
}  // if ($_SERVER['SERVER_PORT'] != $app_port)
$_SERVER['DOCUMENT_ROOT'] = $document_root_dir;
session_start();
// If user hit the submit button, log on and continue to next page.
if (isset($_POST['process'])) {
  if ($_POST['process'] == 1) {
    if (isset ($_POST['user']) && strlen (trim ($_POST['user'])) > 0)
    {
      $_SESSION['user'] = $_POST['user'];
      if (isset ($_POST['pass']) && strlen (trim ($_POST['pass'])) > 0)
      {
        // Set the session application constants.
        $_SESSION['pass'] = $_POST['pass'];
        $_SESSION['db_name'] = $db_name;
        $_SESSION['schema_name'] = $schema_name;
        $_SESSION['postgres_port'] = $postgres_port;
        $_SESSION['website_directory'] = $website_directory;
        $dbconn = database_connect();
        if (! $dbconn) {
          $message = "Login unsuccessful.  Please try again.";
          unset ($_SESSION['user']);
        } else {
          // Get user information.
          $my_user = new NgsclUser ($dbconn, $_SESSION['user']);
          $_SESSION['app_role'] = $my_user->app_role;
          $_SESSION['admin_role'] = $my_user->admin_role;
          $my_search_pi = new SessionSearchPi (
           $dbconn, $_SESSION['app_role'], $_SESSION['user']);
          $_SESSION['search_pi_uid'] = $my_search_pi->search_pi_uid;
          // ***
          // Assign the appropriate item name
          // for tables that have pi views.
          // ***
          foreach ($pi_user_view_array as $key => $row)
          {
            $tablename = $row['table_name'];
            $my_corresponding_view = new NgsclCorrespondingView (
             $dbconn, $tablename, $_SESSION['app_role'],
             $_SESSION['user'], $_SESSION['search_pi_uid']);
            $_SESSION[$tablename] = $my_corresponding_view->from_item;
          }  // foreach ($pi_user_view_array as $key => $row)
          header ("location: http://".$website.":".$app_port."/".
                  $website_directory . "/welcome.php");
        }  // if (! $dbconn = database_connect())
      } else {
        $message = "Please enter a username and password.";
        unset ($_SESSION['user']);
      }  // if (isset ($_POST['pass']) && strlen (trim ($_POST['pass'])) > 0)
    } else {
      $message = "Please enter a username and password.";
      unset ($_SESSION['user']);
    }  // if (isset ($_POST['user']) && strlen (trim ($_POST['user'])) > 0)
  }  // if ($_POST['process'] == 1)
}  // if (isset($_POST['process']))
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
  echo '<title>Login, ',$abbreviated_app_name,'</title>';
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
.style2 {color: #999999}
a:link {
  color: #0000FF;
}
a:visited {
  color: #000080;
}
-->
</style>
<?php
  readfile("text_styles.css");
?>
</head>

<?php
  echo '<body class="twoColElsLtHdr" onload="document.Login.user.focus();">';
  echo '<div class="style1" id="container">';
  echo '<div id="header" align="center">';
  echo '<hi><img src="images/NGSCL_logo.png" width="350" height="25" /></h1>';
  echo '<h1 align="center"><span class="titletext">',
       $app_name,'</span></h1>';
?>
  </div>
    <div align="center">
      <!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
<?php
  if (isset ($_SESSION['message']))
  {
    echo $_SESSION['message'].'<br />';
    unset ($_SESSION['message']);
  }
  echo '<form id="Login" name="Login" method="post" ',
       'action="https://',$website,':',$secure_port,'/',
       $website_directory,
       '/login.php">';
?>
  <input type="hidden" name="process" value="1" /><br />
  <p><label style="vertical-align:middle;" >Username
    <input type="text" name="user" id="user" class="inputtext" />
  </label></p>
  <p><label style="vertical-align:middle;" >Password
    <input type="password" name="pass" id="pass" class="inputtext" />
  </label></p>
  <p><input type="submit" name="submit" value="Submit" title="Log on" 
      class="buttontext" />
  </p>
</form>
<?php
if ($message != "") {
  print '<p><span class="errortext">'.$message.'</span></p>';
  }
?>
</div>
<br class="clearfloat" />
<br />
<div id="footer">
<?php
  echo '<p align="center">',$footer_text,'</p>';
?>
<!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
