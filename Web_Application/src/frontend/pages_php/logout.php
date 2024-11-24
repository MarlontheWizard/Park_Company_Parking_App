<?php
session_start();
/*
For logging out, we do not want to 
modify the database. This action only
affects the session state. 
*/

// Destroy the session
session_unset(); 
session_destroy(); 

// Redirect to login page
header("Location: index.php"); 
exit();
?>
