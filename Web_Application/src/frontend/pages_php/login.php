<?php
session_start();

require_once '/var/www/html/vendor/autoload.php'; 


require_once '/var/www/html/Web_Application/src/backend/database/handle_connection.php';

// Initialize variables for error message
$error_message = '';

// Only process the login if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];


    $mysqli = OpenConn();

    
    // Check if the email exists in the database
    if (userExists($mysqli, $email)) {
        $user = getUserByEmail($mysqli, $email);
        if (!$user) {
            $error_message = "No account found with this email. Please check your email or sign up.";
            CloseConn($mysqli);
            include '../pages_html/login.html';
            exit();
        }
        
        // Verify the password
        if (isset($user['password']) && password_verify($password, $user['password'])) {
            // Password is correct, log the user in
            $_SESSION['user'] = [
                
                'id' => $user['user_id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'picture' => $user['picture'],
                'locale' => $user['locale']
            ];
            
            // Redirect to dashboard after successful login
            header('Location: dashboard.php');
            exit();

        } 
        
        else {
        
            $error_message = "Incorrect password. Please try again.";
        }
    } 
    
    else { // Email isnt being found in the database
        
        $error_message = "No account found with this email. Please check your email or sign up.";
    }
    
    // Close the database connection
    CloseConn($mysqli);
}


include '../pages_html/login.html'; 
?>