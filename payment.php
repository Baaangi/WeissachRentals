<?php
session_start();
include('includes/config.php');
error_reporting(E_ALL); // Enable error reporting

// Get vehicle price based on vhid
$vhid = isset($_GET['vhid']) ? intval($_GET['vhid']) : 0; // Ensure vhid is an integer

// Check if vhid is valid
if ($vhid > 0) {
    try {
        $sql = "SELECT PricePerDay FROM tblvehicles WHERE id=:vhid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':vhid', $vhid, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $pricePerDay = $result['PricePerDay'];
        } else {
            $pricePerDay = 0; // Set a default value if no result is found
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Display error message if query fails
        $pricePerDay = 0; // Set a default value if query fails
    }
} else {
    $pricePerDay = 0; // Set a default value if vhid is not valid
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stripe Payment Gateway</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #252525;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input[type="text"], #card-element {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .card-details {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .card-details .form-group {
            flex: 1;
            margin-bottom: 0; /* Remove default margin bottom */
        }
        button {
            padding: 10px 20px;
            background-color: #ffea17;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #4CAF50;
        }
        .hidden {
            display: none;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: auto;
            margin-top: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #payment-message {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <p style="text-align: center;">Amount Payable: ₹<?php echo htmlspecialchars($pricePerDay); ?></p>
        <h2 style="text-align: center;">Billing Information</h2>
        <form id="payment-form">
            <div class="form-group">
                <label for="cardholder-name">Name on Card</label>
                <input type="text" id="cardholder-name" placeholder="Enter the name on your card" required>
            </div>
            <div class="card-details">
                <div class="form-group">
                    <label for="card-number">Card Details</label>
                    <div id="card-element"></div>
                </div>
            </div>
            <!--<div class="card-details">
                <div class="form-group">
                    <label for="expiry-date">Expiry Date (MM/YY)</label>
                    <input type="text" id="expiry-date" placeholder="MM / YY" required>
                </div>
                <div class="form-group">
                    <label for="card-cvc">CVC</label>
                    <input type="text" id="card-cvc" placeholder="CVC" required>
                </div>
            </div>-->
            <button id="submit">Pay Now</button>
            <div id="loader" class="hidden loader"></div>
            <div id="payment-message" class="hidden"></div>
                <i id="success-icon" class="fas fa-check-circle" style="color: green; display: none; font-size: 24px;"></i>
                <span id="success-message"></span>
        </form>
    </div>


    <script>
        const stripe = Stripe('pk_test_51PWA64P9jxeS6mgExp5ZkBEv7JQubSqg1AWEUiZsejUwxSiZFrbIAg0QbdDKaJVLvcrreYcOvq3sMtwXDUeyMgfY00b1kG75o8');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Show loader
            document.getElementById('loader').classList.remove('hidden');

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: document.getElementById('cardholder-name').value,
                },
            });

            if (error) {
                console.log('Error creating payment method:', error.message);
                // Hide loader on error
                document.getElementById('loader').classList.add('hidden');
                return;
            }

            console.log('Payment method created successfully:', paymentMethod.id);

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ payment_method: paymentMethod.id }),
                });

                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.statusText}`);
                }

                const result = await response.json();

                if (result.error) {
                    console.log('Error processing payment:', result.error);
                } else {
                    console.log('Client secret received:', result.clientSecret);
                    // Confirm payment with client secret
                    stripe.confirmCardPayment(result.clientSecret).then((result) => {
                        // Hide loader
                        document.getElementById('loader').classList.add('hidden');

                        if (result.error) {
                            console.log('Error confirming card payment:', result.error.message);
                        } else {
                            console.log('Payment successful!');
                            // Show payment success message
                            document.getElementById('payment-message').classList.remove('hidden');
                            document.getElementById('success-icon').style.display = 'inline';
                            document.getElementById('payment-message').innerText = 'Payment successful. Redirecting back to website...';

                            setTimeout(function(){
                                const vhid = '<?php echo isset($_GET["vhid"]) ? htmlspecialchars($_GET["vhid"]) : ""; ?>';
                                window.location.href = 'payment-recieved.php';
                            },3000);
                        }
                    });
                }
            } catch (error) {
                console.log('Error during fetch or JSON parsing:', error.message);
                // Hide loader on error
                document.getElementById('loader').classList.add('hidden');
            }
        });
    </script>
    
</body>
</html>
