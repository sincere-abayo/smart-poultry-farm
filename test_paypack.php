<?php
require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$clientId = $_ENV['PAYPACK_CLIENT_ID'] ?? null;
$clientSecret = $_ENV['PAYPACK_CLIENT_SECRET'] ?? null;
$baseUrl = $_ENV['PAYPACK_BASE_URL'] ?? 'https://payments.paypack.rw/api';
echo "PAYPACK_CLIENT_ID: " . ($clientId ?: 'NOT SET') . PHP_EOL;
echo "PAYPACK_CLIENT_SECRET: " . ($clientSecret ? substr($clientSecret, 0, 8) . '...' : 'NOT SET') . PHP_EOL;

function paypack_auth($clientId, $clientSecret, $baseUrl)
{
    $url = $baseUrl . '/auth/agents/authorize';
    $data = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret
    ];
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
        echo "cURL Error: $error\n";
        return false;
    }
    $data = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && isset($data['access'])) {
        return $data['access'];
    }
    echo "Auth failed: $response\n";
    return false;
}

function paypack_poll($ref, $number, $baseUrl, $token)
{
    $url = $baseUrl . '/events/transactions?ref=' . urlencode($ref) . '&kind=CASHIN&client=' . urlencode($number);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    echo "\nHTTP Code: $httpCode\n";
    if ($error) {
        echo "cURL Error: $error\n";
    }
    echo "Raw Response: $response\n";
    $data = json_decode($response, true);
    if (!isset($data['transactions']) || !is_array($data['transactions'])) {
        echo "No transactions found or invalid response.\n";
        return;
    }
    $events = $data['transactions'];
    $foundProcessed = false;
    foreach ($events as $event) {
        $event_kind = $event['event_kind'] ?? $event['event-kind'] ?? null;
        $status = $event['data']['status'] ?? null;
        echo "Event: kind=$event_kind, status=$status\n";
        if ($event_kind === 'transaction:processed') {
            $foundProcessed = true;
            if ($status === 'successful') {
                echo "==> Payment is SUCCESSFUL (transaction:processed)\n";
                return;
            } elseif ($status === 'failed') {
                echo "==> Payment is FAILED (transaction:processed)\n";
                return;
            }
        }
    }
    if (!$foundProcessed) {
        echo "==> Payment is still PENDING (no transaction:processed event)\n";
    }
}

// Prompt for ref and number if not set
$ref = readline("Enter Paypack ref: ");
$number = readline("Enter MTN number: ");
$token = paypack_auth($clientId, $clientSecret, $baseUrl);
if ($token && $ref && $number) {
    paypack_poll($ref, $number, $baseUrl, $token);
} else {
    echo "Missing token, ref, or number.\n";
}
?>