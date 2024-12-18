<?php
require '/var/www/html/vendor/autoload.php';
include_once '../database/handle_connection.php';

// Creates a Google Client
function createGoogleClient() {
    $client = new Google\Client();
    $client->setApplicationName("Park App");       
    $client->setScopes([Google\Service\Oauth2::USERINFO_EMAIL, Google\Service\Oauth2::USERINFO_PROFILE]);
    $client->setAuthConfig('/var/www/html/Secret_Key/client_secret_key.json'); // Path to client secret key 
    $client->setRedirectUri('http://localhost:8080/web_application/src/backend/authentication/authenticator.php'); 
    return $client;
}

// Get user information
function getUserInfo($client) {
    $oauth2 = new Google\Service\Oauth2($client);
    return $oauth2->userinfo->get();
}

// Handles authentication
// Handles authentication
function handleAuth($client) {

    // Open the database connection
    $mysqli = OpenConn(); // Using your OpenCon function to get the connection

    // If there's no authorization code, create the authentication URL and redirect the user to Google
    if (!isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
        exit(); // Ensure no further code executes after the redirect
    }

    // If authorization code is present, exchange it for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Check for errors in token exchange
    if (isset($token['error'])) {
        #echo "Error exchanging code: " . $token['error'];
        exit();
    }

    $client->setAccessToken($token['access_token']);

    // Get user info
    $userInfo = getUserInfo($client);

    // Convert the Google user info object to an associative array
    $userInfoArray = [

        'id'      => null,              //At this point we only have the google client information 
        'email'   => $userInfo->email,
        'name'    => $userInfo->name,
        'locale'  => $userInfo->locale,
        'picture' => $userInfo->picture
        
    ];

    // Store the user info in session as an array
    $_SESSION['user'] = $userInfoArray;

    // Check if the user exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $userInfo->email);  // Ensure this uses the object property for the query
    $stmt->execute();

    $result = $stmt->get_result();

    // User doesn't exist, generate user id and store profile in database
    if($result->num_rows == 0) {
        $sql = "INSERT INTO users (name, email, locale, picture) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $userInfo->name, $userInfo->email, $userInfo->locale, $userInfo->picture);
        
        if ($stmt->execute()){  //User information stored successfully
            
            $user_id = $mysqli->insert_id;

            $_SESSION['user']['id'] = $user_id; // Store the user_id in the session
            #echo "Stored data.";               // Display success message in log 
        } 
        
        else {
            
            //Error: Failed to store user data
            #echo "Error: " . $stmt->error;
        }
    } 
    
    else {
        
        /*
        The user exists. In this case, 
        1)Update the session attributes with the ID
        2)Update database with information in google account (it could change overtime)
        */

        $sql = "UPDATE users SET name = ?, locale = ?, picture = ? WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $userInfo->name, $userInfo->locale, $userInfo->picture, $userInfo->email);
        
        // Execute the query 
        if ($stmt->execute()) { // User information updated successfully

            //Update the session
            $sql = "SELECT user_id FROM users WHERE email = ?";
            $query = $mysqli->prepare($sql);
            $query->bind_param("s", $userInfo->email);

            $query->execute();
            $query->bind_result($userId);
            
            $query->fetch();
            
            $_SESSION['user']['id'] = $userId;

            if($_SESSION['user']['id'] == null){

                echo'Could not store user id into user session during google authentication.';
            }
        } 
        
        else {
            // Error: Failed to update user data
            #echo "Error: " . $stmt->error;
        }
    }

    // Close the database connection
    $stmt->close();
    CloseConn($mysqli); // Close the connection using your CloseCon function

    // Redirect to dashboard
    header('Location: ../../frontend/pages_php/dashboard.php');
    exit();
}