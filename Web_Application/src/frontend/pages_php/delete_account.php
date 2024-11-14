<?php
require_once '/var/www/html/vendor/autoload.php'; 


session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: login.php');
    exit();
}


require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';


$mysqli = OpenConn();

// Get the email of the logged-in user from the session
$email = $_SESSION['user']['email']; // Updated to use array syntax

// Prepare the SQL query to delete the user
$sql = "DELETE FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);

// Execute the query and check if it was successful
if ($stmt->execute()) {
    // If the account was deleted, destroy the session and log the user out
    session_destroy();

    // Redirect the user to the homepage
    header('Location: index.php');
    CloseConn($mysqli);

    exit();
} 

else {
    // If there was an error deleting the account
    echo "Error deleting account. Please try again later.";
    CloseConn($mysqli);
    exit();
}
?>