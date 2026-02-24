<?php
session_start();

header('Content-Type: application/json');

// Import the autoloader and attributes to handle database state
require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/Web_Application/src/backend/database/handle_connection.php';

if (file_exists('/var/www/html/.env')) {
    Dotenv\Dotenv::createImmutable('/var/www/html')->safeLoad();
}

$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');

if (!$stripeSecretKey) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe secret key is not configured']);
    exit();
}

\Stripe\Stripe::setApiKey($stripeSecretKey);

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user = $_SESSION['user']; // Assuming user info is stored in session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $postData = json_decode(file_get_contents('php://input'), true);
    $spotId = $postData['spotId'];

    //echo json_encode(['spotId' => $spotId]);

    $userId = $_SESSION['user']['id']; // Assuming the user's ID is stored in the session under 'user'
    $reservationTime = $postData['reservationTime'];



    $mysqli = OpenConn(); // Open the connection

    // Insert reservation into the database
    $reservationId = insertion_updateReservation_Table($mysqli, $userId, $spotId, $reservationTime);


    /*
    A reservation comes with a transaction, unless the parking is free of course, but we will 
    assume that even a free booking is a transaction. 

    Therefore, let's create the transaction and insert it into the database.
    */
    
    $amount = 5; //  amount from cents to dollars
    $paymentMethod = 'Stripe'; // Set payment method to 'Stripe'
    
    insertion_updateTransaction_Table($mysqli, $userId, $reservationId, $amount, $paymentMethod);
    
    CloseConn($mysqli);

    // Now we create the Stripe session with the reservation ID and parking spot in metadata
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
            'success_url' => 'http://localhost:8080/payment/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:8080/pages_php/payment.php',
            'metadata' => [
                'reservation_id' => $reservationId, //Save reservationId for tracking later
                'parking_spot' => $spotId, // Add parking spot to metadata
                'reservation_time' => $reservationTime
            ],
        ]);

        error_log(print_r($checkoutSession, true)); // Log session for debugging
        
        if ($checkoutSession && isset($checkoutSession->url)) {

            echo json_encode([
                'checkoutUrl' => $checkoutSession->url,
            ]);

            exit();
        } 
        
        else {
            echo json_encode(['error' => 'Stripe session creation failed or URL missing']);
        }
    } 
    
    catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe Error: ' . $e->getMessage());
        echo json_encode(['error' => 'Error creating payment session in create-checkout']);
    }
    

     
}
?>