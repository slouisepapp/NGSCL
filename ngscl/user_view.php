<?php
if (!isset ($_SESSION['user']))
{
  header ("location: https://".$website.":".$secure_port."/".
          $website_directory . "/login.php");
  exit;
}  // if (!isset ($_SESSION['user']))
if (!isset ($_SESSION['contact']))
{
  header ("location: https://".$website.":".$secure_port."/".
          $website_directory . "/login.php");
  exit;
}  // if (!isset ($_SESSION['contact']))
$contact_view = $_SESSION['contact'];
$primary_investigator_view = $_SESSION['primary_investigator'];
$project_view = $_SESSION['project'];
$run_view = $_SESSION['run'];
$run_lane_view = $_SESSION['run_lane'];
$run_lane_sample_view = $_SESSION['run_lane_sample'];
$sample_view = $_SESSION['sample'];
?>
