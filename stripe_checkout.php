<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Stripe Hosted Checkout Page
 * Creates and redirects to Stripe's hosted checkout
 */

require_once('config.php');
require_once('classes/StripeHandler.php');

// Check if user is logged in
if (!isset($_SESSION['userdata']['id']) && !isset($_SESSION['auth_user']['id'])) {
    header('Location: login.php');
    exit;
}

$stripeHandler = new StripeHandler();
$error = '';

// Get parameters from URL or POST
$amount = floatval($_GET['amount'] ?? $_POST['amount'] ?? 0);
$order_type = $_GET['order_type'] ?? $_POST['order_type'] ?? '1';
$delivery_address = $_GET['delivery_address'] ?? $_POST['delivery_address'] ?? '';
$currency = $_GET['currency'] ?? $_POST['currency'] ?? 'rwf';

// Validate required parameters
if ($amount <= 0) {
    $error = 'Invalid order amount';
} else {
    try {
        // Get user ID from session
        $user_id = $_SESSION['userdata']['id'] ?? $_SESSION['auth_user']['id'] ?? null;

        // Convert amount to cents for Stripe
        $amountInCents = $stripeHandler->convertToCents($amount);

        // Prepare metadata - store all necessary data for success page
        $metadata = [
            'user_id' => $user_id,
            'order_type' => $order_type,
            'delivery_address' => $delivery_address,
            'amount' => $amount,
            'currency' => $currency,
            'timestamp' => time()
        ];

        // Create checkout session
        $checkoutResult = $stripeHandler->createCheckoutSession(
            $amountInCents,
            $currency,
            $metadata
        );

        if ($checkoutResult['success']) {
            // Store order details in session for after payment
            $_SESSION['stripe_order_amount'] = $amount;
            $_SESSION['stripe_order_type'] = $order_type;
            $_SESSION['stripe_delivery_address'] = $delivery_address;
            $_SESSION['stripe_currency'] = $currency;
            $_SESSION['stripe_session_id'] = $checkoutResult['session_id'];

            // Redirect to Stripe hosted checkout
            header('Location: ' . $checkoutResult['checkout_url']);
            exit;
        } else {
            $error = $checkoutResult['error'];
        }
    } catch (Exception $e) {
        $error = 'Failed to create checkout session: ' . $e->getMessage();
    }
}

// If we reach here, there was an error
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error - Smart Poultry Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .error-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 500px;
        width: 90%;
    }

    .error-icon {
        font-size: 4rem;
        color: #dc3545;
        margin-bottom: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="mb-3">Payment Error</h2>
        <p class="text-muted mb-4"><?php echo htmlspecialchars($error); ?></p>
        <div class="d-grid gap-2">
            <a href="?p=checkout" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Checkout
            </a>
            <a href="./" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</body>

</html>