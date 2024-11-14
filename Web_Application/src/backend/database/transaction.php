<?php
require_once 'database.php'; // Include the database connection

// 1. Function to Register a New Transaction (Booking a Parking Spot)
function registerTransaction($userId, $spotId, $amount, $transactionDate) {
    global $mysqli;
    
    // Prepare SQL to insert a new transaction record
    $stmt = $mysqli->prepare("INSERT INTO transactions (user_id, parking_spot_id, amount, transaction_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $userId, $spotId, $amount, $transactionDate);
    $stmt->execute();
    $stmt->close();
    
    return $mysqli->insert_id; // Return the ID of the newly inserted transaction
}

// 2. Function to Retrieve a User's Transaction History
function getTransactionHistory($userId) {
    global $mysqli;
    
    // Prepare SQL to select all transactions for a given user
    $stmt = $mysqli->prepare("SELECT t.id, t.parking_spot_id, t.amount, t.transaction_date, p.name AS parking_spot_name
                              FROM transactions t
                              JOIN parking_spots p ON t.parking_spot_id = p.id
                              WHERE t.user_id = ?
                              ORDER BY t.transaction_date DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    // Bind the result to variables
    $stmt->bind_result($transactionId, $spotId, $amount, $transactionDate, $spotName);
    
    // Fetch the results and return them as an associative array
    $transactions = [];
    while ($stmt->fetch()) {
        $transactions[] = [
            'transaction_id' => $transactionId,
            'spot_id' => $spotId,
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'spot_name' => $spotName
        ];
    }
    $stmt->close();
    
    return $transactions; // Return the transaction history
}

// 3. Function to Update a Transaction (e.g., after a dispute or correction)
function updateTransaction($transactionId, $newAmount, $newSpotId) {
    global $mysqli;
    
    // Prepare SQL to update a transaction record
    $stmt = $mysqli->prepare("UPDATE transactions SET amount = ?, parking_spot_id = ? WHERE id = ?");
    $stmt->bind_param("iii", $newAmount, $newSpotId, $transactionId);
    $stmt->execute();
    $stmt->close();
    
    return $stmt->affected_rows > 0; // Return true if the update was successful
}

// 4. Function to Delete a Transaction (e.g., if a booking is canceled)
function deleteTransaction($transactionId) {
    global $mysqli;
    
    // Prepare SQL to delete a transaction
    $stmt = $mysqli->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $stmt->close();
    
    return $stmt->affected_rows > 0; // Return true if the deletion was successful
}

// 5. Function to Get the Last 5 Transactions (for quick access)
function getRecentTransactions($userId) {
    global $mysqli;
    
    // Prepare SQL to select the last 5 transactions for a given user
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
?>