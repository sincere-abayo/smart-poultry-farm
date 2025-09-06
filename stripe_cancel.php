<?php
/**
 * Stripe Payment Cancel Page
 * Handles cancelled payments from Stripe hosted checkout
 */

require_once('config.php');

// Clear any stored session data
unset($_SESSION['stripe_order_amount']);
unset($_SESSION['stripe_order_type']);
unset($_SESSION['stripe_delivery_address']);
unset($_SESSION['stripe_currency']);
unset($_SESSION['stripe_session_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Smart Poultry Farm</title>
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

        .cancel-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .cancel-icon {
            font-size: 4rem;
            color: #ffc107;
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

        .info-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="cancel-container">
        <div class="cancel-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <h2 class="mb-3 text-warning">Payment Cancelled</h2>
        <p class="text-muted mb-4">Your payment was cancelled. No charges have been made to your account.</p>

        <div class="info-box">
            <h6><i class="fas fa-info-circle"></i> What happened?</h6>
            <p class="mb-0 small">You cancelled the payment process or closed the payment window before completing the
                transaction.</p>
        </div>

        <div class="d-grid gap-2">
            <a href="?p=checkout" class="btn btn-primary">
                <i class="fas fa-credit-card"></i> Try Again
            </a>
            <a href="./" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>

        <p class="text-muted small mt-3">
            Need help? <a href="?p=about" class="text-decoration-none">Contact our support team</a>
        </p>
    </div>
</body>

</html>
