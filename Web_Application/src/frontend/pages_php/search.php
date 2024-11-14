<?php
require_once '/var/www/html/vendor/autoload.php';
include 'header_visibility.php'; 
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start the session only if it's not already started
}

// Include the HTML structure for the dashboard
include '../pages_html/search.html'; 
?>