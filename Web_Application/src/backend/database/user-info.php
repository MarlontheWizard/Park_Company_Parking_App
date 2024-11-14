<?php
require_once 'database.php'; // Include the database connection

// Function to Get User Info by User ID
function getUserInfo($userId) {
    global $mysqli;
    
    // Prepare the SQL query to fetch the user's information by ID
    $stmt = $mysqli->prepare("SELECT id, name, email, locale, picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId); // Bind the userId as an integer parameter
    
    // Execute the query and check for errors
    if ($stmt->execute()) {
        $stmt->store_result();
        
        // Check if a user was found
        if ($stmt->num_rows > 0) {
            // Bind the result to variables
            $stmt->bind_result($id, $name, $email, $locale, $picture);
            $stmt->fetch();
            
            // Return the user information as an associative array
            return [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'locale' => $locale,
                'picture' => $picture
            ];
        } else {
            return "No user found"; // Return a message if no user is found
        }
    } else {
        return "Error fetching user data"; // Return error message if query fails
    }
    
    // Close the statement
    $stmt->close();
}

// Function to Insert a New User into the Database
function insertUser($name, $email, $password, $locale, $picture) {
    global $mysqli;

    // Hash the password before inserting
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the SQL query to insert the new user into the database
    $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, locale, picture) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $locale, $picture); // Bind parameters

    // Execute the query and check for success
    if ($stmt->execute()) {
        return "User successfully registered!";
    } else {
        return "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Function to Update User Info
function updateUserInfo($userId, $name, $email, $locale, $picture) {
    global $mysqli;
    
    // Prepare the SQL query to update user information
    $stmt = $mysqli->prepare("UPDATE users SET name = ?, email = ?, locale = ?, picture = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $email, $locale, $picture, $userId); // Bind parameters
    
    // Execute the query and check for success
    if ($stmt->execute()) {
        return "User information successfully updated!";
    } else {
        return "Error updating user information: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>