<?php
session_start();  // Start the session

require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';

\Stripe\Stripe::setApiKey('sk_test_51QKsGKFtWrQJAs7gfdIk8lfixc9tgS7pSDSQkCCRD9EAWtFG96hGZTk3eX91B0kPkBDAj41NGr0karGlzvRiJhkF00ecqW2MjI');

// Retrieve the session ID from the URL parameters
$session_id = $_GET['session_id'] ?? null;

if ($session_id) {
    try {
        // Retrieve the Checkout session to confirm payment success
        $session = \Stripe\Checkout\Session::retrieve($session_id);

        // Check if payment was successful
        if ($session->payment_status == 'paid') {
            // Check if reservation_id is available in the session metadata
            if (isset($session->metadata->reservation_id)) {
                $reservationId = $session->metadata->reservation_id;
                $userId = $_SESSION['user']['id'] ?? null; // Ensure user ID is available in session

                // Check if user is logged in
                if ($userId) {
                    // Open database connection
                    $mysqli = OpenConn();

                    // Update the reservation status to "paid"
                    $stmt = $mysqli->prepare("UPDATE reservations SET status = 'paid' WHERE id = ?");
                    $stmt->bind_param("i", $reservationId);
                    $stmt->execute();
                    $stmt->close();

                    // Insert transaction details into the transactions table
                    $amount = $session->amount_total / 100; // Convert amount from cents to dollars
                    $paymentMethod = 'Stripe'; // Set payment method to 'Stripe'

                    $stmt = $mysqli->prepare("INSERT INTO transactions (user_id, parking_spot, amount, payment_method) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isds", $userId, $session->metadata->parking_spot, $amount, $paymentMethod);

                    if ($stmt->execute()) {
                        
                        echo "<script>
                                window.location.href = '../../frontend/pages_html/success.html';
                              </script>";
                    }
                    
                    else {
                        echo "<h1>Transaction Error</h1>";
                        echo "<p>There was an error recording your transaction. Please contact support.</p>";
                    }

                    $stmt->close();
                    CloseConn($mysqli);
                } else {
                    echo "<p>User not logged in.</p>";
                }
            } else {
                echo "<p>No reservation ID found in metadata.</p>";
            }
        } else {
            echo "<h1>Payment Failed</h1>";
            echo "<p>There was an issue with processing your payment. Please try again.</p>";
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "<h1>Error</h1>";
        echo "<p>There was an issue with your payment. Please contact support.</p>";
        error_log('Stripe API Error: ' . $e->getMessage());
    }
} else {
    echo "<h1>Missing Session ID</h1>";
    echo "<p>No session ID was provided. Please try again later.</p>";
}
?>
