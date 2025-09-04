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
    private $logFile;

    public function __construct()
    {
        try {
            parent::__construct();
            $this->loadConfig();
            $this->initializeMailer();
            $this->logFile = __DIR__ . '/../logs/notifications.log';
            $this->ensureLogDirectory();
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
                'phone' => $_ENV['ADMIN_PHONE'] ?? '+254700000000',
                'name' => $_ENV['ADMIN_NAME'] ?? 'Smart Poultry Admin',
                'notifications_enabled' => filter_var($_ENV['ADMIN_NOTIFICATIONS_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'email_notifications' => filter_var($_ENV['ADMIN_EMAIL_NOTIFICATIONS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'sms_notifications' => filter_var($_ENV['ADMIN_SMS_NOTIFICATIONS'] ?? 'true', FILTER_VALIDATE_BOOLEAN)
            ],
            'notifications' => [
                'notify_admin_on_new_orders' => filter_var($_ENV['NOTIFY_ADMIN_ON_NEW_ORDERS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'notify_admin_on_payments' => filter_var($_ENV['NOTIFY_ADMIN_ON_PAYMENTS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'notify_admin_on_order_status_changes' => filter_var($_ENV['NOTIFY_ADMIN_ON_ORDER_STATUS_CHANGES'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'notify_admin_on_user_registrations' => filter_var($_ENV['NOTIFY_ADMIN_ON_USER_REGISTRATIONS'] ?? 'false', FILTER_VALIDATE_BOOLEAN)
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
        $details = [
            'to' => is_array($to) ? implode(', ', array_keys($to)) : $to,
            'subject' => $subject,
            'body_length' => strlen($body),
            'is_html' => $isHTML,
            'attachments_count' => count($attachments)
        ];

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

            if ($result) {
                $this->logSuccess('email', $details);
                return [
                    'success' => true,
                    'message' => 'Email sent successfully',
                    'data' => ['to' => $to, 'subject' => $subject]
                ];
            } else {
                $error = 'Failed to send email: ' . $this->mail->ErrorInfo;
                $this->logFailure('email', $details, $error);
                return [
                    'success' => false,
                    'message' => $error,
                    'error' => $this->mail->ErrorInfo
                ];
            }

        } catch (Exception $e) {
            $error = 'Email sending failed: ' . $e->getMessage();
            $this->logFailure('email', $details, $error);
            return [
                'success' => false,
                'message' => $error,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Africa's Talking
     */
    public function sendSMS($phoneNumbers, $message)
    {
        // Convert single phone number to array for logging
        $originalNumbers = is_array($phoneNumbers) ? $phoneNumbers : [$phoneNumbers];

        $details = [
            'phone_numbers' => $originalNumbers,
            'message_length' => strlen($message),
            'recipient_count' => count($originalNumbers)
        ];

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

                $success = $successCount > 0;
                $logDetails = array_merge($details, [
                    'total_recipients' => count($formattedNumbers),
                    'successful' => $successCount,
                    'failed' => $failedCount
                ]);

                if ($success) {
                    $this->logSuccess('sms', $logDetails);
                } else {
                    $this->logFailure('sms', $logDetails, "All SMS messages failed");
                }

                return [
                    'success' => $success,
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
            $error = 'SMS sending failed: ' . $e->getMessage();
            $this->logFailure('sms', $details, $error);
            return [
                'success' => false,
                'message' => $error,
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
            ],
            'admin_new_order_notification' => [
                'subject' => 'New Order Placed - Order #{order_id}',
                'body' => $this->getAdminNewOrderNotificationTemplate()
            ],
            'admin_user_registration_notification' => [
                'subject' => 'New User Registration - {firstname} {lastname}',
                'body' => $this->getAdminUserRegistrationNotificationTemplate()
            ]
        ];
    }

    /**
     * Get SMS Templates
     */
    public function getSMSTemplates()
    {
        return [
            'welcome' => 'Welcome to {app_name}! Your account has been created successfully. Thank you for choosing us!',
            'payment_confirmation' => 'Payment confirmed! Order #{order_id} - {currency} {amount} via {payment_method}. Thank you!',
            'order_status_update' => 'Order #{order_id} status updated to: {new_status}. {status_message}',
            'admin_payment_notification' => 'New payment: Order #{order_id} - {currency} {amount} from {customer_name}',
            'admin_new_order_notification' => 'New order: #{order_id} - {currency} {total_amount} from {customer_name}',
            'admin_user_registration_notification' => 'New user registered: {firstname} {lastname} ({email})'
        ];
    }

    private function getWelcomeEmailTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to ' . $this->config['app']['name'] . '</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        🐔 Welcome to ' . $this->config['app']['name'] . '
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #e8f4fd; font-size: 16px; opacity: 0.9;">
                                        Your Fresh Poultry Journey Starts Here
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                                            <span style="font-size: 32px;">✅</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            Account Created Successfully!
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            Dear {firstname} {lastname}, we\'re excited to have you on board!
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #667eea;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            📋 Your Account Details
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">📧 Email:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{email}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📱 Phone:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{contact}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Joined:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{registration_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e8f5e8; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #27ae60;">
                                        <h3 style="margin: 0 0 10px 0; color: #27ae60; font-size: 16px; font-weight: 600;">
                                            🎉 What\'s Next?
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            You can now start shopping for fresh, high-quality poultry products and enjoy our premium services. 
                                            Browse our catalog, place orders, and experience the best in poultry farming!
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;">
                                            🛒 Start Shopping Now
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        Need help? Contact us anytime!
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">The ' . $this->config['app']['name'] . ' Team</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
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
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Payment Confirmation - Order #{order_id}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        💳 Payment Confirmed!
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #e8f8f5; font-size: 16px; opacity: 0.9;">
                                        Your order is being processed
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);">
                                            <span style="font-size: 32px;">✅</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            Payment Received Successfully!
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            Dear {firstname} {lastname}, thank you for your purchase!
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #27ae60;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            💰 Payment Details
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">🆔 Order ID:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-family: monospace; font-weight: 600;">#{order_id}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">💵 Amount:</td>
                                                <td style="padding: 8px 0; color: #27ae60; font-size: 18px; font-weight: 700;">{amount}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">💳 Method:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{payment_method}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Date:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{payment_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e3f2fd; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #2196f3;">
                                        <h3 style="margin: 0 0 10px 0; color: #1976d2; font-size: 16px; font-weight: 600;">
                                            📦 What Happens Next?
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            We\'re now processing your order and will keep you updated on its status. 
                                            You\'ll receive notifications when your order is packed, shipped, and delivered.
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '/?p=my_account" style="display: inline-block; background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s ease;">
                                            📋 Track Your Order
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        Questions about your order? We\'re here to help!
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">The ' . $this->config['app']['name'] . ' Team</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
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
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Order Status Update - Order #{order_id}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        📦 Order Status Update
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #e8f4fd; font-size: 16px; opacity: 0.9;">
                                        Your order is on the move!
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);">
                                            <span style="font-size: 32px;">📋</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            Status Updated!
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            Dear {firstname} {lastname}, your order status has been updated
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #3498db;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            📋 Order Details
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">🆔 Order ID:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-family: monospace; font-weight: 600;">#{order_id}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📊 Status:</td>
                                                <td style="padding: 8px 0; color: #3498db; font-size: 18px; font-weight: 700;">{status}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Updated:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{update_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e8f4fd; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #2196f3;">
                                        <h3 style="margin: 0 0 10px 0; color: #1976d2; font-size: 16px; font-weight: 600;">
                                            ℹ️ Status Information
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            {status_message}
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '/?p=my_account" style="display: inline-block; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3); transition: all 0.3s ease;">
                                            📋 View Order Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        Questions about your order? We\'re here to help!
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">The ' . $this->config['app']['name'] . ' Team</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
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
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Payment Received - Order #{order_id}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        🚨 PAYMENT ALERT
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #fadbd8; font-size: 16px; opacity: 0.9;">
                                        New payment received - Review required
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);">
                                            <span style="font-size: 32px;">🔔</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            Payment Notification
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            A customer has made a payment for their order
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #e74c3c;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            📊 Payment Information
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">🆔 Order ID:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-family: monospace; font-weight: 600;">#{order_id}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">👤 Customer:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">{customer_name}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">💵 Amount Paid:</td>
                                                <td style="padding: 8px 0; color: #e74c3c; font-size: 18px; font-weight: 700;">{amount}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">💳 Payment Method:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{payment_method}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Received At:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{payment_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e8f5e8; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #27ae60;">
                                        <h3 style="margin: 0 0 10px 0; color: #27ae60; font-size: 16px; font-weight: 600;">
                                            ✅ Payment Status
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            Payment has been successfully processed and confirmed. 
                                            Order is now ready for fulfillment.
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '/admin/orders/" style="display: inline-block; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); transition: all 0.3s ease;">
                                            📋 View Order Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        This is an automated notification from the system
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">' . $this->config['app']['name'] . ' System</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }

    private function getAdminNewOrderNotificationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Order Placed - Order #{order_id}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        🚨 ORDER ALERT
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #fef9e7; font-size: 16px; opacity: 0.9;">
                                        New order received - Review required
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);">
                                            <span style="font-size: 32px;">🛒</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            Order Notification
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            A customer has placed a new order
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #f39c12;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            📊 Order Information
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">🆔 Order ID:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-family: monospace; font-weight: 600;">#{order_id}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">👤 Customer:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">{customer_name}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">💵 Order Value:</td>
                                                <td style="padding: 8px 0; color: #f39c12; font-size: 18px; font-weight: 700;">{total_amount}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📦 Items:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{item_count} items</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Placed At:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{order_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e8f5e8; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #27ae60;">
                                        <h3 style="margin: 0 0 10px 0; color: #27ae60; font-size: 16px; font-weight: 600;">
                                            ℹ️ Order Status
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            New order is pending processing. Review order details and update status as needed.
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '/admin/orders/" style="display: inline-block; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); transition: all 0.3s ease;">
                                            📋 View Order Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        This is an automated notification from the system
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">' . $this->config['app']['name'] . ' System</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }

    private function getAdminUserRegistrationNotificationTemplate()
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New User Registration - {firstname} {lastname}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); overflow: hidden;">
                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                        🚨 USER ALERT
                                    </h1>
                                    <p style="margin: 10px 0 0 0; color: #f4ecf7; font-size: 16px; opacity: 0.9;">
                                        New user registered - Review required
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <div style="text-align: center; margin-bottom: 30px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3);">
                                            <span style="font-size: 32px;">👤</span>
                                        </div>
                                        <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 600;">
                                            User Registration Notification
                                        </h2>
                                        <p style="margin: 0; color: #7f8c8d; font-size: 16px;">
                                            A new user has registered on the platform
                                        </p>
                                    </div>
                                    
                                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #9b59b6;">
                                        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px; font-weight: 600;">
                                            📊 User Information
                                        </h3>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600; width: 40%;">👤 Name:</td>
                                                <td style="padding: 8px 0; color: #2c3e50; font-weight: 600;">{firstname} {lastname}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📧 Email:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{email}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📱 Phone:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{contact}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #34495e; font-weight: 600;">📅 Registered At:</td>
                                                <td style="padding: 8px 0; color: #2c3e50;">{registration_date}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <div style="background: #e8f5e8; border-radius: 8px; padding: 20px; margin: 25px 0; border-left: 4px solid #27ae60;">
                                        <h3 style="margin: 0 0 10px 0; color: #27ae60; font-size: 16px; font-weight: 600;">
                                            ✅ Registration Status
                                        </h3>
                                        <p style="margin: 0; color: #2c3e50; font-size: 14px;">
                                            User account has been successfully created and is now active. 
                                            They can place orders and access all platform features.
                                        </p>
                                    </div>
                                    
                                    <div style="text-align: center; margin: 30px 0;">
                                        <a href="' . $this->config['app']['url'] . '/admin/user/" style="display: inline-block; background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(155, 89, 182, 0.3); transition: all 0.3s ease;">
                                            👥 View User Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                                    <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 14px;">
                                        This is an automated notification from the system
                                    </p>
                                    <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                        Best regards,<br>
                                        <strong style="color: #2c3e50;">' . $this->config['app']['name'] . ' System</strong>
                                    </p>
                                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                                        <p style="margin: 0; color: #adb5bd; font-size: 12px;">
                                            © ' . date('Y') . ' ' . $this->config['app']['name'] . '. All rights reserved.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }

    /**
     * Get configuration array
     */
    public function getConfig()
    {
        return $this->config;
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

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory()
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log notification activity
     */
    private function logNotification($type, $status, $details, $error = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => $type,
            'status' => $status,
            'details' => $details,
            'error' => $error
        ];

        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log successful notification
     */
    private function logSuccess($type, $details)
    {
        $this->logNotification($type, 'success', $details);
    }

    /**
     * Log failed notification
     */
    private function logFailure($type, $details, $error)
    {
        $this->logNotification($type, 'failure', $details, $error);
    }

    /**
     * Get notification logs
     */
    public function getNotificationLogs($limit = 100, $type = null)
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $logs = [];
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return [];
        }

        // Get last N lines
        $lines = array_slice($lines, -$limit);

        foreach ($lines as $line) {
            $logEntry = json_decode($line, true);
            if ($logEntry && (!$type || $logEntry['type'] === $type)) {
                $logs[] = $logEntry;
            }
        }

        return array_reverse($logs); // Most recent first
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats($days = 7)
    {
        $logs = $this->getNotificationLogs(1000);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $stats = [
            'total' => 0,
            'successful' => 0,
            'failed' => 0,
            'by_type' => [],
            'by_status' => []
        ];

        foreach ($logs as $log) {
            if ($log['timestamp'] < $cutoff) {
                continue;
            }

            $stats['total']++;

            if ($log['status'] === 'success') {
                $stats['successful']++;
            } else {
                $stats['failed']++;
            }

            // Count by type
            if (!isset($stats['by_type'][$log['type']])) {
                $stats['by_type'][$log['type']] = 0;
            }
            $stats['by_type'][$log['type']]++;

            // Count by status
            if (!isset($stats['by_status'][$log['status']])) {
                $stats['by_status'][$log['status']] = 0;
            }
            $stats['by_status'][$log['status']]++;
        }

        return $stats;
    }
}
?>