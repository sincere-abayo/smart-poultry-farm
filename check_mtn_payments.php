<?php
/**
 * MTN Payment Status Checker
 * Periodically checks Paypack for payment status updates
 */

require_once('initialize.php');
require_once('classes/PaypackHandler.php');
require_once('classes/Master.php');

echo "Starting MTN payment status check...\n";

try {
    $paypack = new PaypackHandler();
    $master = new Master();
    $conn = $master->conn;

    // Get all pending MTN payments (paid = 0 and payment_method = 'mtn')
    $stmt = $conn->prepare("SELECT id, payment_reference, amount, client_id FROM orders WHERE payment_method = 'mtn' AND paid = 0 AND payment_reference IS NOT NULL AND payment_reference != ''");
    $stmt->execute();
    $result = $stmt->get_result();

    $checked = 0;
    $updated = 0;

    while ($order = $result->fetch_assoc()) {
        $order_id = $order['id'];
        $reference = $order['payment_reference'];
        $amount = $order['amount'];
        $client_id = $order['client_id'];

        echo "Checking order #$order_id with reference: $reference\n";

        // Check payment status with Paypack
        $statusResult = $paypack->checkTransactionStatus($reference);

        if ($statusResult['success']) {
            $status = $statusResult['status'];

            if ($status === 'successful') {
                // Update order as paid
                $updateStmt = $conn->prepare("UPDATE orders SET paid = 1, status = 0 WHERE id = ?");
                $updateStmt->bind_param("i", $order_id);
                $updateStmt->execute();
                $updateStmt->close();

                // Send notifications
                try {
                    $master->sendPaymentNotifications($order_id, $client_id, $amount, 'mtn');
                } catch (Exception $e) {
                    echo "Notification error for order #$order_id: " . $e->getMessage() . "\n";
                }

                echo "Order #$order_id marked as paid\n";
                $updated++;

            } elseif ($status === 'failed') {
                // Mark as cancelled
                $updateStmt = $conn->prepare("UPDATE orders SET status = 5 WHERE id = ?"); // 5 = Cancelled
                $updateStmt->bind_param("i", $order_id);
                $updateStmt->execute();
                $updateStmt->close();

                echo "Order #$order_id marked as failed\n";
                $updated++;
            } else {
                echo "Order #$order_id status: $status (no action needed)\n";
            }
        } else {
            echo "Failed to check status for order #$order_id: " . $statusResult['error'] . "\n";
        }

        $checked++;
    }

    $stmt->close();

    echo "\nPayment check completed:\n";
    echo "Orders checked: $checked\n";
    echo "Orders updated: $updated\n";

} catch (Exception $e) {
    echo "Error during payment check: " . $e->getMessage() . "\n";
    error_log("MTN payment check error: " . $e->getMessage());
}
?>