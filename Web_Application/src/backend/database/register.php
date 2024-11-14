<?php
require_once 'database.php'; // Include the database connection

// Function to Register a New User
function registerUser($name, $email, $password, $locale, $picture) {
    global $mysqli;
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format"; // Return an error if email is invalid
    }
    
    // Check if email already exists in the database
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return "Email already registered"; // Return an error if email is already in use
    }
    $stmt->close();
    
    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare the SQL query to insert the new user into the database
    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, locale, picture) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $locale, $picture);
    
    // Execute the query and check if the insertion was successful
    if ($stmt->execute()) {
        $stmt->close();
        return "User registered successfully"; // Return success message
    } else {
        $stmt->close();
        return "Error registering user"; // Return error if insertion failed
    }
}
?>