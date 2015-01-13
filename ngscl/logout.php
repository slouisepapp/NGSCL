<?php
  require_once('constants.php');
  session_start();
  session_unset();
  session_destroy();
  session_write_close();
  header("location:http://".$website.":".$app_port."/" .
         $website_directory . "/main.php");
?>
