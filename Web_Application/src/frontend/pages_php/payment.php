<?php
require_once '/var/www/html/vendor/autoload.php'; // Include Stripe's PHP SDK
include_once '../database/handle_connection.php'; // Include database connection handler

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Your Stripe secret key (ensure it's set properly for test/live mode)
\Stripe\Stripe::setApiKey('sk_test_51QKsGKFtWrQJAs7gfdIk8lfixc9tgS7pSDSQkCCRD9EAWtFG96hGZTk3eX91B0kPkBDAj41NGr0karGlzvRiJhkF00ecqW2MjI'); 

// Check if the reservation data is being posted (for example, from a form or an API call)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you pass the spotId and reservationTime to this file
    $spotId = $_POST['spotId'];
    $reservationTime = $_POST['reservationTime'];

    // Fetch the user details from the session (ensure user is logged in)
    $user = $_SESSION['user'];

    // Open the database connection
    $mysqli = OpenConn();

    // Insert reservation into the database
    $stmt = $mysqli->prepare("INSERT INTO reservations (user_id, spot_id, reservation_time) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user['id'], $spotId, $reservationTime);
    
    // Now we can run the query
    if ($stmt->execute()) {
        $reservationId = $stmt->insert_id; // Get the inserted reservation ID
    } 
    
    else {
        echo json_encode(['error' => 'Failed to insert reservation.']);
        CloseConn($mysqli); // Close the connection before exiting
        exit();
    }

    // Close the database connection
    CloseConn($mysqli);

    // Create a Stripe Checkout session
    try {
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Parking Spot Reservation',
                        ],
                        'unit_amount' => 500, // Example: $5.00, adjust as needed
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost:8080/web_application/src/backend/payment/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:8080/web_application/src/frontend/php_pages/index.php',
            'metadata' => [
                'reservation_id' => $reservationId, // Include the reservation ID for later reference
            ],
        ]);

        // Redirect the user to the Stripe Checkout page
        header('Location: ' . $checkoutSession->url);
        exit();  // Ensure no further code runs after the redirect
    } 
    
    catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(['error' => 'Stripe Checkout session creation failed: ' . $e->getMessage()]);
    }
} 

else {
    // Show form to initiate the payment if no data was posted
    echo "<form method='POST' action='payment.php'>
            <input type='text' name='spotId' placeholder='Spot ID' required />
            <input type='datetime-local' name='reservationTime' required />
            <button type='submit'>Reserve & Pay</button>
          </form>";
}
?>