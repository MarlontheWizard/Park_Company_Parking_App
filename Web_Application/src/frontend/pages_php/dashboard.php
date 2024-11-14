<?php
require_once '/var/www/html/vendor/autoload.php';
include 'header_visibility.php';  

if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start the session only if it's not already started
}
// Check if session is set and contains the user data
if (!isset($_SESSION['user'])) {
    exit();  // If session is not set, exit
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit();
}

// Retrieve user information from session (ensure it's stored as an array)
$userInfo = $_SESSION['user'];  // User information should be in the 'user' session variable

// Database connection
require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';
$mysqli = OpenConn();

// Retrieve user information from the database based on the user's email
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $userInfo['email']);  // Access email from session array
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists in the database
if ($result->num_rows > 0) {
    // Fetch user as an associative array using fetch_assoc()
    $dbUserInfo = $result->fetch_assoc();
} else {
    echo "User not found in the database.";
    exit();
}

CloseConn($mysqli);


include '../pages_html/dashboard.html';
?>