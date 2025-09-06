<?php
/**
 * Test Paypack Integration
 */

require_once('initialize.php');

// Load .env file
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

require_once('classes/PaypackHandler.php');

echo "Testing Paypack Integration...\n\n";

try {
    $paypack = new PaypackHandler();

    // Test authentication
    echo "1. Testing authentication...\n";
    $authResult = $paypack->authenticate();

    if ($authResult['success']) {
        echo "✓ Authentication successful\n";
        echo "Access Token: " . substr($authResult['access_token'], 0, 20) . "...\n";
    } else {
        echo "✗ Authentication failed: " . $authResult['error'] . "\n";
        exit(1);
    }

    // Test transaction status check (using a dummy reference)
    echo "\n2. Testing transaction status check...\n";
    $statusResult = $paypack->checkTransactionStatus('test-reference');

    if ($statusResult['success']) {
        echo "✓ Status check successful\n";
        echo "Status: " . ($statusResult['status'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Status check failed: " . $statusResult['error'] . "\n";
    }

    echo "\nPaypack integration test completed successfully!\n";

} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
}
?>