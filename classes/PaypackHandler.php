<?php
/**
 * Paypack Payment Handler
 * Integrates with Paypack Rwanda payment gateway
 */

class PaypackHandler
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $tokenExpiry;

    public function __construct()
    {
        // Load configuration from environment
        $this->baseUrl = $_ENV['PAYPACK_BASE_URL'] ?? 'https://payments.paypack.rw/api';
        $this->clientId = $_ENV['PAYPACK_CLIENT_ID'] ?? null;
        $this->clientSecret = $_ENV['PAYPACK_CLIENT_SECRET'] ?? null;

        if (!$this->clientId || !$this->clientSecret) {
            throw new Exception('Paypack credentials not configured');
        }
    }

    /**
     * Authenticate with Paypack and get access token
     */
    public function authenticate()
    {
        try {
            $url = $this->baseUrl . '/auth/agents/authorize';

            $data = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];

            $response = $this->makeRequest($url, 'POST', $data);

            if (isset($response['access']) && isset($response['refresh'])) {
                $this->accessToken = $response['access'];
                $this->tokenExpiry = time() + 3600; // Token typically expires in 1 hour
                return [
                    'success' => true,
                    'access_token' => $response['access'],
                    'refresh_token' => $response['refresh'],
                    'expiry' => $response['expires'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Authentication failed: Invalid response from Paypack'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Authentication failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initiate cashin transaction
     */
    public function initiateCashin($amount, $phoneNumber)
    {
        try {
            // Ensure we have a valid token
            if (!$this->isTokenValid()) {
                $authResult = $this->authenticate();
                if (!$authResult['success']) {
                    return $authResult;
                }
            }

            $url = $this->baseUrl . '/transactions/cashin';

            $data = [
                'amount' => (int)$amount,
                'number' => $this->formatPhoneNumber($phoneNumber)
            ];

            $headers = [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'Accept: application/json'
            ];

            $response = $this->makeRequest($url, 'POST', $data, $headers);

            if (isset($response['ref']) && isset($response['status'])) {
                return [
                    'success' => true,
                    'reference' => $response['ref'],
                    'status' => $response['status'],
                    'amount' => $response['amount'] ?? $amount,
                    'created_at' => $response['created_at'] ?? null,
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Transaction failed: ' . (isset($response['message']) ? $response['message'] : 'Unknown error')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Transaction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($reference)
    {
        try {
            // Ensure we have a valid token
            if (!$this->isTokenValid()) {
                $authResult = $this->authenticate();
                if (!$authResult['success']) {
                    return $authResult;
                }
            }

            $url = $this->baseUrl . '/transactions/find/' . $reference;

            $headers = [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'Accept: application/json'
            ];

            $response = $this->makeRequest($url, 'GET', null, $headers);

            if (isset($response['ref']) && isset($response['status'])) {
                return [
                    'success' => true,
                    'reference' => $response['ref'],
                    'status' => $response['status'],
                    'amount' => $response['amount'] ?? null,
                    'client' => $response['client'] ?? null,
                    'fee' => $response['fee'] ?? null,
                    'kind' => $response['kind'] ?? null,
                    'merchant' => $response['merchant'] ?? null,
                    'timestamp' => $response['timestamp'] ?? null,
                    'data' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Status check failed: ' . (isset($response['message']) ? $response['message'] : 'Unknown error')
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Status check failed: ' . $e->getMessage()
            ];
        }
    }


    /**
     * Check if current token is valid
     */
    private function isTokenValid()
    {
        return $this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry;
    }

    /**
     * Format phone number to Paypack format
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Handle Rwandan phone numbers
        if (strlen($phoneNumber) === 9 && $phoneNumber[0] === '7') {
            // Already in correct format (07XXXXXXXX)
            return '0' . $phoneNumber;
        } elseif (strlen($phoneNumber) === 10 && $phoneNumber[0] === '0' && $phoneNumber[1] === '7') {
            // Already in correct format (07XXXXXXXX)
            return $phoneNumber;
        } elseif (strlen($phoneNumber) === 12 && substr($phoneNumber, 0, 3) === '250') {
            // International format (2507XXXXXXXX)
            return '0' . substr($phoneNumber, 3);
        } else {
            // Try to convert to correct format
            if (strlen($phoneNumber) === 9) {
                return '0' . $phoneNumber;
            } elseif (strlen($phoneNumber) === 8) {
                return '07' . $phoneNumber;
            }
        }

        return $phoneNumber; // Return as-is if format is unclear
    }

    /**
     * Make HTTP request to Paypack API
     */
    private function makeRequest($url, $method = 'GET', $data = null, $additionalHeaders = [])
    {
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if (!empty($additionalHeaders)) {
            $headers = array_merge($headers, $additionalHeaders);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET') {
            // GET is default
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $decodedResponse;
        } else {
            $errorMessage = isset($decodedResponse['message']) ? $decodedResponse['message'] : 'HTTP ' . $httpCode;
            throw new Exception('API Error: ' . $errorMessage);
        }
    }

}