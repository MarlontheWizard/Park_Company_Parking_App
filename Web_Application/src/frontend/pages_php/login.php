<?php
session_start();

require_once '/var/www/html/vendor/autoload.php'; 


require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';

// Initialize variables for error message
$error_message = '';

// Only process the login if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Open database connection using openConn() from handle_connection.php
    $mysqli = OpenConn();
    
    // Query to check if the user exists with the given email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the email exists in the database
    if ($result->num_rows > 0) {
        $user = $result->fetch_object();
        
        // Verify the password
        if (password_verify($password, $user->password)) {
            // Password is correct, log the user in
            $_SESSION['user'] = (object)[
                'email' => $user->email,
                'name' => $user->name,
                'picture' => $user->picture,
                'locale' => $user->locale
            ];
            
            // Redirect to dashboard after successful login
            header('Location: dashboard.php');
            exit();
        } else {
            // Invalid password
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