<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start the session only if it's not already started
}


require_once '/var/www/html/vendor/autoload.php'; 
require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}


// Check that the email exists in the session
if (empty($_SESSION['user']['email'])) {
    die("Error: No email address found in session.");
}

// Get the user email and ID from the session
$email = $_SESSION['user']['email'];
$userId = $_SESSION['user']['id']; // Assuming user ID is stored in the session

//Display session details to log
#echo "User Email: " . $email . "<br>";
#echo "User ID: " . $userId . "<br>";

$mysqli = OpenConn();

try {
    /* begin_transaction -> Begins a mysqli transaction. 
       A transaction essentially allows our database to 
       revert to the previous state if a crash/error/loss 
       of internet connection occurs during modifications
       or updates. 
    */
    $mysqli->begin_transaction();

  
    /*
       The methods below execute the necessary updates needed in the database
       when an account deletion occurs. 
       
       These updates include: 

       1)Profile, or row, must be deleted from the users database. 
       2)Any reservations made by the user must be eliminated in the 
         reservations database. 
       3)Any transactions made by the user must be eliminated in the 
         transactions database. 

       The issue is that the user_id of a user, which originates in the users 
       database, is found in the other databases as a reference. Therefore,
       if we delete the profile from the users database first then our database
       will crash. The crash happens when the references are stateless, while 
       the entry cannot be null. 
    */

    account_deletion_updateTransaction_Table($mysqli, $userId);

    account_deletion_updateReservation_Table($mysqli, $userId);

    account_deletion_updateUserID_Table($mysqli, $email);


    // End database transaction
    $mysqli->commit();

    // Destroy the session and log the user out
    session_destroy();

    CloseConn($mysqli);

    // Redirect to the homepage
    header('Location: index.php');

    exit();

} 

catch (Exception $e) {
    
    /* Roll back the transaction in case of an error.
       This is where the transaction functionality shines, 
       allowing us to revert to the most recent copy of 
       the database. 
    */
    $mysqli->rollback();

    // Log and display the error
    error_log("Error deleting user account: " . $e->getMessage());
    echo "Error deleting account. Please try again later.";
    
    CloseConn($mysqli);

    exit();
}

?>
