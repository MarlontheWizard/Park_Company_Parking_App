<?php
function OpenConn() {

    // Create the connection
    $mysqli = new mysqli("db", "db_user", "dbpassword", "park_app_database");

    // Check the connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    ensureSchema($mysqli);

    return $mysqli; // Return the connection object
}

function ensureSchema($mysqli) {
    $usersTableSql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NULL,
        locale VARCHAR(50) NULL,
        picture TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $reservationsTableSql = "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        spot_id VARCHAR(255) NOT NULL,
        reservation_time DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_reservations_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $transactionsTableSql = "CREATE TABLE IF NOT EXISTS transactions (
        transaction_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reservation_id INT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transactions_user_id (user_id),
        INDEX idx_transactions_reservation_id (reservation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$mysqli->query($usersTableSql)) {
        throw new Exception("Failed to ensure users table exists: " . $mysqli->error);
    }

    if (!$mysqli->query($reservationsTableSql)) {
        throw new Exception("Failed to ensure reservations table exists: " . $mysqli->error);
    }

    if (!$mysqli->query($transactionsTableSql)) {
        throw new Exception("Failed to ensure transactions table exists: " . $mysqli->error);
    }
}

function CloseConn($mysqli) {
    // Close the connection
    $mysqli->close();
}


/* --------------------------------------------------------------------------------------
   |                            TRANSACTION ATTRIBUTES                                  |
   --------------------------------------------------------------------------------------                                                                                      
*/

/*
@param $mysqli: database connection 
@param $userId: user_id from users database

Deletes transactions with $userId. 
*/
function account_deletion_updateTransaction_Table($mysqli, $userId) {
    // Prepare SQL to delete related rows in the transactions table
    $deleteTransactions = $mysqli->prepare("DELETE FROM transactions WHERE user_id = ?");
    
    if (!$deleteTransactions){
        // Log the error if prepare fails
        error_log("Error preparing delete query for transactions: " . $mysqli->error);
        throw new Exception("Error preparing delete query for transactions.");
    }

    // Bind parameters and execute the query
    $deleteTransactions->bind_param("i", $userId);
    
    if (!$deleteTransactions->execute()) {
        // Log the error if execute fails
        error_log("Error executing delete query for transactions: " . $deleteTransactions->error);
        throw new Exception("Error deleting transactions from database.");
    }

    $deleteTransactions->close();
}


/*
@param $transactionId:     Transaction identifier

Delete a Transaction (e.g. if a booking is canceled or user account is deleted)
*/
function deleteTransaction($transactionId) {
    global $mysqli;
    
    // Prepare SQL to delete a transaction
    $stmt = $mysqli->prepare("DELETE FROM transactions WHERE transaction_id = ?");
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $stmt->close();
    
    return $stmt->affected_rows > 0; // Return true if the deletion was successful
}


/*
**Incomplete
 Function to Retrieve a User's Transaction History
 */
function getTransactionHistory($mysqli, $userId) {
    
    // SQL query to select all transactions for a given user
    $stmt = $mysqli->prepare("SELECT t.id, t.parking_spot_id, t.amount, t.transaction_date, p.name AS parking_spot_name
                              FROM transactions t
                              JOIN parking_spots p ON t.parking_spot_id = p.id
                              WHERE t.user_id = ?
                              ORDER BY t.transaction_date DESC");
    
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    
    $stmt->bind_param("i", $userId);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->store_result();

    // Check if results are empty
    if ($stmt->num_rows === 0) {
        
        $stmt->close();
        return []; // No transactions found
    }

    $transactionId = null;
    $spotId = null;
    $amount = null;
    $transactionDate = null;
  
    
    // Bind the result to variables
    $stmt->bind_result($transactionId, $spotId, $amount, $transactionDate);

    // Fetch the results and return them as an associative array
    $transactions = [];
    while ($stmt->fetch()) {
        $transactions[] = [
            'transaction_id' => $transactionId,
            'spot_id' => $spotId,
            'amount' => $amount,
            'transaction_date' => $transactionDate,
        ];
    }

    $stmt->close();
    
    return $transactions; // Return the transaction history
}

/*
**Incomplete
 Function to Get the Last 5 Transactions (for dashboard output)
*/
function getRecentTransactions($mysqli, $userId, $transactionId, $spotId, $amount, $transactionDate, $spotName) {
    
    //Create SQL query to select the last 5 transactions for a given user
    $stmt = $mysqli->prepare("SELECT t.id, t.parking_spot_id, t.amount, t.transaction_date, p.name AS parking_spot_name
                              FROM transactions t
                              JOIN parking_spots p ON t.parking_spot_id = p.id
                              WHERE t.user_id = ?
                              ORDER BY t.transaction_date DESC LIMIT 5");

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    // Bind the result to variables
    $stmt->bind_result($transactionId, $spotId, $amount, $transactionDate, $spotName);
    
    // Fetch the results and return them as an associative array
    $recentTransactions = [];
    while ($stmt->fetch()) {
        $recentTransactions[] = [
            'transaction_id' => $transactionId,
            'spot_id' => $spotId,
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'spot_name' => $spotName
        ];
    }
    
    $stmt->close();
    
    return $recentTransactions; // Return the most recent transactions
}


/*
@param $mysqli:         database connection 
@param $userId:         user_id from users database
@param $parkingId:      Identifier of selected parking space
@param $amount:         Price of the reservation
@param $paymentMethod:  What type of payment used (Currently only supports card)

Updates transaction table with new transaction. 
*/
function insertion_updateTransaction_Table($mysqli, $userId, $reservationId, $amount, $paymentMethod){

    $stmt = $mysqli->prepare("INSERT INTO transactions (user_id, reservation_id, amount, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $userId, $reservationId, $amount, $paymentMethod);

    if ($stmt->execute()) {
                        
        /*echo "<script>
                window.location.href = '../../frontend/pages_html/success.html';
              </script>";*/
        }
                    
    else {
            echo "<h1>Transaction Error</h1>";
            echo "<p>There was an error recording your transaction. Please contact support.</p>";
            CloseConn($mysqli);
    }

    $stmt->close();
}

/*
@param $mysqli:              database connection
@param $reservationId:       stores query to perform on database

Update the reservation status to "paid"
*/
function status_updateTransaction_Table($mysqli, $reservationId, $status) {

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("UPDATE transactions SET status = ? WHERE reservation_id = ?");
    
    if ($stmt === false) {
        // Error preparing the statement
        error_log("Error preparing statement: " . $mysqli->error);
        return false;
    }
    
    // Bind the parameters
    $stmt->bind_param("si", $status, $reservationId);
    
    // Execute the query
    if ($stmt->execute()) {
        // Successfully updated the reservation status
        $stmt->close();
        return true;
    } 
    
    else {
        
        // Error executing the query
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return false;
    }
}



/* --------------------------------------------------------------------------------------
   |                            RESERVATION ATTRIBUTES                                  |
   --------------------------------------------------------------------------------------                                                                                      
*/

/*
@param $mysqli: database connection 
@param $userId: user_id from users database

Deletes reservations with $userId. 
*/
function account_deletion_updateReservation_Table($mysqli, $userId) {
    // Prepare SQL to delete related rows in the reservations table
    $deleteReservations = $mysqli->prepare("DELETE FROM reservations WHERE user_id = ?");
    if (!$deleteReservations) {
        // Log the error if prepare fails
        error_log("Error preparing delete query for reservations: " . $mysqli->error);
        throw new Exception("Error preparing delete query for reservations.");
    }

    // Bind parameters and execute the query
    $deleteReservations->bind_param("i", $userId);
    if (!$deleteReservations->execute()) {
        // Log the error if execute fails
        error_log("Error executing delete query for reservations: " . $deleteReservations->error);
        throw new Exception("Error deleting reservations from database.");
    }

    $deleteReservations->close();
}



/*
@param $mysqli:              database connection
@param $reservationId:       stores query to perform on database
@param $status:              status of reservation -> reserved/completed

Update the reservation status to $status
*/
function status_updateReservation_Table($mysqli, $reservationId, $status) {

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    
    if ($stmt === false) {
        // Error preparing the statement
        error_log("Error preparing statement: " . $mysqli->error);
        return false;
    }
    
    // Bind the parameters
    $stmt->bind_param("si", $status, $reservationId);
    
    // Execute the query
    if ($stmt->execute()) {

        // Successfully updated the reservation status
        $stmt->close();
        return true;
    } 
    
    else {

        // Error executing the query
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

function insertion_updateReservation_Table($mysqli, $user_id, $spotId, $reservationTime){
    

    // Insert reservation into the database
    $stmt = $mysqli->prepare("INSERT INTO reservations (user_id, spot_id, reservation_time) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $spotId, $reservationTime);
    

    if ($stmt->execute()) { 
        
        $reservationId = $stmt->insert_id; // Get the inserted reservation ID
    
    } 
    
    else {  // Error running the query 
        
        echo "Error executing query: " . $mysqli->error;
        echo json_encode(['error' => 'Failed to insert reservation.']);
        CloseConn($mysqli); // Close the connection before exiting
        exit();
    }

   
    return $reservationId;

}


function get_reservationId($mysqli, $userId, $spotId, $reservationTime){

    $reservationId = null;

    $stmt = $mysqli->prepare('SELECT id FROM reservations where user_id = ? AND spot_id = ? AND reservation_time = ?');
    $stmt->bind_param("iis", $userId, $spotId, $reservationTime);

    $stmt->execute();

    // Bind the result variable
    $stmt->bind_result($reservationId);

    // Fetch the result
    if ($stmt->fetch()) {
        
        return $reservationId;
    } 
    
    else {

        echo "No reservation found.";
    }

    return null; 

}

/* --------------------------------------------------------------------------------------
   |                                 USERS ATTRIBUTES                                   |
   --------------------------------------------------------------------------------------                                                                                      
*/


function userExists($mysqli, $email){

    // Query to check if the user exists with the given email
    $sql = "SELECT * FROM users WHERE email = ?";

    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare userExists query: " . $mysqli->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $exists = $result->num_rows > 0;
    $stmt->close();

    return $exists;
}

function getUserByEmail($mysqli, $email) {
    $sql = "SELECT user_id, email, name, password, locale, picture FROM users WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare getUserByEmail query: " . $mysqli->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

/*
@param $mysqli: database connection 
@param $email: user's email from users database

Deletes users with $email. 
*/
function account_deletion_updateUserID_Table($mysqli, $email) {
    // Prepare SQL to delete user based on email
    $deleteUser = $mysqli->prepare("DELETE FROM users WHERE email = ?");
    
    if (!$deleteUser) {
        
        // Log the error if prepare fails
        error_log("Error preparing delete query for users: " . $mysqli->error);
        throw new Exception("Error preparing delete query for users.");
    }
    
    // Bind parameters and execute the query
    $deleteUser->bind_param("s", $email);
    if (!$deleteUser->execute()) {
        // Log the error if execute fails
        error_log("Error executing delete query for users: " . $deleteUser->error);
        throw new Exception("Error deleting user from database.");
    }

    $deleteUser->close();
}


// Function to Get User Info by User ID

?>