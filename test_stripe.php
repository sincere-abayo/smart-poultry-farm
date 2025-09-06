<?php
// Enable error reporting for debugging but don't display errors during AJAX
if (!isset($_POST['action'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    // For AJAX requests, only log errors, don't display them
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

require_once('config.php');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');

    try {
        // Check if StripeHandler class exists
        if (!file_exists('classes/StripeHandler.php')) {
            throw new Exception('StripeHandler.php not found');
        }

        require_once('classes/StripeHandler.php');
        $stripeHandler = new StripeHandler();
        $action = $_POST['action'];

        switch ($action) {
            case 'test_stripe_config':
                $result = [
                    'success' => true,
                    'message' => 'Stripe configuration loaded successfully',
                    'config' => [
                        'publishable_key' => substr($stripeHandler->getPublishableKey(), 0, 20) . '...',
                        'currency' => $stripeHandler->getCurrency(),
                        'environment' => $stripeHandler->getEnvironment()
                    ]
                ];
                break;

            case 'test_payment_intent':
                $amount = floatval($_POST['amount'] ?? 100);
                $currency = $_POST['currency'] ?? 'rwf';
                $amountInCents = $stripeHandler->convertToCents($amount);

                $result = $stripeHandler->createPaymentIntent($amountInCents, $currency, [
                    'test' => 'true',
                    'user_id' => 'test_user',
                    'timestamp' => time()
                ]);
                break;

            case 'test_checkout_session':
                $amount = floatval($_POST['amount'] ?? 100);
                $currency = $_POST['currency'] ?? 'rwf';
                $amountInCents = $stripeHandler->convertToCents($amount);

                $lineItems = [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'Test Product - Smart Poultry Farm'
                            ],
                            'unit_amount' => $amountInCents
                        ],
                        'quantity' => 1
                    ]
                ];

                $result = $stripeHandler->createCheckoutSession(
                    $lineItems,
                    'http://localhost/smart-poultry-farm/test_stripe.php?success=1',
                    'http://localhost/smart-poultry-farm/test_stripe.php?cancel=1',
                    ['test' => 'true', 'amount' => $amount]
                );

                // Add debug information
                if ($result['success']) {
                    $result['message'] = 'Checkout session created successfully!';
                    $result['data'] = [
                        'session_id' => $result['session_id'],
                        'url' => $result['url'],
                        'amount' => $amount,
                        'currency' => $currency
                    ];
                } else {
                    $result['message'] = 'Failed to create checkout session: ' . ($result['error'] ?? 'Unknown error');
                }
                break;

            case 'test_payment_confirmation':
                $paymentIntentId = $_POST['payment_intent_id'] ?? '';
                if (empty($paymentIntentId)) {
                    throw new Exception('Payment intent ID is required');
                }

                $result = $stripeHandler->confirmPaymentIntent($paymentIntentId);
                break;

            case 'test_amount_conversion':
                $amounts = [100, 1000, 10000, 100000];
                $conversions = [];

                foreach ($amounts as $amount) {
                    $cents = $stripeHandler->convertToCents($amount);
                    $backToAmount = $stripeHandler->convertFromCents($cents);
                    $conversions[] = [
                        'original' => $amount,
                        'cents' => $cents,
                        'converted_back' => $backToAmount,
                        'match' => $amount == $backToAmount
                    ];
                }

                $result = [
                    'success' => true,
                    'message' => 'Amount conversion test completed',
                    'conversions' => $conversions
                ];
                break;

            case 'retrieve_payment_intent':
                $paymentIntentId = $_POST['payment_intent_id'] ?? '';
                if (empty($paymentIntentId)) {
                    throw new Exception('Payment intent ID is required');
                }

                $result = $stripeHandler->retrievePaymentIntent($paymentIntentId);
                break;

            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }

        echo json_encode($result);

    } catch (Exception $e) {
        error_log("Stripe test error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    exit;
}

// Check for success/cancel parameters
$success = isset($_GET['success']);
$cancel = isset($_GET['cancel']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Integration Test - Smart Poultry Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            border-radius: 5px;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success {
            background-color: #10b981;
        }

        .status-error {
            background-color: #ef4444;
        }

        .status-warning {
            background-color: #f59e0b;
        }

        .config-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .result-box {
            min-height: 100px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            background-color: #f8f9fa;
        }

        .stripe-logo {
            width: 120px;
            height: auto;
            margin: 10px 0;
        }

        .test-card {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border: 2px solid #d1d5db;
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .test-card h6 {
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .test-card p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }

        .amount-display {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            text-align: center;
            margin: 20px 0;
        }

        .conversion-table {
            font-size: 0.9rem;
        }

        .conversion-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1><i class="fab fa-stripe"></i> Stripe Integration Test</h1>
                    <p class="mb-0">Smart Poultry Farm - Payment Processing Testing Interface</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <strong>Success!</strong> Payment completed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($cancel): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <strong>Cancelled!</strong> Payment was cancelled.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Configuration Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Stripe Configuration Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="config-section">
                                    <h6><i class="fab fa-stripe"></i> API Configuration</h6>
                                    <div id="stripe-config-status">
                                        <span class="status-indicator status-warning"></span>
                                        <span>Click "Test Config" to check</span>
                                    </div>
                                    <button class="btn btn-warning btn-sm mt-2" onclick="testStripeConfig()">
                                        <i class="fas fa-check"></i> Test Config
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="config-section">
                                    <h6><i class="fas fa-key"></i> API Keys</h6>
                                    <div id="api-keys-status">
                                        <span class="status-indicator status-warning"></span>
                                        <span>Check configuration</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="config-section">
                                    <h6><i class="fas fa-globe"></i> Environment</h6>
                                    <div id="environment-status">
                                        <span class="status-indicator status-warning"></span>
                                        <span>Test mode</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Intent Testing -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Payment Intent Testing</h5>
                    </div>
                    <div class="card-body">
                        <form id="payment-intent-form">
                            <div class="mb-3">
                                <label for="pi-amount" class="form-label">Amount (RWF)</label>
                                <input type="number" class="form-control" id="pi-amount" value="100" min="1"
                                    step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="pi-currency" class="form-label">Currency</label>
                                <select class="form-control" id="pi-currency">
                                    <option value="rwf">RWF (Rwandan Franc)</option>
                                    <option value="usd">USD (US Dollar)</option>
                                    <option value="eur">EUR (Euro)</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="testPaymentIntent()">
                                <i class="fas fa-plus"></i> Create Payment Intent
                            </button>
                        </form>
                        <div class="mt-3">
                            <div class="result-box" id="payment-intent-result">
                                <small class="text-muted">Payment intent test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Session Testing -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Checkout Session Testing</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkout-session-form">
                            <div class="mb-3">
                                <label for="cs-amount" class="form-label">Amount (RWF)</label>
                                <input type="number" class="form-control" id="cs-amount" value="100" min="1"
                                    step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="cs-currency" class="form-label">Currency</label>
                                <select class="form-control" id="cs-currency">
                                    <option value="rwf">RWF (Rwandan Franc)</option>
                                    <option value="usd">USD (US Dollar)</option>
                                    <option value="eur">EUR (Euro)</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-success" onclick="testCheckoutSession()">
                                <i class="fas fa-external-link-alt"></i> Create Checkout Session
                            </button>
                        </form>
                        <div class="mt-3">
                            <div class="result-box" id="checkout-session-result">
                                <small class="text-muted">Checkout session test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Conversion Testing -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Amount Conversion Testing</h5>
                    </div>
                    <div class="card-body">
                        <p>Test conversion between RWF amounts and Stripe cents:</p>
                        <button type="button" class="btn btn-warning" onclick="testAmountConversion()">
                            <i class="fas fa-exchange-alt"></i> Test Conversions
                        </button>
                        <div class="mt-3">
                            <div class="result-box" id="conversion-result">
                                <small class="text-muted">Amount conversion test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Confirmation Testing -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Payment Confirmation Testing</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="confirm-pi-id" class="form-label">Payment Intent ID</label>
                            <input type="text" class="form-control" id="confirm-pi-id"
                                placeholder="pi_1234567890abcdef">
                        </div>
                        <p>Test direct payment confirmation (without webhooks):</p>
                        <button type="button" class="btn btn-danger" onclick="testPaymentConfirmation()">
                            <i class="fas fa-check"></i> Confirm Payment
                        </button>
                        <div class="mt-3">
                            <div class="result-box" id="payment-confirmation-result">
                                <small class="text-muted">Payment confirmation test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Intent Retrieval -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Payment Intent Retrieval</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="retrieve-pi-id" class="form-label">Payment Intent ID</label>
                                    <input type="text" class="form-control" id="retrieve-pi-id"
                                        placeholder="pi_1234567890abcdef">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100"
                                        onclick="retrievePaymentIntent()">
                                        <i class="fas fa-search"></i> Retrieve
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="result-box" id="retrieve-result">
                                <small class="text-muted">Payment intent retrieval results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Cards Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Stripe Test Cards</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="test-card">
                                    <h6><i class="fas fa-check-circle text-success"></i> Successful Payment</h6>
                                    <p><strong>4242 4242 4242 4242</strong></p>
                                    <p>Use this card for successful payments</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="test-card">
                                    <h6><i class="fas fa-times-circle text-danger"></i> Declined Payment</h6>
                                    <p><strong>4000 0000 0000 0002</strong></p>
                                    <p>Use this card for declined payments</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="test-card">
                                    <h6><i class="fas fa-exclamation-triangle text-warning"></i> Requires Authentication
                                    </h6>
                                    <p><strong>4000 0025 0000 3155</strong></p>
                                    <p>Use this card for 3D Secure authentication</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="test-card">
                                    <h6><i class="fas fa-info-circle text-info"></i> Insufficient Funds</h6>
                                    <p><strong>4000 0000 0000 9995</strong></p>
                                    <p>Use this card for insufficient funds</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Instructions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Setup Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fab fa-stripe"></i> Stripe Setup</h6>
                                <ol>
                                    <li>Create a Stripe account at <a href="https://stripe.com"
                                            target="_blank">stripe.com</a></li>
                                    <li>Get your API keys from the Stripe Dashboard</li>
                                    <li>Update <code>stripe_config.php</code> with your keys:
                                        <ul>
                                            <li><code>STRIPE_PUBLISHABLE_KEY</code> - Your publishable key (pk_test_...)
                                            </li>
                                            <li><code>STRIPE_SECRET_KEY</code> - Your secret key (sk_test_...)</li>
                                        </ul>
                                    </li>
                                    <li><strong>No webhook setup needed!</strong> Uses direct payment confirmation</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cogs"></i> Payment Processing</h6>
                                <ol>
                                    <li><strong>No Webhooks Required!</strong> This integration uses direct payment
                                        confirmation</li>
                                    <li>Payments are confirmed immediately after Stripe checkout</li>
                                    <li>Order processing happens automatically on payment success</li>
                                    <li>Email and SMS notifications are sent instantly</li>
                                    <li>Simpler setup - no webhook configuration needed</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLoading(elementId) {
            document.getElementById(elementId).innerHTML =
                '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        }

        function showResult(elementId, result) {
            const element = document.getElementById(elementId);

            // Handle undefined or null result
            if (!result) {
                element.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> No response received</div>';
                return;
            }

            const isSuccess = result.success;
            const statusClass = isSuccess ? 'text-success' : 'text-danger';
            const statusIcon = isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
            const message = result.message || (isSuccess ? 'Operation completed successfully' : 'Operation failed');

            let html = `<div class="${statusClass}"><i class="fas ${statusIcon}"></i> ${message}</div>`;

            if (result.data) {
                html += '<div class="mt-2"><small class="text-muted">Data:</small><pre class="mt-1">' + JSON.stringify(
                    result.data, null, 2) + '</pre></div>';
            }

            if (result.config) {
                html += '<div class="mt-2"><small class="text-muted">Configuration:</small><pre class="mt-1">' + JSON.stringify(
                    result.config, null, 2) + '</pre></div>';
            }

            if (result.conversions) {
                html += '<div class="mt-2"><small class="text-muted">Conversions:</small>';
                html += '<table class="table table-sm conversion-table mt-2">';
                html += '<thead><tr><th>Original (RWF)</th><th>Cents</th><th>Converted Back</th><th>Match</th></tr></thead><tbody>';
                result.conversions.forEach(conv => {
                    const matchClass = conv.match ? 'text-success' : 'text-danger';
                    const matchIcon = conv.match ? 'fa-check' : 'fa-times';
                    html += `<tr><td>${conv.original}</td><td>${conv.cents}</td><td>${conv.converted_back}</td><td class="${matchClass}"><i class="fas ${matchIcon}"></i></td></tr>`;
                });
                html += '</tbody></table></div>';
            }

            if (result.error) {
                html += '<div class="mt-2"><small class="text-danger">Error:</small><pre class="mt-1">' + result.error +
                    '</pre></div>';
            }

            element.innerHTML = html;
        }

        function testStripeConfig() {
            showLoading('stripe-config-status');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_stripe_config'
            })
                .then(response => response.json())
                .then(result => {
                    const element = document.getElementById('stripe-config-status');
                    const isSuccess = result.success;
                    const statusClass = isSuccess ? 'status-success' : 'status-error';
                    const statusText = isSuccess ? 'Stripe config loaded successfully' : 'Stripe config has issues';

                    element.innerHTML =
                        `<span class="status-indicator ${statusClass}"></span><span>${statusText}</span>`;

                    if (result.config) {
                        element.innerHTML += '<br><small class="text-muted">' + JSON.stringify(result.config) +
                            '</small>';
                    }

                    if (result.error) {
                        element.innerHTML += '<br><small class="text-danger">Error: ' + result.error + '</small>';
                    }
                })
                .catch(error => {
                    console.error('Stripe config test error:', error);
                    document.getElementById('stripe-config-status').innerHTML =
                        '<span class="status-indicator status-error"></span><span>Error testing config: ' + error
                            .message + '</span>';
                });
        }

        function testPaymentIntent() {
            const amount = document.getElementById('pi-amount').value;
            const currency = document.getElementById('pi-currency').value;

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            showLoading('payment-intent-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=test_payment_intent&amount=${encodeURIComponent(amount)}&currency=${encodeURIComponent(currency)}`
            })
                .then(response => response.json())
                .then(result => showResult('payment-intent-result', result))
                .catch(error => showResult('payment-intent-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        function testCheckoutSession() {
            const amount = document.getElementById('cs-amount').value;
            const currency = document.getElementById('cs-currency').value;

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            showLoading('checkout-session-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=test_checkout_session&amount=${encodeURIComponent(amount)}&currency=${encodeURIComponent(currency)}`
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('Checkout session result:', result); // Debug log
                    showResult('checkout-session-result', result);
                    if (result.success && result.url) {
                        // Show success message and automatically redirect
                        const element = document.getElementById('checkout-session-result');
                        element.innerHTML += '<div class="mt-3">' +
                            '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Redirecting to Stripe Checkout in 3 seconds...</div>' +
                            '<div class="text-center">' +
                            '<a href="' + result.url + '" class="btn btn-success me-2" target="_blank"><i class="fas fa-external-link-alt"></i> Open Checkout Now</a>' +
                            '<button class="btn btn-secondary" onclick="this.parentElement.parentElement.style.display=\'none\'"><i class="fas fa-times"></i> Cancel</button>' +
                            '</div>' +
                            '</div>';

                        // Automatically redirect after 3 seconds
                        setTimeout(() => {
                            window.open(result.url, '_blank');
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Checkout session error:', error); // Debug log
                    showResult('checkout-session-result', {
                        success: false,
                        message: 'Network error: ' + error.message
                    });
                });
        }

        function testAmountConversion() {
            showLoading('conversion-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_amount_conversion'
            })
                .then(response => response.json())
                .then(result => showResult('conversion-result', result))
                .catch(error => showResult('conversion-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        function testPaymentConfirmation() {
            const paymentIntentId = document.getElementById('confirm-pi-id').value;

            if (!paymentIntentId) {
                alert('Please enter a payment intent ID');
                return;
            }

            showLoading('payment-confirmation-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=test_payment_confirmation&payment_intent_id=${encodeURIComponent(paymentIntentId)}`
            })
                .then(response => response.json())
                .then(result => showResult('payment-confirmation-result', result))
                .catch(error => showResult('payment-confirmation-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        function retrievePaymentIntent() {
            const paymentIntentId = document.getElementById('retrieve-pi-id').value;

            if (!paymentIntentId) {
                alert('Please enter a payment intent ID');
                return;
            }

            showLoading('retrieve-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=retrieve_payment_intent&payment_intent_id=${encodeURIComponent(paymentIntentId)}`
            })
                .then(response => response.json())
                .then(result => showResult('retrieve-result', result))
                .catch(error => showResult('retrieve-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        // Auto-test configuration on page load
        document.addEventListener('DOMContentLoaded', function () {
            testStripeConfig();
        });
    </script>
</body>

</html>