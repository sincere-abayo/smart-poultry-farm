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

$action = $_GET['action'] ?? $_POST['action'] ?? null;

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
        return ['error' => 'Auth failed', 'raw' => $response];
    }
    return ['access' => $data['access'], 'expires' => $data['expires'] ?? null];
}

function paypack_create($amount, $number, $baseUrl, $token)
{
    $url = $baseUrl . '/transactions/cashin';
    $data = ['amount' => floatval($amount), 'number' => $number];
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
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if ($error || $httpCode < 200 || $httpCode >= 300 || !isset($data['ref'])) {
        return ['error' => 'Create failed', 'raw' => $response];
    }
    return ['ref' => $data['ref'], 'status' => $data['status'] ?? null, 'raw' => $data];
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

function paypack_find($ref, $baseUrl, $token)
{
    $url = $baseUrl . '/transactions/find/' . urlencode($ref);
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
    if ($error || $httpCode == 404 || (isset($data['message']) && $data['message'] === 'transaction not found')) {
        return ['status' => 'pending', 'raw' => $data];
    }
    $status = $data['status'] ?? null;
    if ($status === 'successful') {
        return ['status' => 'successful', 'raw' => $data];
    } elseif ($status === 'failed') {
        return ['status' => 'failed', 'raw' => $data];
    }
    return ['status' => 'pending', 'raw' => $data];
}

if ($action === 'auth') {
    echo json_encode(paypack_auth($clientId, $clientSecret, $baseUrl));
    exit;
}
if ($action === 'create') {
    $number = $_POST['number'] ?? null;
    $amount = $_POST['amount'] ?? null;
    if (!$number || !$amount) {
        echo json_encode(['error' => 'Missing number or amount']);
        exit;
    }
    $auth = paypack_auth($clientId, $clientSecret, $baseUrl);
    if (!isset($auth['access'])) {
        echo json_encode(['error' => 'Auth failed', 'raw' => $auth]);
        exit;
    }
    echo json_encode(paypack_create($amount, $number, $baseUrl, $auth['access']));
    exit;
}
if ($action === 'status') {
    $ref = $_POST['ref'] ?? null;
    $number = $_POST['number'] ?? null;
    if (!$ref) {
        echo json_encode(['error' => 'Missing ref']);
        exit;
    }
    $auth = paypack_auth($clientId, $clientSecret, $baseUrl);
    if (!isset($auth['access'])) {
        echo json_encode(['error' => 'Auth failed', 'raw' => $auth]);
        exit;
    }
    // Try /transactions/find/{ref} first
    $find = paypack_find($ref, $baseUrl, $auth['access']);
    if ($find['status'] === 'pending') {
        // If not found, fallback to events for pending
        $poll = paypack_poll($ref, $number, $baseUrl, $auth['access']);
        echo json_encode($poll);
        exit;
    } else {
        echo json_encode($find);
        exit;
    }
}
echo json_encode(['error' => 'Invalid action', 'usage' => 'action=auth|create|status']);