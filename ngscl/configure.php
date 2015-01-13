<?php
// ***********************************************
// This file has the user configurable parameters.
// ***********************************************
$mail_server = "myinstitution.edu";
$apache_root_dir = "/usr/apache2/htdocs";
$website = "myweb.myinstitution.edu";
// The following must have read/write privileges for postgres owner.
$large_object_dir = "/export/http_uploads/ngscl/large_objects";
// The following must have read/write privileges for webserver owner.
$upload_dir = "/export/http_uploads/ngscl/uploads";
// Website application port.
$app_port = 80;
// Website ecure port is used for login.
$secure_port = 443;
// PostgreSQL database port.
$postgres_port = 5432;
// Run sample sheet variables.
$sample_sheet_recipe = "NA";
$sample_sheet_operator = "John Doe";
$sample_sheet_control = "0";
?>
