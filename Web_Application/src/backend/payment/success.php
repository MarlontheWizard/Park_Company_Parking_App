<?php
session_start();  // Start the session

require_once '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/web_application/src/backend/database/handle_connection.php';

if (file_exists('/var/www/html/.env')) {
    Dotenv\Dotenv::createImmutable('/var/www/html')->safeLoad();
}

$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');

if (!$stripeSecretKey) {
    http_response_code(500);
    echo '<h1>Configuration Error</h1><p>Stripe secret key is not configured.</p>';
    exit();
}

$googlePlacesApiKey = $_ENV['GOOGLE_PLACES_API_KEY'] ?? getenv('GOOGLE_PLACES_API_KEY');

function getLocationFromPlaceId($placeId) {
    global $googlePlacesApiKey;
    if (!$googlePlacesApiKey) {
        return null;
    }
    
    $apiKey = $googlePlacesApiKey;

    $url = "https://maps.googleapis.com/maps/api/place/details/json?key=$apiKey&place_id=$placeId";

    // fetch the API response
    $response = file_get_contents($url);
    
    if ($response === false) {
        
        return null; // Return null if API call fails
    }

    $data = json_decode($response, true); // Decode JSON response

    if (isset($data['result']['formatted_address'])) {
        
        return $data['result']['formatted_address']; // Return the address
    }

    return null; // Return null if address is not found
}


\Stripe\Stripe::setApiKey($stripeSecretKey);

// Retrieve the session ID from the URL parameters
$session_id = $_GET['session_id'] ?? null;

if ($session_id) {
    
    try {

        // Retrieve the Checkout session to confirm payment success
        $session = \Stripe\Checkout\Session::retrieve($session_id);

        //Check if payment was successful
        if($session->payment_status == 'paid') {
            
            //Check if reservation_id is available in the session metadata
            if (isset($session->metadata->reservation_id)) {
                
                $reservationId = $session->metadata->reservation_id;

                // echo 'reservation id: ' . $reservationId; 

                $userId = $_SESSION['user']['id'] ?? null; // Ensure user ID is available in session

                // 'reservation id: ' . $userId; 

                
                if($userId) { //Users logged in
                   
                    $mysqli = OpenConn();

                    //Reservation ID is available, update transaction status to paid in transactions table
                    status_updateTransaction_Table($mysqli, $reservationId, 'paid');

                    CloseConn($mysqli);

                    /*
                    We need a way to inject parking location, reservation id,
                    and reservation time into the html. Instead of using javascript 
                    in the approach of passing the values in a redirect url, we can 
                    take advantage of php and inject the html directly here. 

                    ***The normal approach throughout this app is to seperate the html from the
                    php.
                    */
                    $parking_location = getLocationFromPlaceId($session->metadata->parking_spot);
                    $reservation_time = $session->metadata->reservation_time;

                    //echo 'reservation_time_console ' . $reservation_time;

                    

                    ?>

                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Payment Successful</title>
                        <link rel="stylesheet" href="../../frontend/pages_css/style.css">
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                background-color: #f4f4f9;
                                text-align: center;
                                padding: 50px;
                            }
                    
                            h1 {
                                color: #28a745;
                            }
                    
                            .success-message {
                                background-color: #28a745;
                                color: white;
                                padding: 20px;
                                margin: 20px 0;
                                border-radius: 5px;
                            }
                    
                            .details {
                                background-color: #f8f9fa;
                                padding: 20px;
                                border: 1px solid #ddd;
                                border-radius: 5px;
                                margin-top: 20px;
                            }
                    
                            .button {
                                background-color: #007bff;
                                color: white;
                                padding: 10px 20px;
                                border: none;
                                border-radius: 5px;
                                text-decoration: none;
                                display: inline-block;
                            }
                    
                            .button:hover {
                                background-color: #0056b3;
                            }
                        </style>
                    </head>
                    <body>
                    
                        <h1>Payment Successful!</h1>
                    
                        <div class="success-message">
                            <p>Your payment has been successfully processed. Your reservation is now confirmed.</p>
                        </div>
                    
                        <div class="details">
                    
                            <h2>Reservation Details</h2>
                    
                            <p>Reservation ID: <strong><?php echo htmlspecialchars($reservationId); ?></strong></p>
                            <p>Location: <strong><?php echo htmlspecialchars($parking_location); ?></strong></p>
                            <p>Time: <strong><?php echo htmlspecialchars($reservation_time); ?></strong></p>
                    
                        </div>
                    
                        <div>
                            <a href="../../frontend/pages_php/dashboard.php" class="button">Go to Dashboard</a>
                        </div>
                    
                        <script>
                    
                            // Redirect to the dashboard after 5 seconds
                            setTimeout(function() {
                                window.location.href = '../../frontend/pages_php/dashboard.php'; // Redirect to the dashboard URL
                            }, 10000); // 5 seconds
                        </script>
                    
                    </body>
                    </html>
                <?php

                exit();

                } 
                
                else {

                    echo "<p>User not logged in.</p>";
                }

            } 
            
            else {
                
                echo "<p>No reservation ID found in metadata.</p>";
            }

        } 
        
        else {
            
            echo "<h1>Payment Failed</h1>";
            echo "<p>There was an issue with processing your payment. Please try again.</p>";
        }
    } 
    
    catch (\Stripe\Exception\ApiErrorException $e) {
        
        echo "<h1>Error</h1>";
        echo "<p>There was an issue with your payment. Please contact support.</p>";
        error_log('Stripe API Error: ' . $e->getMessage());
    }

    
} 

else {
    echo "<h1>Missing Session ID</h1>";
    echo "<p>No session ID was provided. Please try again later.</p>";
}

?>
