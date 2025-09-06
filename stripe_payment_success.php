<?php
/**
 * Stripe Payment Success Handler
 * Handles successful payments without webhooks
 */

require_once('config.php');
require_once('classes/StripeHandler.php');
require_once('classes/NotificationManager.php');

// Get payment intent ID from URL
$paymentIntentId = $_GET['payment_intent'] ?? '';
$sessionId = $_GET['session_id'] ?? '';

if (empty($paymentIntentId) && empty($sessionId)) {
    header('Location: checkout.php?error=no_payment_data');
    exit;
}

try {
    $stripeHandler = new StripeHandler();
    $notificationManager = new NotificationManager();

    // If we have a session ID, get the payment intent from the session
    if (!empty($sessionId)) {
        // Retrieve session to get payment intent
        $sessionResult = $stripeHandler->makeStripeRequest("checkout/sessions/{$sessionId}", [], 'GET');
        if ($sessionResult['success']) {
            $paymentIntentId = $sessionResult['data']['payment_intent'];
        }
    }

    if (empty($paymentIntentId)) {
        throw new Exception('Payment intent ID not found');
    }

    // Check if payment was successful
    $paymentResult = $stripeHandler->isPaymentSuccessful($paymentIntentId);

    if (!$paymentResult['success']) {
        throw new Exception('Failed to check payment status: ' . $paymentResult['error']);
    }

    if (!$paymentResult['is_successful']) {
        header('Location: checkout.php?error=payment_failed&status=' . urlencode($paymentResult['status']));
        exit;
    }

    // Get payment intent details
    $paymentDetails = $stripeHandler->retrievePaymentIntent($paymentIntentId);

    if (!$paymentDetails['success']) {
        throw new Exception('Failed to retrieve payment details: ' . $paymentDetails['error']);
    }

    $paymentIntent = $paymentDetails['payment_intent'];
    $amount = $paymentIntent['amount'] / 100; // Convert from cents
    $currency = $paymentIntent['currency'];
    $metadata = $paymentIntent['metadata'] ?? [];

    // Create order in database
    $orderData = [
        'client_id' => $_SESSION['client_id'] ?? 1, // Use session or default
        'payment_method' => 'stripe',
        'amount' => $amount,
        'currency' => $currency,
        'payment_intent_id' => $paymentIntentId,
        'status' => 'paid',
        'metadata' => json_encode($metadata)
    ];

    // Insert order into database
    $conn = new DBConnection();
    $db = $conn->getConnection();

    $stmt = $db->prepare("
        INSERT INTO orders (client_id, payment_method, amount, currency, payment_intent_id, status, metadata, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "issssss",
        $orderData['client_id'],
        $orderData['payment_method'],
        $orderData['amount'],
        $orderData['currency'],
        $orderData['payment_intent_id'],
        $orderData['status'],
        $orderData['metadata']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }

    $orderId = $db->insert_id;
    $stmt->close();

    // Send payment confirmation notifications
    $paymentData = [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
        'payment_method' => 'Stripe',
        'payment_intent_id' => $paymentIntentId,
        'client_id' => $orderData['client_id']
    ];

    // Send notifications
    $notificationManager->sendPaymentConfirmation($paymentData);

    // Redirect to success page
    header('Location: checkout.php?success=1&order_id=' . $orderId . '&amount=' . $amount);
    exit;

} catch (Exception $e) {
    error_log("Stripe payment success error: " . $e->getMessage());
    header('Location: checkout.php?error=payment_processing_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>
