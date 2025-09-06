<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Don't display errors in JSON response
ini_set('log_errors', 1);

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
        // Check if NotificationService class exists
        if (!file_exists('classes/NotificationService.php')) {
            throw new Exception('NotificationService.php not found');
        }

        require_once('classes/NotificationService.php');
        $notificationService = new NotificationService();
        $action = $_POST['action'];

        switch ($action) {
            case 'test_email_config':
                $result = $notificationService->testEmailConfig();
                break;

            case 'test_sms_config':
                $result = $notificationService->testSMSConfig();
                break;

            case 'send_test_email':
                $to = $_POST['email'] ?? '';
                $subject = $_POST['subject'] ?? 'Test Email';
                $body = $_POST['body'] ?? 'This is a test email from Selling  Poultry Farm notification system.';
                $result = $notificationService->sendEmail($to, $subject, $body);
                break;

            case 'send_test_sms':
                $phone = $_POST['phone'] ?? '';
                $message = $_POST['message'] ?? 'Test SMS from Selling  Poultry Farm notification system.';
                $result = $notificationService->sendSMS($phone, $message);
                break;

            case 'send_template_email':
                $to = $_POST['email'] ?? '';
                $template = $_POST['template'] ?? 'welcome';
                $variables = json_decode($_POST['variables'] ?? '{}', true);

                $templates = $notificationService->getEmailTemplates();
                if (isset($templates[$template])) {
                    $templateData = $templates[$template];
                    $subject = $notificationService->replaceTemplateVariables($templateData['subject'], $variables);
                    $body = $notificationService->replaceTemplateVariables($templateData['body'], $variables);
                    $result = $notificationService->sendEmail($to, $subject, $body);
                } else {
                    $result = ['success' => false, 'message' => 'Template not found'];
                }
                break;

            default:
                $result = ['success' => false, 'message' => 'Invalid action'];
        }

        echo json_encode($result);

    } catch (Exception $e) {
        error_log("Notification test error: " . $e->getMessage());
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

// Get templates for display
$templates = [];
try {
    // Ensure NotificationService is loaded
    if (!class_exists('NotificationService')) {
        require_once('classes/NotificationService.php');
    }
    $notificationService = new NotificationService();
    $templates = $notificationService->getEmailTemplates();
} catch (Exception $e) {
    error_log("Error loading templates: " . $e->getMessage());
    $templates = [
        'welcome' => ['subject' => 'Welcome', 'body' => 'Welcome template'],
        'payment_confirmation' => ['subject' => 'Payment Confirmation', 'body' => 'Payment template'],
        'order_status_update' => ['subject' => 'Order Update', 'body' => 'Order template'],
        'admin_payment_notification' => ['subject' => 'Admin Notification', 'body' => 'Admin template']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System Test - Selling Poultry Farm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
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
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a5f 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            border: none;
            border-radius: 5px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
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
            background-color: #28a745;
        }

        .status-error {
            background-color: #dc3545;
        }

        .status-warning {
            background-color: #ffc107;
        }

        .config-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .template-preview {
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
        }

        .loading {
            display: none;
        }

        .result-box {
            min-height: 100px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1><i class="fas fa-bell"></i> Notification System Test</h1>
                    <p class="mb-0">Selling Poultry Farm - Email & SMS Testing Interface</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Configuration Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Configuration Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="config-section">
                                    <h6><i class="fas fa-envelope"></i> Email Configuration</h6>
                                    <div id="email-config-status">
                                        <span class="status-indicator status-warning"></span>
                                        <span>Click "Test Email Config" to check</span>
                                    </div>
                                    <button class="btn btn-warning btn-sm mt-2" onclick="testEmailConfig()">
                                        <i class="fas fa-check"></i> Test Email Config
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="config-section">
                                    <h6><i class="fas fa-sms"></i> SMS Configuration</h6>
                                    <div id="sms-config-status">
                                        <span class="status-indicator status-warning"></span>
                                        <span>Click "Test SMS Config" to check</span>
                                    </div>
                                    <button class="btn btn-warning btn-sm mt-2" onclick="testSMSConfig()">
                                        <i class="fas fa-check"></i> Test SMS Config
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Testing -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> Email Testing</h5>
                    </div>
                    <div class="card-body">
                        <form id="email-test-form">
                            <div class="mb-3">
                                <label for="test-email" class="form-label">Test Email Address</label>
                                <input type="email" class="form-control" id="test-email" placeholder="test@example.com"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="email-subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="email-subject"
                                    value="Test Email from Selling  Poultry Farm">
                            </div>
                            <div class="mb-3">
                                <label for="email-body" class="form-label">Message Body</label>
                                <textarea class="form-control" id="email-body" rows="4"
                                    placeholder="Enter your test message...">This is a test email from the Selling  Poultry Farm notification system. If you receive this email, the email configuration is working correctly!</textarea>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="sendTestEmail()">
                                <i class="fas fa-paper-plane"></i> Send Test Email
                            </button>
                        </form>
                        <div class="mt-3">
                            <div class="result-box" id="email-result">
                                <small class="text-muted">Email test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Testing -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sms"></i> SMS Testing</h5>
                    </div>
                    <div class="card-body">
                        <form id="sms-test-form">
                            <div class="mb-3">
                                <label for="test-phone" class="form-label">Test Phone Number</label>
                                <input type="tel" class="form-control" id="test-phone" placeholder="+254700000000"
                                    required>
                                <small class="form-text text-muted">Include country code (e.g., +254 for Kenya)</small>
                            </div>
                            <div class="mb-3">
                                <label for="sms-message" class="form-label">Message</label>
                                <textarea class="form-control" id="sms-message" rows="4"
                                    placeholder="Enter your test message...">Test SMS from Selling  Poultry Farm notification system. If you receive this SMS, the SMS configuration is working correctly!</textarea>
                            </div>
                            <button type="button" class="btn btn-success" onclick="sendTestSMS()">
                                <i class="fas fa-sms"></i> Send Test SMS
                            </button>
                        </form>
                        <div class="mt-3">
                            <div class="result-box" id="sms-result">
                                <small class="text-muted">SMS test results will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Templates Testing -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Email Templates Testing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="template-email" class="form-label">Test Email Address</label>
                                    <input type="email" class="form-control" id="template-email"
                                        placeholder="test@example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="template-select" class="form-label">Select Template</label>
                                    <select class="form-control" id="template-select" onchange="loadTemplatePreview()">
                                        <option value="">Select a template...</option>
                                        <option value="welcome">Welcome Email</option>
                                        <option value="payment_confirmation">Payment Confirmation</option>
                                        <option value="order_status_update">Order Status Update</option>
                                        <option value="admin_payment_notification">Admin Payment Notification</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Template Variables</label>
                                    <div id="template-variables">
                                        <small class="text-muted">Select a template to see variables</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="sendTemplateEmail()">
                                    <i class="fas fa-paper-plane"></i> Send Template Email
                                </button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Template Preview</label>
                                <div class="template-preview" id="template-preview">
                                    <small class="text-muted">Select a template to see preview</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="result-box" id="template-result">
                                <small class="text-muted">Template email test results will appear here...</small>
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
                                <h6><i class="fas fa-envelope"></i> Email Setup</h6>
                                <ol>
                                    <li>Copy <code>env.example</code> to <code>.env</code></li>
                                    <li>Update email configuration in <code>.env</code>:
                                        <ul>
                                            <li><code>MAIL_HOST</code> - SMTP server (e.g., smtp.gmail.com)</li>
                                            <li><code>MAIL_USERNAME</code> - Your email address</li>
                                            <li><code>MAIL_PASSWORD</code> - Your email password or app password</li>
                                            <li><code>MAIL_FROM_EMAIL</code> - From email address</li>
                                        </ul>
                                    </li>
                                    <li>Run <code>composer install</code> to install PHPMailer</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-sms"></i> SMS Setup</h6>
                                <ol>
                                    <li>Get Africa's Talking account and API credentials</li>
                                    <li>Update SMS configuration in <code>.env</code>:
                                        <ul>
                                            <li><code>SMS_USERNAME</code> - Your Africa's Talking username</li>
                                            <li><code>SMS_API_KEY</code> - Your API key</li>
                                            <li><code>SMS_SENDER_ID</code> - Your sender ID</li>
                                            <li><code>SMS_ENVIRONMENT</code> - sandbox or live</li>
                                        </ul>
                                    </li>
                                    <li>Test with sandbox first before going live</li>
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
        // Email templates data
        const templates = <?php echo json_encode($templates); ?>;

        function showLoading(elementId) {
            document.getElementById(elementId).innerHTML =
                '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        }

        function showResult(elementId, result) {
            const element = document.getElementById(elementId);
            const isSuccess = result.success;
            const statusClass = isSuccess ? 'text-success' : 'text-danger';
            const statusIcon = isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';

            let html = `<div class="${statusClass}"><i class="fas ${statusIcon}"></i> ${result.message}</div>`;

            if (result.data) {
                html += '<div class="mt-2"><small class="text-muted">Details:</small><pre class="mt-1">' + JSON.stringify(
                    result.data, null, 2) + '</pre></div>';
            }

            if (result.error) {
                html += '<div class="mt-2"><small class="text-danger">Error:</small><pre class="mt-1">' + result.error +
                    '</pre></div>';
            }

            element.innerHTML = html;
        }

        function testEmailConfig() {
            showLoading('email-config-status');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_email_config'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Response is not valid JSON:', text);
                            throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(result => {
                    const element = document.getElementById('email-config-status');
                    const isSuccess = result.success;
                    const statusClass = isSuccess ? 'status-success' : 'status-error';
                    const statusText = isSuccess ? 'Email config is valid' : 'Email config has issues';

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
                    console.error('Email config test error:', error);
                    document.getElementById('email-config-status').innerHTML =
                        '<span class="status-indicator status-error"></span><span>Error testing email config: ' + error
                            .message + '</span>';
                });
        }

        function testSMSConfig() {
            showLoading('sms-config-status');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_sms_config'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Response is not valid JSON:', text);
                            throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(result => {
                    const element = document.getElementById('sms-config-status');
                    const isSuccess = result.success;
                    const statusClass = isSuccess ? 'status-success' : 'status-error';
                    const statusText = isSuccess ? 'SMS config is valid' : 'SMS config has issues';

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
                    console.error('SMS config test error:', error);
                    document.getElementById('sms-config-status').innerHTML =
                        '<span class="status-indicator status-error"></span><span>Error testing SMS config: ' + error
                            .message + '</span>';
                });
        }

        function sendTestEmail() {
            const email = document.getElementById('test-email').value;
            const subject = document.getElementById('email-subject').value;
            const body = document.getElementById('email-body').value;

            if (!email) {
                alert('Please enter an email address');
                return;
            }

            showLoading('email-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_test_email&email=${encodeURIComponent(email)}&subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
            })
                .then(response => response.json())
                .then(result => showResult('email-result', result))
                .catch(error => showResult('email-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        function sendTestSMS() {
            const phone = document.getElementById('test-phone').value;
            const message = document.getElementById('sms-message').value;

            if (!phone) {
                alert('Please enter a phone number');
                return;
            }

            showLoading('sms-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_test_sms&phone=${encodeURIComponent(phone)}&message=${encodeURIComponent(message)}`
            })
                .then(response => response.json())
                .then(result => showResult('sms-result', result))
                .catch(error => showResult('sms-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }

        function loadTemplatePreview() {
            const templateSelect = document.getElementById('template-select');
            const selectedTemplate = templateSelect.value;
            const previewElement = document.getElementById('template-preview');
            const variablesElement = document.getElementById('template-variables');

            if (!selectedTemplate || !templates[selectedTemplate]) {
                previewElement.innerHTML = '<small class="text-muted">Select a template to see preview</small>';
                variablesElement.innerHTML = '<small class="text-muted">Select a template to see variables</small>';
                return;
            }

            const template = templates[selectedTemplate];
            previewElement.innerHTML = template.body;

            // Extract variables from template
            const variables = template.body.match(/\{([^}]+)\}/g) || [];
            const uniqueVariables = [...new Set(variables.map(v => v.slice(1, -1)))];

            let variablesHtml = '';
            uniqueVariables.forEach(variable => {
                variablesHtml += `
                    <div class="mb-2">
                        <label class="form-label form-label-sm">${variable}</label>
                        <input type="text" class="form-control form-control-sm" id="var-${variable}" placeholder="Enter ${variable}">
                    </div>
                `;
            });

            variablesElement.innerHTML = variablesHtml || '<small class="text-muted">No variables in this template</small>';
        }

        function sendTemplateEmail() {
            const email = document.getElementById('template-email').value;
            const template = document.getElementById('template-select').value;

            if (!email) {
                alert('Please enter an email address');
                return;
            }

            if (!template) {
                alert('Please select a template');
                return;
            }

            // Collect variables
            const variables = {};
            const variableInputs = document.querySelectorAll('#template-variables input');
            variableInputs.forEach(input => {
                const varName = input.id.replace('var-', '');
                variables[varName] = input.value || `[${varName}]`;
            });

            showLoading('template-result');

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_template_email&email=${encodeURIComponent(email)}&template=${encodeURIComponent(template)}&variables=${encodeURIComponent(JSON.stringify(variables))}`
            })
                .then(response => response.json())
                .then(result => showResult('template-result', result))
                .catch(error => showResult('template-result', {
                    success: false,
                    message: 'Network error: ' + error.message
                }));
        }
    </script>
</body>

</html>