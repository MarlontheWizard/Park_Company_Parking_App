<?php
require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/Web_Application/src/backend/database/handle_connection.php';

include __DIR__ . '/header_visibility.php';  

if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}


// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit();
}


// Check if session is set and has the user data
if (!isset($_SESSION['user'])) {
    exit();  // If session is not set, exit
}


include __DIR__ . '/../pages_html/dashboard.html';
?>