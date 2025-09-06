<?php
ob_start();
session_start();
$session_id = $_GET['session_id'] ?? '';

require_once('config.php');
require_once('classes/StripeHandler.php');
require_once('classes/Master.php');
require_once('classes/NotificationManager.php');

// Retrieve Stripe session
$session = null;
$orderResult = null;
$amount = 0;
$order_id = null;
$success = false;
$message = '';

if (!empty($session_id)) {
    $stripeHandler = new StripeHandler();
    $sessionResult = $stripeHandler->retrieveCheckoutSession($session_id);
    if ($sessionResult['success']) {
        $session = $sessionResult['session'];
        if (($session['payment_status'] ?? null) === 'paid') {
            $user_id = $session['metadata']['user_id'] ?? ($_SESSION['userdata']['id'] ?? $_SESSION['auth_user']['id'] ?? null);
            if ($user_id) {
                $master = new Master();
                $orderResult = $master->create_order_core([
                    'client_id' => $user_id,
                    'amount' => $session['amount_total'] / 100,
                    'order_type' => $session['metadata']['order_type'] ?? '1',
                    'delivery_address' => $session['metadata']['delivery_address'] ?? '',
                    'payment_method' => 'stripe',
                    'paid' => 1
                ]);
                if ($orderResult['status'] === 'success') {
                    $success = true;
                    $order_id = $orderResult['order_id'];
                    $amount = $session['amount_total'] / 100;
                    $message = $orderResult['message'];
                } else {
                    $message = $orderResult['msg'] ?? 'Order failed.';
                }
            } else {
                $message = 'User session not found. Please contact support with your payment reference: ' . htmlspecialchars($session_id);
            }
        } else {
            $message = 'Payment was not successful. Status: ' . htmlspecialchars($session['payment_status'] ?? 'unknown');
        }
    } else {
        $message = 'Failed to verify payment: ' . htmlspecialchars($sessionResult['error'] ?? 'Unknown error');
    }
} else {
    $message = 'No session ID provided.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Error'; ?> - Selling Poultry Farm</title>
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
                    <p class="mb-1"><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_id); ?></p>
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
                <a href="index.php?p=my_account" class="btn btn-outline-secondary">
                    <i class="fas fa-user"></i> View My Orders
                </a>
            </div>
        <?php else: ?>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="mb-3 text-danger">Payment Error</h2>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
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
</body>

</html>