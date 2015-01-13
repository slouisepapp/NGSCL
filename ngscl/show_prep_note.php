<?php
header("Content-type: image/jpeg");
$image_number = $_GET['image_number'];
require_once('constants.php');
$file_name = $large_object_dir .
             "/tmp" .
             $image_number .
             ".jpg";
$jpeg = fopen ($file_name, "r");
$image = fread ($jpeg, filesize ($file_name));
echo $image;
?>
