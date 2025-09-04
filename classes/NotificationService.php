<?php
// Only load if not already loaded
if (!defined('NOTIFICATION_SERVICE_LOADED')) {
    define('NOTIFICATION_SERVICE_LOADED', true);

    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once(__DIR__ . '/../vendor/autoload.php');
    }

    if (!defined('DB_SERVER') && file_exists(__DIR__ . '/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }

    if (!class_exists('DBConnection') && file_exists(__DIR__ . '/DBConnection.php')) {
        require_once(__DIR__ . '/DBConnection.php');
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class NotificationService extends DBConnection
{
    private $mail;
    private $config;

    public function __construct()
    {
        try {
            parent::__construct();
            $this->loadConfig();
            $this->initializeMailer();
        } catch (Exception $e) {
            error_log("NotificationService constructor error: " . $e->getMessage());
            throw new Exception("Failed to initialize NotificationService: " . $e->getMessage());
        }
    }

    private function loadConfig()
    {
        // Load environment variables
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                throw new Exception("Failed to read .env file");
            }
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        } else {
            throw new Exception(".env file not found at: " . $envFile);
        }

        $this->config = [
            'mail' => [
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
                'port' => $_ENV['MAIL_PORT'] ?? 587,
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'from_email' => $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@smartpoultry.com',
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Smart Poultry Farm',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls'
            ],
            'sms' => [
                'username' => $_ENV['SMS_USERNAME'] ?? '',
                'api_key' => $_ENV['SMS_API_KEY'] ?? '',
                'sender_id' => $_ENV['SMS_SENDER_ID'] ?? 'SMARTPOULTRY',
                'environment' => $_ENV['SMS_ENVIRONMENT'] ?? 'sandbox'
            ],
            'admin' => [
                'email' => $_ENV['ADMIN_EMAIL'] ?? 'admin@smartpoultry.com',
                'phone' => $_ENV['ADMIN_PHONE'] ?? '+254700000000'
            ],
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Smart Poultry Farm',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost/smart-poultry-farm'
            ]
        ];
    }

    private function initializeMailer()
    {
        try {
            $this->mail = new PHPMailer(true);

            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['mail']['host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['mail']['username'];
            $this->mail->Password = $this->config['mail']['password'];
            $this->mail->SMTPSecure = $this->config['mail']['encryption'];
            $this->mail->Port = $this->config['mail']['port'];

            // Recipients
            $this->mail->setFrom($this->config['mail']['from_email'], $this->config['mail']['from_name']);
            $this->mail->addReplyTo($this->config['mail']['from_email'], $this->config['mail']['from_name']);

            // Content
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';

        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize PHPMailer: " . $e->getMessage());
        }
    }

    /**
     * Send Email
     */
    public function sendEmail($to, $subject, $body, $isHTML = true, $attachments = [])
    {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            // Add recipient
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    $this->mail->addAddress($email, $name);
                }
            } else {
                $this->mail->addAddress($to);
            }

            // Add attachments
            foreach ($attachments as $attachment) {
                $this->mail->addAttachment($attachment);
            }

            // Content
            $this->mail->Subject = $subject;
            if ($isHTML) {
                $this->mail->Body = $body;
                $this->mail->AltBody = strip_tags($body);
            } else {
                $this->mail->Body = $body;
                $this->mail->isHTML(false);
            }

            $result = $this->mail->send();

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'data' => ['to' => $to, 'subject' => $subject]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email sending failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Africa's Talking
     */
    public function sendSMS($phoneNumbers, $message)
    {
        try {
            $username = $this->config['sms']['username'];
            $apiKey = $this->config['sms']['api_key'];
            $senderId = $this->config['sms']['sender_id'];

            if (empty($username) || empty($apiKey)) {
                throw new Exception('SMS configuration is incomplete');
            }

            // Convert single phone number to array
            if (!is_array($phoneNumbers)) {
                $phoneNumbers = [$phoneNumbers];
            }

            // Prepare phone numbers (ensure they start with +)
            $formattedNumbers = [];
            foreach ($phoneNumbers as $number) {
                $number = trim($number);
                if (substr($number, 0, 1) !== '+') {
                    $number = '+' . $number;
                }
                $formattedNumbers[] = $number;
            }

            // Determine API endpoint
            // Note: Africa's Talking sandbox might not be available, so we'll use live endpoint for testing
            $endpoint = 'https://api.africastalking.com/version1/messaging/bulk';

            // Prepare request data
            // Note: Remove senderId if it's not approved, Africa's Talking will use default
            $data = [
                'username' => $username,
                'message' => $message,
                'phoneNumbers' => $formattedNumbers
            ];

            // Only add senderId if it's approved (you can test with different sender IDs)
            // $data['senderId'] = $senderId;

            // Make API request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'apiKey: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception('cURL Error: ' . $error);
            }

            if ($httpCode !== 200 && $httpCode !== 201) {
                throw new Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
            }

            $result = json_decode($response, true);

            if (isset($result['SMSMessageData']['Recipients'])) {
                $successCount = 0;
                $failedCount = 0;
                $details = [];

                foreach ($result['SMSMessageData']['Recipients'] as $recipient) {
                    if ($recipient['statusCode'] >= 100 && $recipient['statusCode'] <= 102) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                    $details[] = [
                        'number' => $recipient['number'],
                        'status' => $recipient['status'],
                        'statusCode' => $recipient['statusCode'],
                        'cost' => $recipient['cost'] ?? 'N/A'
                    ];
                }

                return [
                    'success' => $successCount > 0,
                    'message' => "SMS sent to {$successCount} recipients, {$failedCount} failed",
                    'data' => [
                        'total' => count($formattedNumbers),
                        'success' => $successCount,
                        'failed' => $failedCount,
                        'details' => $details,
                        'message' => $result['SMSMessageData']['Message'] ?? 'N/A'
                    ]
                ];
            } else {
                throw new Exception('Invalid response format from SMS API');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test Email Configuration
     */
    public function testEmailConfig()
    {
        try {
            $this->mail->smtpConnect();
            $this->mail->smtpClose();

            return [
                'success' => true,
                'message' => 'Email configuration is valid',
                'config' => [
                    'host' => $this->config['mail']['host'],
                    'port' => $this->config['mail']['port'],
                    'encryption' => $this->config['mail']['encryption'],
                    'from_email' => $this->config['mail']['from_email']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email configuration test failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test SMS Configuration
     */
    public function testSMSConfig()
    {
        $username = $this->config['sms']['username'];
        $apiKey = $this->config['sms']['api_key'];
        $senderId = $this->config['sms']['sender_id'];

        if (empty($username) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'SMS configuration is incomplete',
                'config' => [
                    'username' => $username ? 'Set' : 'Missing',
                    'api_key' => $apiKey ? 'Set' : 'Missing',
                    'sender_id' => $senderId,
                    'environment' => $this->config['sms']['environment']
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'SMS configuration appears valid',
            'config' => [
                'username' => $username,
                'sender_id' => $senderId,
                'environment' => $this->config['sms']['environment']
            ]
        ];
    }

    /**
     * Get Email Templates
     */
    public function getEmailTemplates()
    {
        return [
            'welcome' => [
                'subject' => 'Welcome to ' . $this->config['app']['name'],
                'body' => $this->getWelcomeEmailTemplate()
            ],
            'payment_confirmation' => [
                'subject' => 'Payment Confirmation - Order #{order_id}',
                'body' => $this->getPaymentConfirmationTemplate()
            ],
            'order_status_update' => [
                'subject' => 'Order Status Update - Order #{order_id}',
                'body' => $this->getOrderStatusUpdateTemplate()
            ],
            'admin_payment_notification' => [
                'subject' => 'New Payment Received - Order #{order_id}',
                'body' => $this->getAdminPaymentNotificationTemplate()
            ]
        ];
    }

    private function getWelcomeEmailTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Welcome to ' . $this->config['app']['name'] . '</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">Welcome to ' . $this->config['app']['name'] . '!</h2>
                <p>Dear {firstname} {lastname},</p>
                <p>Thank you for registering with us! We are excited to have you as part of our community.</p>
                <p>Your account has been successfully created with the following details:</p>
                <ul>
                    <li><strong>Email:</strong> {email}</li>
                    <li><strong>Phone:</strong> {contact}</li>
                    <li><strong>Registration Date:</strong> {registration_date}</li>
                </ul>
                <p>You can now start shopping for fresh poultry products and enjoy our services.</p>
                <p>If you have any questions, please don\'t hesitate to contact us.</p>
                <p>Best regards,<br>The ' . $this->config['app']['name'] . ' Team</p>
            </div>
        </body>
        </html>';
    }

    private function getPaymentConfirmationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Payment Confirmation</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">Payment Confirmation</h2>
                <p>Dear {firstname} {lastname},</p>
                <p>We have successfully received your payment for Order #{order_id}.</p>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Payment Details:</h3>
                    <ul>
                        <li><strong>Order ID:</strong> #{order_id}</li>
                        <li><strong>Amount:</strong> {amount}</li>
                        <li><strong>Payment Method:</strong> {payment_method}</li>
                        <li><strong>Payment Date:</strong> {payment_date}</li>
                    </ul>
                </div>
                <p>Your order is now being processed. You will receive updates on your order status.</p>
                <p>Thank you for choosing ' . $this->config['app']['name'] . '!</p>
                <p>Best regards,<br>The ' . $this->config['app']['name'] . ' Team</p>
            </div>
        </body>
        </html>';
    }

    private function getOrderStatusUpdateTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Order Status Update</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">Order Status Update</h2>
                <p>Dear {firstname} {lastname},</p>
                <p>Your order status has been updated:</p>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Order Details:</h3>
                    <ul>
                        <li><strong>Order ID:</strong> #{order_id}</li>
                        <li><strong>New Status:</strong> <span style="color: #2c5aa0; font-weight: bold;">{status}</span></li>
                        <li><strong>Update Date:</strong> {update_date}</li>
                    </ul>
                </div>
                <p>{status_message}</p>
                <p>Thank you for choosing ' . $this->config['app']['name'] . '!</p>
                <p>Best regards,<br>The ' . $this->config['app']['name'] . ' Team</p>
            </div>
        </body>
        </html>';
    }

    private function getAdminPaymentNotificationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Payment Received</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #2c5aa0;">New Payment Received</h2>
                <p>Dear Admin,</p>
                <p>A new payment has been received for Order #{order_id}.</p>
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Payment Details:</h3>
                    <ul>
                        <li><strong>Order ID:</strong> #{order_id}</li>
                        <li><strong>Customer:</strong> {customer_name}</li>
                        <li><strong>Amount:</strong> {amount}</li>
                        <li><strong>Payment Method:</strong> {payment_method}</li>
                        <li><strong>Payment Date:</strong> {payment_date}</li>
                    </ul>
                </div>
                <p>Please process this order accordingly.</p>
                <p>Best regards,<br>' . $this->config['app']['name'] . ' System</p>
            </div>
        </body>
        </html>';
    }

    /**
     * Replace template variables
     */
    public function replaceTemplateVariables($template, $variables)
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
}
?>