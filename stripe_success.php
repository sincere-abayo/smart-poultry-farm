<?php
/**
 * Stripe Payment Success Page
 * Handles successful payment returns from Stripe hosted checkout
 */

// Start output buffering to prevent header issues
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get session ID from URL first
$session_id = $_GET['session_id'] ?? '';

// Initialize variables
$success = false;
$error = '';
$order_id = null;
$amount = 0;
$currency = 'rwf';

echo "<!-- DEBUG: Starting stripe_success.php -->\n";
echo "<!-- DEBUG: Session ID: " . htmlspecialchars($session_id) . " -->\n";

if ($session_id) {
    try {
        echo "<!-- DEBUG: Including config.php -->\n";
        require_once('config.php');
        echo "<!-- DEBUG: config.php included successfully -->\n";

        echo "<!-- DEBUG: Including StripeHandler.php -->\n";
        require_once('classes/StripeHandler.php');
        echo "<!-- DEBUG: StripeHandler.php included successfully -->\n";

        echo "<!-- DEBUG: Including Master.php -->\n";
        require_once('classes/Master.php');
        echo "<!-- DEBUG: Master.php included successfully -->\n";

        echo "<!-- DEBUG: Including NotificationManager.php -->\n";
        require_once('classes/NotificationManager.php');
        echo "<!-- DEBUG: NotificationManager.php included successfully -->\n";

        echo "<!-- DEBUG: Creating StripeHandler instance -->\n";
        $stripeHandler = new StripeHandler();
        echo "<!-- DEBUG: StripeHandler created successfully -->\n";

        echo "<!-- DEBUG: Retrieving checkout session from Stripe -->\n";
        $sessionResult = $stripeHandler->retrieveCheckoutSession($session_id);
        echo "<!-- DEBUG: Stripe API call completed -->\n";

        if ($sessionResult['success']) {
            echo "<!-- DEBUG: Stripe API call successful -->\n";
            $session = $sessionResult['session'];
            echo "<!-- DEBUG: Session data retrieved -->\n";
            echo "<!-- DEBUG: Payment status: " . ($session['payment_status'] ?? 'not set') . " -->\n";

            // Check if payment was successful
            if ($session['payment_status'] === 'paid') {
                echo "<!-- DEBUG: Payment status is 'paid' -->\n";
                // Get user ID from session metadata
                $user_id = $session['metadata']['user_id'] ?? null;
                echo "<!-- DEBUG: User ID from metadata: " . ($user_id ?? 'not found') . " -->\n";

                // If no user_id in metadata, try to get from current session
                if (!$user_id) {
                    $user_id = $_SESSION['userdata']['id'] ?? $_SESSION['auth_user']['id'] ?? null;
                    echo "<!-- DEBUG: User ID from session: " . ($user_id ?? 'not found') . " -->\n";
                }

                if ($user_id) {
                    echo "<!-- DEBUG: User ID found, processing order -->\n";
                    // Process the order
                    echo "<!-- DEBUG: Master class instantiated -->\n";
                    $master = new Master();
                    echo "<!-- DEBUG: Order placement attempted -->\n";
                    $orderResult = $master->place_order([
                        'client_id' => $user_id,
                        'amount' => $session['amount_total'] / 100, // Convert from cents
                        'order_type' => $session['metadata']['order_type'] ?? '1',
                        'delivery_address' => $session['metadata']['delivery_address'] ?? '',
                        'payment_method' => 'stripe',
                        'paid' => 1
                    ]);

                    if ($orderResult['status'] === 'success') {
                        echo "<!-- DEBUG: Order created successfully -->\n";
                        $success = true;
                        $order_id = $orderResult['order_id'];
                        $amount = $session['amount_total'] / 100;
                        $currency = $session['currency'] ?? 'rwf';

                        echo "<!-- DEBUG: Sending notifications -->\n";
                        // Send payment confirmation notifications
                        $notificationManager = new NotificationManager();

                        // Try to get user data from session or use defaults
                        $user_data = $_SESSION['userdata'] ?? $_SESSION['auth_user'] ?? [];
                        $paymentData = [
                            'user_email' => $user_data['email'] ?? 'user@example.com',
                            'user_phone' => $user_data['contact'] ?? '+250700000000',
                            'user_firstname' => $user_data['firstname'] ?? 'Customer',
                            'user_lastname' => $user_data['lastname'] ?? '',
                            'amount' => $amount,
                            'currency' => $currency,
                            'order_id' => $order_id,
                            'payment_method' => 'Stripe',
                            'payment_date' => date('Y-m-d H:i:s')
                        ];

                        $notificationManager->sendPaymentConfirmation($paymentData);
                        echo "<!-- DEBUG: Notifications sent -->\n";
                    } else {
                        $error = 'Failed to create order: ' . ($orderResult['msg'] ?? 'Unknown error');
                        echo "<!-- DEBUG: Order creation failed: $error -->\n";
                    }
                } else {
                    $error = 'User session not found. Please contact support with your payment reference: ' . $session_id;
                    echo "<!-- DEBUG: No user ID found: $error -->\n";
                }
            } else {
                $error = 'Payment was not successful. Status: ' . $session['payment_status'];
                echo "<!-- DEBUG: Payment not successful: $error -->\n";
            }
        } else {
            $error = 'Failed to verify payment: ' . $sessionResult['error'];
            echo "<!-- DEBUG: Stripe API call failed: $error -->\n";
        }
    } catch (Exception $e) {
        $error = 'Payment verification failed: ' . $e->getMessage();
        echo "<!-- DEBUG: Exception caught: $error -->\n";
        echo "<!-- DEBUG: Exception trace: " . $e->getTraceAsString() . " -->\n";
    }
} else {
    $error = 'No session ID provided';
    echo "<!-- DEBUG: No session ID provided -->\n";
}

echo "<!-- DEBUG: Processing complete. Success: " . ($success ? 'true' : 'false') . " -->\n";
echo "<!-- DEBUG: Error: " . $error . " -->\n";

// Clear any output that might have been generated
ob_clean();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Error'; ?> - Smart Poultry Farm</title>
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

        .result-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
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

        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .debug-info {
            background: #e9ecef;
            border-radius: 5px;
            padding: 0.5rem;
            margin: 1rem 0;
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="result-container">
        <?php if ($success): ?>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="mb-3 text-success">Payment Successful!</h2>
            <p class="text-muted mb-4">Your order has been placed successfully and payment has been processed.</p>

            <?php if ($order_id): ?>
                <div class="order-details">
                    <h5><i class="fas fa-receipt"></i> Order Details</h5>
                    <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                    <p class="mb-1"><strong>Amount:</strong> Frw <?php echo number_format($amount); ?></p>
                    <p class="mb-1"><strong>Payment Method:</strong> Stripe</p>
                    <p class="mb-0"><strong>Payment ID:</strong> <?php echo htmlspecialchars($session_id); ?></p>
                </div>
            <?php endif; ?>

            <p class="text-muted small mb-4">
                <i class="fas fa-envelope"></i> A confirmation email and SMS have been sent to you.
            </p>

            <div class="d-grid gap-2">
                <a href="./" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="?p=my_account" class="btn btn-outline-secondary">
                    <i class="fas fa-user"></i> View My Orders
                </a>
            </div>
        <?php else: ?>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="mb-3 text-danger">Payment Error</h2>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($error); ?></p>

            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                Session ID: <?php echo htmlspecialchars($session_id); ?><br>
                <?php if (isset($session)): ?>
                    Payment Status: <?php echo $session['payment_status'] ?? 'Unknown'; ?><br>
                    Amount: <?php echo ($session['amount_total'] ?? 0) / 100; ?><br>
                <?php endif; ?>
            </div>

            <p class="text-muted small mb-4">
                You will be redirected to checkout in 5 seconds...
            </p>
            <div class="d-grid gap-2">
                <a href="?p=checkout" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Checkout
                </a>
                <a href="./" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-redirect on error after 5 seconds
        <?php if (!$success): ?>
            setTimeout(function () {
                window.location.href = '?p=checkout';
            }, 5000);
        <?php endif; ?>
    </script>
</body>

</html>