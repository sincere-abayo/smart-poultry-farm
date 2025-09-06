<?php
/**
 * Stripe Payment Handler
 * Handles Stripe payment processing without composer packages
 */

require_once(__DIR__ . '/../stripe_config.php');

class StripeHandler
{
    private $secretKey;
    private $publishableKey;
    private $currency;
    private $environment;

    public function __construct()
    {
        $this->secretKey = STRIPE_SECRET_KEY;
        $this->publishableKey = STRIPE_PUBLISHABLE_KEY;
        $this->currency = STRIPE_CURRENCY;
        $this->environment = STRIPE_ENVIRONMENT;
    }

    /**
     * Create a checkout session for hosted checkout
     * 
     * @param float $amount Amount in cents
     * @param string $currency Currency code
     * @param array $metadata Additional metadata
     * @param string $successUrl Success redirect URL
     * @param string $cancelUrl Cancel redirect URL
     * @return array Result array
     */
    public function createCheckoutSession($amount, $currency = null, $metadata = [], $successUrl = '', $cancelUrl = '')
    {
        try {
            $currency = $currency ?: $this->currency;

            // Default URLs if not provided
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
            $successUrl = $successUrl ?: $baseUrl . '/stripe_success.php';
            $cancelUrl = $cancelUrl ?: $baseUrl . '/stripe_cancel.php';

            $data = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'Order Payment',
                                'description' => 'Payment for order'
                            ],
                            'unit_amount' => $amount
                        ],
                        'quantity' => 1
                    ]
                ],
                'mode' => 'payment',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'metadata' => $metadata
            ];

            $response = $this->makeStripeRequest('checkout/sessions', $data);

            if ($response['success']) {
                return [
                    'success' => true,
                    'checkout_url' => $response['data']['url'],
                    'session_id' => $response['data']['id']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create checkout session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retrieve a checkout session
     * 
     * @param string $sessionId Session ID
     * @return array Result array
     */
    public function retrieveCheckoutSession($sessionId)
    {
        try {
            $response = $this->makeStripeRequest('checkout/sessions/' . $sessionId, [], 'GET');

            if ($response['success']) {
                return [
                    'success' => true,
                    'session' => $response['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to retrieve checkout session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment intent
     * 
     * @param float $amount Amount in cents
     * @param string $currency Currency code
     * @param array $metadata Additional metadata
     * @return array Result array
     */
    public function createPaymentIntent($amount, $currency = null, $metadata = [])
    {
        try {
            $currency = $currency ?: $this->currency;

            $data = [
                'amount' => $amount,
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'metadata' => $metadata
            ];

            $response = $this->makeStripeRequest('payment_intents', $data);

            if ($response['success']) {
                return [
                    'success' => true,
                    'client_secret' => $response['data']['client_secret'],
                    'payment_intent_id' => $response['data']['id']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    /**
     * Retrieve a payment intent
     * 
     * @param string $paymentIntentId Payment intent ID
     * @return array Result array
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        try {
            $response = $this->makeStripeRequest("payment_intents/{$paymentIntentId}", [], 'GET');

            if ($response['success']) {
                return [
                    'success' => true,
                    'payment_intent' => $response['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirm payment intent (direct confirmation without webhooks)
     * 
     * @param string $paymentIntentId Payment intent ID
     * @return array Result array
     */
    public function confirmPaymentIntent($paymentIntentId)
    {
        try {
            $data = [
                'payment_method' => 'pm_card_visa' // Default test payment method
            ];

            $response = $this->makeStripeRequest("payment_intents/{$paymentIntentId}/confirm", $data);

            if ($response['success']) {
                return [
                    'success' => true,
                    'payment_intent' => $response['data'],
                    'status' => $response['data']['status']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if payment intent is successful
     * 
     * @param string $paymentIntentId Payment intent ID
     * @return array Result array
     */
    public function isPaymentSuccessful($paymentIntentId)
    {
        try {
            $result = $this->retrievePaymentIntent($paymentIntentId);

            if ($result['success']) {
                $status = $result['payment_intent']['status'];
                return [
                    'success' => true,
                    'is_successful' => $status === 'succeeded',
                    'status' => $status
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error']
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make a request to Stripe API
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $method HTTP method
     * @return array Response array
     */
    private function makeStripeRequest($endpoint, $data = [], $method = 'POST')
    {
        try {
            $url = STRIPE_API_URL . '/' . $endpoint;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERPWD, $this->secretKey . ':');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Stripe-Version: 2020-08-27'
            ]);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'error' => 'cURL Error: ' . $error
                ];
            }

            $responseData = json_decode($response, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                $errorMessage = isset($responseData['error']['message'])
                    ? $responseData['error']['message']
                    : 'HTTP Error: ' . $httpCode;

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'http_code' => $httpCode
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get publishable key
     * 
     * @return string Publishable key
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }

    /**
     * Get currency
     * 
     * @return string Currency code
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get environment
     * 
     * @return string Environment (test/live)
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Convert amount to cents
     * 
     * @param float $amount Amount in main currency unit
     * @return int Amount in cents
     */
    public function convertToCents($amount)
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert cents to amount
     * 
     * @param int $cents Amount in cents
     * @return float Amount in main currency unit
     */
    public function convertFromCents($cents)
    {
        return $cents / 100;
    }
}
?>