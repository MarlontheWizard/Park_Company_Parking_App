<?php
session_start();
require_once '/var/www/html/Web_Application/src/backend/database/handle_connection.php';


$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    // Get form inputs
    $email = $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['password'];

    // Import default profile image
    $default_picture = '../images/default_user_photo.jpg'; // Replace with your default image URL or path

    // If the user has uploaded a picture, use it; otherwise, use the default picture
    $user_picture = isset($_POST['picture']) && !empty($_POST['picture']) ? $_POST['picture'] : $default_picture;

    try {
        

        $mysqli = OpenConn();
        

        // Check if email already exists...
        if (userExists($mysqli, $email)) {
            
            $error_message = "Email already exists. Please use a different email.";
        } 
        
        else {
            
            // Hash 
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO users (email, name, password) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sss", $email, $name, $hashed_password);
            $stmt->execute();

            $user_id = $mysqli->insert_id;

            // Store user info in session (as array)
            $_SESSION['user'] = [
                'id' => $user_id,
                'email' => $email,
                'name' => $name,
                'locale' => 'en',
                'picture' => $user_picture // Store picture in session as well
            ];

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        }
        
    } 
    
    catch (Exception $e) {
        
        // If an exception occurs, log it and set an error message
        error_log($e->getMessage());
        $error_message = "There was an error processing your registration. Please try again.";
    }

   
    CloseConn($mysqli);
}

// Include the registration form if errors are present
include __DIR__ . '/../pages_html/registration.html';
?>