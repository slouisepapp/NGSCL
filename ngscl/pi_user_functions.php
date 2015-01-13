<?php
require_once 'constants.php';
// *********************************************************************
// This class defines primary investigator for an input
// primary_investigator_uid.
// *********************************************************************
class PrimaryInvestigatorCapability
{
  function __construct ($dbconn, primary_investigator_uid)
  {
    // Create pi role.
    // Grant appropriate privileges to pi role.
      // Grant select on pi_ngscl_role table.
      // Grant insert on deleted project log tables.
      // Grant pi_user role to pi role.
    // Create the pi views and assign appropriate privileges.
  }  // function __construct ($dbconn, primary_investigator_uid);
  // ****
  // This function creates a view and grants the appropriate
  // privilege to the pi role.
  // ****
  function create_view ($dbconn)
  {
     // Loop through the pi user views.
     foreach ($pi_user_view_array as $key => $row)
     {
       // Create or replace pi view.
       // Grant select on view-only pi views to pi role.
       // Grant all on pi views that are not view-only to pi role.
     }  // foreach ($pi_user_view_array as $key => $row)
  }  // function create_view 
}  // class PrimaryInvestigatorCapability
// *********************************************************************
?>
