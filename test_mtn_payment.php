<?php
/**
 * Test MTN Payment Flow
 */

require_once('initialize.php');

// Load .env file
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

require_once('classes/PaypackHandler.php');
require_once('classes/Master.php');

echo "Testing MTN Payment Flow...\n\n";

try {
    $paypack = new PaypackHandler();

    // Test basic cashin initiation
    echo "1. Testing cashin transaction initiation...\n";
    $amount = 1000; // RWF
    $phoneNumber = "250781234567"; // Test MTN number

    $cashinResult = $paypack->initiateCashin($amount, $phoneNumber);

    if ($cashinResult['success']) {
        echo "✓ Cashin transaction initiated successfully\n";
        echo "Reference: " . $cashinResult['reference'] . "\n";
        echo "Status: " . $cashinResult['status'] . "\n";
        echo "Amount: " . $cashinResult['amount'] . "\n";
    } else {
        echo "✗ Cashin failed: " . $cashinResult['error'] . "\n";
    }

    echo "\nMTN payment flow test completed!\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
}
?>