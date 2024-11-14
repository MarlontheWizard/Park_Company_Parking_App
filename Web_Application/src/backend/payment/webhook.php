<?php
require_once '/var/www/html/vendor/autoload.php';
include_once '../database/handle_connection.php';

\Stripe\Stripe::setApiKey('sk_test_51QKsGKFtWrQJAs7gfdIk8lfixc9tgS7pSDSQkCCRD9EAWtFG96hGZTk3eX91B0kPkBDAj41NGr0karGlzvRiJhkF00ecqW2MjI');

// Get the payload and signature sent by Stripe
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_8e829a229df7516ee33964148cecb905c49d9d41fc983c792f78909fb1057231';

error_log("Webhook accessed.");

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

    // Inside the `checkout.session.completed` event handler
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
    $reservationId = $session->metadata->reservation_id;

    $mysqli = OpenConn();

    if ($mysqli) {
        $stmt = $mysqli->prepare("UPDATE reservations SET status = 'paid' WHERE id = ?");
        $stmt->bind_param("i", $reservationId);

        if ($stmt->execute()) {
            error_log("Reservation ID $reservationId marked as paid.");
        } else {
            error_log("Database update failed for Reservation ID $reservationId: " . $mysqli->error);
        }
        $stmt->close();
        CloseConn($mysqli);
    } else {
        error_log("Database connection failed: " . mysqli_connect_error());
    }
}

    http_response_code(200); // Return a response to acknowledge receipt of the event
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    exit();
}