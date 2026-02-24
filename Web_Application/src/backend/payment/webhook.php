<?php
require_once '/var/www/html/vendor/autoload.php';
include_once '/var/www/html/Web_Application/src/backend/database/handle_connection.php';

if (file_exists('/var/www/html/.env')) {
    Dotenv\Dotenv::createImmutable('/var/www/html')->safeLoad();
}

$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');
$endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET');

if (!$stripeSecretKey || !$endpointSecret) {
    http_response_code(500);
    exit();
}

\Stripe\Stripe::setApiKey($stripeSecretKey);

// Get the payload and signature sent by Stripe
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

error_log("Webhook accessed.");

try {
    
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpointSecret);

    // Inside the `checkout.session.completed` event handler
    if ($event->type == 'checkout.session.completed') {
        $session = $event->data->object;
    }

    http_response_code(200); // Return a response to acknowledge receipt of the event
} 

catch (Exception $e) {
    
    http_response_code(400); // Bad Request
    exit();
}