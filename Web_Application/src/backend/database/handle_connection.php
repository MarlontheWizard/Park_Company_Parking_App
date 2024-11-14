<?php
function OpenConn() {

    // Create the connection
    $mysqli = new mysqli("db", "db_user", "dbpassword", "park_app_database");

    // Check the connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    return $mysqli; // Return the connection object
}

function CloseConn($mysqli) {
    // Close the connection
    $mysqli->close();
}

?>