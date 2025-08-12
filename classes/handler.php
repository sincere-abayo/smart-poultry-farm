<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Set content type to JSON
header('Content-Type: application/json');

// Include required files
require_once(dirname(__FILE__) . '/../initialize.php');
require_once(dirname(__FILE__) . '/DBConnection.php');
require_once(dirname(__FILE__) . '/Master.php');

try {
    // Check if the function parameter is set
    if (!isset($_GET['f'])) {
        throw new Exception('No function specified');
    }

    $action = $_GET['f'];

    // Create instance of Master class
    $Master = new Master();

    // Check if method exists
    if (!method_exists($Master, $action)) {
        throw new Exception('Function not found');
    }

    // Call the requested method and get the response
    $response = $Master->$action();
    echo $response;  // Master methods return JSON encoded strings

} catch (Exception $e) {
    error_log("Handler Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'failed',
        'msg' => $e->getMessage(),
        'error' => $e->getTraceAsString()
    ]);
}
