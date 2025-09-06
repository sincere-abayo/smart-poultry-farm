<?php
/**
 * Paypack Payment Handler
 * Handles Paypack Rwanda payment processing (MTN Mobile Money)
 */

class PaypackHandler
{
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $accessToken;
    private $tokenExpires;

    public function __construct()
    {
        $this->clientId = $_ENV['PAYPACK_CLIENT_ID'] ?? null;
        $this->clientSecret = $_ENV['PAYPACK_CLIENT_SECRET'] ?? null;
        $this->baseUrl = $_ENV['PAYPACK_BASE_URL'] ?? 'https://payments.paypack.rw/api';
        $this->accessToken = null;
        $this->tokenExpires = null;
    }

    /**
     * Authenticate and get JWT access token
     * @return string|false
     */
    public function authenticate()
    {
        // Use cached token if valid
        if ($this->accessToken && $this->tokenExpires && $this->tokenExpires > time() + 60) {
            return $this->accessToken;
        }
        $url = $this->baseUrl . '/auth/agents/authorize';
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        $response = $this->curlRequest($url, $data, $headers);
        if ($response['success'] && isset($response['data']['access'])) {
            $this->accessToken = $response['data']['access'];
            $this->tokenExpires = isset($response['data']['expires']) ? (int) $response['data']['expires'] : (time() + 3500);
            return $this->accessToken;
        }
        error_log('Paypack Auth Error: ' . $response['error']);
        return false;
    }

    /**
     * Initiate a cashin (deposit) transaction
     * @param float $amount
     * @param string $number (MTN number)
     * @return array
     */
    public function cashin($amount, $number)
    {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => false, 'error' => 'Authentication failed'];
        }
        $url = $this->baseUrl . '/transactions/cashin';
        $data = [
            'amount' => $amount,
            'number' => $number
        ];
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ];
        $response = $this->curlRequest($url, $data, $headers);
        if ($response['success'] && isset($response['data']['ref'])) {
            return [
                'success' => true,
                'ref' => $response['data']['ref'],
                'status' => $response['data']['status'],
                'raw' => $response['data']
            ];
        }
        return ['success' => false, 'error' => $response['error']];
    }

    /**
     * Poll transaction status using events endpoint
     * @param string $ref
     * @param string $number
     * @return array
     */
    public function pollStatus($ref, $number)
    {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => false, 'error' => 'Authentication failed'];
        }
        $url = $this->baseUrl . '/events/transactions?ref=' . urlencode($ref) . '&kind=CASHIN&client=' . urlencode($number);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ];
        $response = $this->curlRequest($url, null, $headers, 'GET');
        // Log the full response for debugging
        error_log('Paypack pollStatus response: ' . json_encode($response));
        if ($response['success'] && isset($response['data']['transactions']) && is_array($response['data']['transactions'])) {
            $events = $response['data']['transactions'];
            $foundProcessed = false;
            foreach ($events as $event) {
                $event_kind = $event['event_kind'] ?? $event['event-kind'] ?? null;
                $status = $event['data']['status'] ?? null;
                if ($event_kind === 'transaction:processed') {
                    $foundProcessed = true;
                    if ($status === 'successful') {
                        return [
                            'success' => true,
                            'status' => 'successful',
                            'event' => $event
                        ];
                    } elseif ($status === 'failed') {
                        return [
                            'success' => true,
                            'status' => 'failed',
                            'event' => $event
                        ];
                    }
                }
            }
            // If no processed event, but there are events, it's still pending
            if (!$foundProcessed) {
                return [
                    'success' => true,
                    'status' => 'pending',
                    'event' => $events[0]
                ];
            }
        }
        return ['success' => false, 'error' => $response['error']];
    }

    /**
     * Robust status check: use /transactions/find/{ref} for success/fail, fallback to /events/transactions for pending
     */
    public function checkTransactionStatus($ref, $number)
    {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => false, 'error' => 'Authentication failed'];
        }
        // Try /transactions/find/{ref} first
        $url = $this->baseUrl . '/transactions/find/' . urlencode($ref);
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
            // Fallback to events for pending
            return $this->pollStatus($ref, $number);
        }
        $status = $data['status'] ?? null;
        if ($status === 'successful') {
            return ['success' => true, 'status' => 'successful', 'raw' => $data];
        } elseif ($status === 'failed') {
            return ['success' => true, 'status' => 'failed', 'raw' => $data];
        }
        // If not clear, fallback to events
        return $this->pollStatus($ref, $number);
    }

    /**
     * Helper for cURL requests
     */
    private function curlRequest($url, $data = null, $headers = [], $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            return ['success' => false, 'error' => 'cURL Error: ' . $error];
        }
        $responseData = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $responseData];
        } else {
            $errorMessage = isset($responseData['error']) ? $responseData['error'] : 'HTTP Error: ' . $httpCode;
            return ['success' => false, 'error' => $errorMessage, 'http_code' => $httpCode];
        }
    }
}
