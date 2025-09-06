<?php
/**
 * Stripe Payment Intent Creation
 * AJAX endpoint for creating payment intents
 */

header('Content-Type: application/json');
require_once('config.php');
require_once('classes/StripeHandler.php');

// Check if user is logged in
if (!isset($_SESSION['userdata']['id']) && !isset($_SESSION['auth_user']['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $amount = floatval($input['amount'] ?? 0);
    $currency = $input['currency'] ?? 'rwf';
    $order_type = $input['order_type'] ?? '1';
    $delivery_address = $input['delivery_address'] ?? '';

    if ($amount <= 0) {
        throw new Exception('Invalid amount');
    }

    // Initialize Stripe handler
    $stripeHandler = new StripeHandler();

    // Convert amount to cents
    $amountInCents = $stripeHandler->convertToCents($amount);

    // Create payment intent
    $paymentIntent = $stripeHandler->createPaymentIntent($amountInCents, $currency, [
        'user_id' => $_SESSION['userdata']['id'] ?? $_SESSION['auth_user']['id'] ?? null,
        'order_type' => $order_type,
        'delivery_address' => $delivery_address,
        'timestamp' => time()
    ]);

    if ($paymentIntent['success']) {
        // Store payment intent details in session
        $_SESSION['stripe_payment_intent_id'] = $paymentIntent['payment_intent_id'];
        $_SESSION['stripe_amount'] = $amount;
        $_SESSION['stripe_currency'] = $currency;
        $_SESSION['stripe_order_type'] = $order_type;
        $_SESSION['stripe_delivery_address'] = $delivery_address;

        echo json_encode([
            'success' => true,
            'client_secret' => $paymentIntent['client_secret'],
            'payment_intent_id' => $paymentIntent['payment_intent_id']
        ]);
    } else {
        throw new Exception($paymentIntent['error']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>