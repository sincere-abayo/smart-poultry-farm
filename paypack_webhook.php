<?php
/**
 * Paypack Webhook Handler
 * Handles incoming webhooks from Paypack for payment status updates
 */

require_once('initialize.php');
require_once('classes/DBConnection.php');
require_once('classes/Master.php');

header('Content-Type: application/json');

// Get webhook data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Log webhook data for debugging
error_log("Paypack Webhook Received: " . json_encode($data));

try {
    $master = new Master();
    $conn = $master->conn;

    // Extract relevant data from webhook
    $event_kind = $data['event-kind'] ?? '';
    $reference = $data['data']['ref'] ?? '';
    $status = $data['data']['status'] ?? '';
    $amount = $data['data']['amount'] ?? 0;

    if (empty($reference)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing payment reference']);
        exit;
    }

    // Find order by Paypack reference
    $stmt = $conn->prepare("SELECT id, amount, client_id FROM orders WHERE payment_reference = ? AND payment_method = 'mtn'");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found for reference: ' . $reference]);
        exit;
    }

    $order = $result->fetch_assoc();
    $order_id = $order['id'];
    $client_id = $order['client_id'];
    $stmt->close();

    // Update order status based on webhook event
    if ($event_kind === 'transaction:processed') {
        if ($status === 'successful') {
            // Payment successful
            $paid = 1;
            $stmt = $conn->prepare("UPDATE orders SET paid = ?, status = 0 WHERE id = ?");
            $stmt->bind_param("ii", $paid, $order_id);
            $stmt->execute();
            $stmt->close();

            // Send success notifications
            try {
                $master->sendPaymentNotifications($order_id, $client_id, $amount, 'mtn');
            } catch (Exception $e) {
                error_log("Payment notification error: " . $e->getMessage());
            }

            error_log("MTN Payment successful for order #$order_id, reference: $reference");

        } elseif ($status === 'failed') {
            // Payment failed
            $paid = 0;
            $stmt = $conn->prepare("UPDATE orders SET paid = ?, status = 5 WHERE id = ?"); // 5 = Cancelled
            $stmt->bind_param("ii", $paid, $order_id);
            $stmt->execute();
            $stmt->close();

            error_log("MTN Payment failed for order #$order_id, reference: $reference");
        }
    }

    // Respond to webhook
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Webhook processed successfully']);

} catch (Exception $e) {
    error_log("Paypack webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>