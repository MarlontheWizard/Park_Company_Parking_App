<?php
session_start();
include('database.php');

// Check that user data is available in the session
if (isset($_SESSION['user'])) {
    $userInfo = $_SESSION['user'];

    // Check if user exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userInfo->email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert new user
        $sql = "INSERT INTO users (name, email, locale, picture) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $userInfo->name, $userInfo->email, $userInfo->locale, $userInfo->picture);
        $stmt->execute();
    } else {
        // Update existing user
        $sql = "UPDATE users SET name = ?, locale = ?, picture = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $userInfo->name, $userInfo->locale, $userInfo->picture, $userInfo->email);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    // Redirect to the dashboard or another page
    header('Location: http://localhost:8080/web_application/src/frontend/pages_php/dashboard.php');
    exit();
} else {
    echo "User information not found in session.";
    exit();
}