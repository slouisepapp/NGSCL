The NGSCL application is a web application for tracking
NextGen sequencing runs, projects, and samples.
Author: Suzanne Papp
For support contact spapp@scripps.edu

The NGSCL application requires:
 - PHP at least version 5.4.28
 - Apache at least version 2.4.3
 - PostgreSQL at least version 9.3.5
To install the application follow these steps.
1) Copy the ngscl directory to apache root.
2) Create a large_objects directory that is read/write by postgres
for uploading images files.
3) Create an uploads directory that is read/write by daemon
for uploading text files.
4) Run ngscl_create_db.SQL as postgres on postgres database.
5) Run ngscl_schema_and_initial_data.SQL as postgres on ngscl database.
6) Modify the configure.php file on ngscl.
