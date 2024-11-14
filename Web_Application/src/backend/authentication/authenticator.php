<?php

session_start();

// Include the Google authentication logic
require 'google_authentication_attributes.php';
require '/var/www/html/vendor/autoload.php';
include_once '../database/handle_connection.php';  // Ensure this file contains your database connection setup


$client = createGoogleClient();

// Check if 'code' is in the URL
if (!isset($_GET['code'])) {
    // If no code is present, redirect to Google OAuth screen
    $authUrl = $client->createAuthUrl();
    header("Location: " . $authUrl); // Redirect the user to Google
    exit(); // Stop further execution
} 

else {
    // If 'code' is present, handle the OAuth callback
    handleAuth($client); // Call the handleAuth function to process the authentication
}


?>