<?php
require '../vendor/autoload.php';  // Adjust the path as necessary

session_start();

//Creates a Google Client
function createGoogleClient() {
    $client = new Google\Client();
    $client->setApplicationName("Your Application Name");
    $client->setScopes(Google\Service\Oauth2::USERINFO_EMAIL);
    $client->setAuthConfig('path/to/credentials.json'); // Path to your credentials file
    $client->setRedirectUri('http://localhost:8080/auth.php'); // Your redirect URI
    return $client;
}

//Get user information
function getUserInfo($client) {
    
    $oauth2 = new Google\Service\Oauth2($client);
    return $oauth2->userinfo->get();
}

//Handles authentication
function handleAuth() {

    $client = createGoogleClient();
    
    // Check if we have an authorization code in the query string
    if(isset($_GET['code'])){

        //Exchange authorization code for an access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token['access_token']);

        //Get user info
        $userInfo = getUserInfo($client);

        //Store user info in session or database as needed
        $_SESSION['user'] = $userInfo;

        //Redirection to home_page
        header('Location: /pages/welcome.php');
        exit();
    } 
    
    else{

        //Generate a URL to request access from Google's OAuth 2.0 server
        return $client->createAuthUrl();
    }
}









include 'auth_page.php';  //Include the HTML from auth_page.php