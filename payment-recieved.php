<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        /* Basic styling */
        body {
            background-image: url("assets/images/payment-recieved-bg.jpg");
            background-size: cover;
            background-color: #252525;
            font-family: Arial, sans-serif;
            color: #252525;
            text-align: center;
            padding: 20px;
        }
        .confirmation-message {
            margin-top: 50px;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #ffea17;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #white;
            color: black;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: grey;
        }
    </style>
</head>
<body>
    <div class="confirmation-message">
        <h1>Payment Received</h1>
        <p>Thank you for your payment. Your transaction has been successfully completed.</p>
        <p>After your vehicle order has been confirmed, please visit our pickup location at <strong>Old Kalamassery Rd</strong> and meet our <strong>Pickup Representative</strong> at the front desk.</p>
        <p>Your vehicle will be ready for collection at the scheduled time.</p>
        <p>If you have any additional queries, dial <strong> +91-9044776236. </strong> </p>
        <button class="btn" onclick="window.location.href='my-booking.php';">Go to My Bookings</button>
    </div>
</body>
</html>
