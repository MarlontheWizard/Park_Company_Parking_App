<?php
session_start();

// Import the autoloader and attributes to handle database state
require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';

\Stripe\Stripe::setApiKey('sk_test_51QKsGKFtWrQJAs7gfdIk8lfixc9tgS7pSDSQkCCRD9EAWtFG96hGZTk3eX91B0kPkBDAj41NGr0karGlzvRiJhkF00ecqW2MjI'); // Use your secret Stripe API key here

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user = $_SESSION['user']; // Assuming user info is stored in session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $spotId = $postData['spotId'];
    $userId = $_SESSION['user']['id']; // Assuming the user's ID is stored in the session under 'user'
    $reservationTime = $postData['reservationTime'];

    $mysqli = OpenConn(); // Open the connection

    // Insert reservation into the database
    $stmt = $mysqli->prepare("INSERT INTO reservations (user_id, spot_id, reservation_time) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $spotId, $reservationTime);

    if ($stmt->execute()) {
        $reservationId = $stmt->insert_id; // Get the reservation ID after the insert

        // Now create the Stripe session with the reservation ID and parking spot in metadata
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
                    'reservation_id' => $reservationId, 
                    'parking_spot' => $spotId // Add parking spot to metadata
                ],
            ]);

            error_log(print_r($checkoutSession, true)); // Log session for debugging

            if ($checkoutSession && isset($checkoutSession->url)) {
                echo json_encode([
                    'checkoutUrl' => $checkoutSession->url,
                ]);
            } else {
                echo json_encode(['error' => 'Stripe session creation failed or URL missing']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Error: ' . $e->getMessage());
            echo json_encode(['error' => 'Error creating payment session in create-checkout']);
        }
    } else {
        echo "Error executing query: " . $mysqli->error;
        echo json_encode(['error' => 'Failed to insert reservation.']);
    }

    CloseConn($mysqli); 
}
?>