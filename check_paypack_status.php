<?php
require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
header('Content-Type: application/json');

$clientId = $_ENV['PAYPACK_CLIENT_ID'] ?? null;
$clientSecret = $_ENV['PAYPACK_CLIENT_SECRET'] ?? null;
$baseUrl = $_ENV['PAYPACK_BASE_URL'] ?? 'https://payments.paypack.rw/api';

$ref = $_GET['ref'] ?? $_POST['ref'] ?? null;
$number = $_GET['number'] ?? $_POST['number'] ?? null;

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
    $data = json_decode($response, true);
    if ($error || $httpCode < 200 || $httpCode >= 300 || !isset($data['access'])) {
        return false;
    }
    return $data['access'];
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
    $data = json_decode($response, true);
    if (!isset($data['transactions']) || !is_array($data['transactions'])) {
        return ['status' => 'pending', 'raw' => $data];
    }
    $events = $data['transactions'];
    $foundProcessed = false;
    foreach ($events as $event) {
        $event_kind = $event['event_kind'] ?? $event['event-kind'] ?? null;
        $status = $event['data']['status'] ?? null;
        if ($event_kind === 'transaction:processed') {
            $foundProcessed = true;
            if ($status === 'successful') {
                return ['status' => 'successful', 'event' => $event, 'raw' => $data];
            } elseif ($status === 'failed') {
                return ['status' => 'failed', 'event' => $event, 'raw' => $data];
            }
        }
    }
    if (!$foundProcessed) {
        return ['status' => 'pending', 'event' => $events[0], 'raw' => $data];
    }
    return ['status' => 'pending', 'raw' => $data];
}

if (!$ref || !$number) {
    echo json_encode(['error' => 'Missing ref or number', 'usage' => 'GET or POST ref, number']);
    exit;
}
$token = paypack_auth($clientId, $clientSecret, $baseUrl);
if (!$token) {
    echo json_encode(['error' => 'Paypack authentication failed']);
    exit;
}
$result = paypack_poll($ref, $number, $baseUrl, $token);
echo json_encode($result, JSON_PRETTY_PRINT);
