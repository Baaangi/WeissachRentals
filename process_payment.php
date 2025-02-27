<?php
// Show errors for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer's autoload file
require 'vendor/autoload.php';

// Set your Stripe secret key
\Stripe\Stripe::setApiKey(' sk_test_51PWA64P9jxeS6mgEUYVohJbZY1JnXfw6DpCGfbCmZoUN8pInjUhn1XamcSKu7v14BVnfnjHO49eMrj4Rpbprd5EF00UXmLxbXd');

// Set the content type for JSON
header('Content-Type: application/json');

// Read the JSON input sent from the client
$input = @file_get_contents("php://input");
$body = json_decode($input, true);

try {
    // Create a PaymentIntent with the order amount and currency
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => 2000, // Amount in cents
        'currency' => 'usd',
        'payment_method' => $body['payment_method'],
        'confirmation_method' => 'manual',
        'confirm' => true,
        'return_url' => 'https://example.com/payment/success', // Replace with your actual success URL
    ]);

    // Prepare the response to send back to the client
    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    // Encode the output array into JSON format and echo it
    echo json_encode($output);
} catch (Exception $e) {
    // Handle any errors that occur during the creation of the PaymentIntent
    http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)
    echo json_encode(['error' => $e->getMessage()]);
}
?>
