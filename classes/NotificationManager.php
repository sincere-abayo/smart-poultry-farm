<?php
/**
 * Professional Notification Manager
 * Centralized service for sending emails and SMS notifications
 * 
 * @author Smart Poultry Farm System
 * @version 1.0
 */

// Prevent direct access
if (!defined('NOTIFICATION_MANAGER_LOADED')) {
    define('NOTIFICATION_MANAGER_LOADED', true);

    // Load dependencies only if not already loaded
    if (!defined('DB_SERVER')) {
        require_once(__DIR__ . '/../config.php');
    }

    if (!class_exists('NotificationService')) {
        require_once(__DIR__ . '/NotificationService.php');
    }
}

class NotificationManager
{
    private $notificationService;
    private $appName;
    private $appUrl;

    public function __construct()
    {
        try {
            $this->notificationService = new NotificationService();
            $this->appName = $_ENV['APP_NAME'] ?? 'Smart Poultry Farm';
            $this->appUrl = $_ENV['APP_URL'] ?? 'http://localhost/smart-poultry-farm';
        } catch (Exception $e) {
            error_log("NotificationManager initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize notification service");
        }
    }

    /**
     * Send welcome email to new user
     * 
     * @param array $userData User information
     * @return array Result with success status and message
     */
    public function sendWelcomeEmail($userData)
    {
        try {
            $requiredFields = ['firstname', 'lastname', 'email'];
            $this->validateRequiredFields($userData, $requiredFields);

            $templateData = [
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'email' => $userData['email'],
                'contact' => $userData['contact'] ?? '',
                'registration_date' => date('F j, Y \a\t g:i A'),
                'app_name' => $this->appName,
                'app_url' => $this->appUrl
            ];

            $templates = $this->notificationService->getEmailTemplates();
            $welcomeTemplate = $templates['welcome'];

            $subject = $this->notificationService->replaceTemplateVariables($welcomeTemplate['subject'], $templateData);
            $body = $this->notificationService->replaceTemplateVariables($welcomeTemplate['body'], $templateData);

            $result = $this->notificationService->sendEmail($userData['email'], $subject, $body);

            // Log result for monitoring
            if (!$result['success']) {
                error_log("Welcome email failed: " . $result['message']);
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send welcome email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send payment confirmation notifications
     * 
     * @param array $paymentData Payment information
     * @return array Result with success status and details
     */
    public function sendPaymentConfirmation($paymentData)
    {
        try {
            $requiredFields = ['user_email', 'user_phone', 'amount', 'order_id', 'payment_method'];
            $this->validateRequiredFields($paymentData, $requiredFields);

            $templateData = [
                'firstname' => $paymentData['user_firstname'] ?? 'Customer',
                'lastname' => $paymentData['user_lastname'] ?? '',
                'email' => $paymentData['user_email'],
                'phone' => $paymentData['user_phone'],
                'amount' => number_format($paymentData['amount'], 2),
                'currency' => $paymentData['currency'] ?? 'RWF',
                'order_id' => $paymentData['order_id'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => date('F j, Y \a\t g:i A'),
                'app_name' => $this->appName,
                'app_url' => $this->appUrl
            ];

            $results = [
                'user_email' => ['success' => false, 'message' => ''],
                'user_sms' => ['success' => false, 'message' => ''],
                'admin_email' => ['success' => false, 'message' => ''],
                'admin_sms' => ['success' => false, 'message' => '']
            ];

            // Send email to user
            $templates = $this->notificationService->getEmailTemplates();
            $paymentTemplate = $templates['payment_confirmation'];

            $subject = $this->notificationService->replaceTemplateVariables($paymentTemplate['subject'], $templateData);
            $body = $this->notificationService->replaceTemplateVariables($paymentTemplate['body'], $templateData);

            $results['user_email'] = $this->notificationService->sendEmail($paymentData['user_email'], $subject, $body);

            // Send SMS to user
            $smsMessage = "Payment confirmed! Order #{$paymentData['order_id']} - {$paymentData['currency']} {$templateData['amount']} via {$paymentData['payment_method']}. Thank you!";
            $results['user_sms'] = $this->notificationService->sendSMS($paymentData['user_phone'], $smsMessage);

            // Send notification to admin
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@smartpoultry.com';
            $adminPhone = $_ENV['ADMIN_PHONE'] ?? '+250784424423';

            $adminTemplateData = array_merge($templateData, [
                'admin_notification' => true,
                'customer_name' => $templateData['firstname'] . ' ' . $templateData['lastname']
            ]);

            $adminSubject = "New Payment Received - Order #{$paymentData['order_id']}";
            $adminBody = $this->notificationService->replaceTemplateVariables($paymentTemplate['body'], $adminTemplateData);

            $results['admin_email'] = $this->notificationService->sendEmail($adminEmail, $adminSubject, $adminBody);

            $adminSmsMessage = "New payment: Order #{$paymentData['order_id']} - {$paymentData['currency']} {$templateData['amount']} from {$adminTemplateData['customer_name']}";
            $results['admin_sms'] = $this->notificationService->sendSMS($adminPhone, $adminSmsMessage);

            return [
                'success' => true,
                'message' => 'Payment confirmation notifications sent',
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send payment confirmation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send order status update notifications
     * 
     * @param array $orderData Order information
     * @return array Result with success status and details
     */
    public function sendOrderStatusUpdate($orderData)
    {
        try {
            $requiredFields = ['user_email', 'user_phone', 'order_id', 'new_status'];
            $this->validateRequiredFields($orderData, $requiredFields);

            $templateData = [
                'firstname' => $orderData['user_firstname'] ?? 'Customer',
                'lastname' => $orderData['user_lastname'] ?? '',
                'email' => $orderData['user_email'],
                'phone' => $orderData['user_phone'],
                'order_id' => $orderData['order_id'],
                'new_status' => $orderData['new_status'],
                'status' => $orderData['new_status'],
                'status_message' => $this->getStatusMessage($orderData['new_status']),
                'update_date' => date('F j, Y \a\t g:i A'),
                'app_name' => $this->appName,
                'app_url' => $this->appUrl
            ];

            $results = [
                'user_email' => ['success' => false, 'message' => ''],
                'user_sms' => ['success' => false, 'message' => '']
            ];

            // Send email to user
            $templates = $this->notificationService->getEmailTemplates();
            $statusTemplate = $templates['order_status_update'];

            $subject = $this->notificationService->replaceTemplateVariables($statusTemplate['subject'], $templateData);
            $body = $this->notificationService->replaceTemplateVariables($statusTemplate['body'], $templateData);

            $results['user_email'] = $this->notificationService->sendEmail($orderData['user_email'], $subject, $body);

            // Send SMS to user using template
            $smsTemplates = $this->notificationService->getSMSTemplates();
            $smsTemplate = $smsTemplates['order_status_update'];
            $smsMessage = $this->notificationService->replaceTemplateVariables($smsTemplate, $templateData);
            $results['user_sms'] = $this->notificationService->sendSMS($orderData['user_phone'], $smsMessage);

            return [
                'success' => true,
                'message' => 'Order status update notifications sent',
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send order status update: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send custom email notification
     * 
     * @param string $to Email address
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $templateData Optional template variables
     * @return array Result with success status and message
     */
    public function sendCustomEmail($to, $subject, $body, $templateData = [])
    {
        try {
            if (!empty($templateData)) {
                $subject = $this->notificationService->replaceTemplateVariables($subject, $templateData);
                $body = $this->notificationService->replaceTemplateVariables($body, $templateData);
            }

            return $this->notificationService->sendEmail($to, $subject, $body);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send custom email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send custom SMS notification
     * 
     * @param string $to Phone number
     * @param string $message SMS message
     * @param array $templateData Optional template variables
     * @return array Result with success status and message
     */
    public function sendCustomSMS($to, $message, $templateData = [])
    {
        try {
            if (!empty($templateData)) {
                $message = $this->notificationService->replaceTemplateVariables($message, $templateData);
            }

            return $this->notificationService->sendSMS($to, $message);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send custom SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk notifications (email and SMS)
     * 
     * @param array $recipients Array of recipient data
     * @param string $type Notification type (welcome, payment, status, custom)
     * @param array $data Notification data
     * @return array Result with success status and details
     */
    public function sendBulkNotifications($recipients, $type, $data)
    {
        try {
            $results = [];

            foreach ($recipients as $recipient) {
                switch ($type) {
                    case 'welcome':
                        $results[] = $this->sendWelcomeEmail($recipient);
                        break;
                    case 'payment':
                        $results[] = $this->sendPaymentConfirmation(array_merge($data, $recipient));
                        break;
                    case 'status':
                        $results[] = $this->sendOrderStatusUpdate(array_merge($data, $recipient));
                        break;
                    default:
                        $results[] = [
                            'success' => false,
                            'message' => 'Unknown notification type: ' . $type
                        ];
                }
            }

            return [
                'success' => true,
                'message' => 'Bulk notifications sent',
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send bulk notifications: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate required fields in data array
     * 
     * @param array $data Data to validate
     * @param array $requiredFields Required field names
     * @throws Exception If required fields are missing
     */
    private function validateRequiredFields($data, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing or empty");
            }
        }
    }

    /**
     * Get notification service instance
     * 
     * @return NotificationService
     */
    public function getNotificationService()
    {
        return $this->notificationService;
    }

    /**
     * Test notification system
     * 
     * @return array Test results
     */
    public function testSystem()
    {
        try {
            $results = [
                'email_config' => $this->notificationService->testEmailConfig(),
                'sms_config' => $this->notificationService->testSMSConfig()
            ];

            return [
                'success' => true,
                'message' => 'Notification system test completed',
                'results' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Notification system test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send admin notification for new order
     * 
     * @param array $orderData Order data
     * @return array Result with success status and details
     */
    public function sendAdminNewOrderNotification($orderData)
    {
        try {
            // Check if admin notifications are enabled
            if (!$this->notificationService->getConfig()['admin']['notifications_enabled']) {
                return [
                    'success' => false,
                    'message' => 'Admin notifications are disabled'
                ];
            }

            $requiredFields = ['order_id', 'customer_name', 'total_amount', 'item_count', 'order_date'];
            $this->validateRequiredFields($orderData, $requiredFields);

            $templateData = [
                'order_id' => $orderData['order_id'],
                'customer_name' => $orderData['customer_name'],
                'total_amount' => $orderData['total_amount'],
                'item_count' => $orderData['item_count'],
                'order_date' => $orderData['order_date'],
                'app_name' => $this->appName,
                'app_url' => $this->appUrl
            ];

            $results = [
                'admin_email' => ['success' => false, 'message' => ''],
                'admin_sms' => ['success' => false, 'message' => '']
            ];

            $adminConfig = $this->notificationService->getConfig()['admin'];

            // Send email to admin
            if ($adminConfig['email_notifications']) {
                $templates = $this->notificationService->getEmailTemplates();
                $template = $templates['admin_new_order_notification'];

                $subject = $this->notificationService->replaceTemplateVariables($template['subject'], $templateData);
                $body = $this->notificationService->replaceTemplateVariables($template['body'], $templateData);

                $results['admin_email'] = $this->notificationService->sendEmail($adminConfig['email'], $subject, $body);
            }

            // Send SMS to admin
            if ($adminConfig['sms_notifications']) {
                $smsTemplates = $this->notificationService->getSMSTemplates();
                $smsTemplate = $smsTemplates['admin_new_order_notification'];
                $smsMessage = $this->notificationService->replaceTemplateVariables($smsTemplate, $templateData);

                $results['admin_sms'] = $this->notificationService->sendSMS($adminConfig['phone'], $smsMessage);
            }

            return [
                'success' => true,
                'message' => 'Admin new order notifications sent',
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send admin new order notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send admin notification for user registration
     * 
     * @param array $userData User data
     * @return array Result with success status and details
     */
    public function sendAdminUserRegistrationNotification($userData)
    {
        try {
            // Check if admin notifications are enabled
            if (!$this->notificationService->getConfig()['admin']['notifications_enabled']) {
                return [
                    'success' => false,
                    'message' => 'Admin notifications are disabled'
                ];
            }

            $requiredFields = ['firstname', 'lastname', 'email', 'contact', 'registration_date'];
            $this->validateRequiredFields($userData, $requiredFields);

            $templateData = [
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'email' => $userData['email'],
                'contact' => $userData['contact'],
                'registration_date' => $userData['registration_date'],
                'app_name' => $this->appName,
                'app_url' => $this->appUrl
            ];

            $results = [
                'admin_email' => ['success' => false, 'message' => ''],
                'admin_sms' => ['success' => false, 'message' => '']
            ];

            $adminConfig = $this->notificationService->getConfig()['admin'];

            // Send email to admin
            if ($adminConfig['email_notifications']) {
                $templates = $this->notificationService->getEmailTemplates();
                $template = $templates['admin_user_registration_notification'];

                $subject = $this->notificationService->replaceTemplateVariables($template['subject'], $templateData);
                $body = $this->notificationService->replaceTemplateVariables($template['body'], $templateData);

                $results['admin_email'] = $this->notificationService->sendEmail($adminConfig['email'], $subject, $body);
            }

            // Send SMS to admin
            if ($adminConfig['sms_notifications']) {
                $smsTemplates = $this->notificationService->getSMSTemplates();
                $smsTemplate = $smsTemplates['admin_user_registration_notification'];
                $smsMessage = $this->notificationService->replaceTemplateVariables($smsTemplate, $templateData);

                $results['admin_sms'] = $this->notificationService->sendSMS($adminConfig['phone'], $smsMessage);
            }

            return [
                'success' => true,
                'message' => 'Admin user registration notifications sent',
                'details' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send admin user registration notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get status-specific message for order updates
     * 
     * @param string $status Order status
     * @return string Status-specific message
     */
    private function getStatusMessage($status)
    {
        $statusMessages = [
            'Pending' => 'Your order is being processed and will be prepared soon.',
            'Packed' => 'Your order has been packed and is ready for delivery.',
            'Out for Delivery' => 'Your order is out for delivery and will arrive soon.',
            'Picked Up' => 'Your order has been picked up and is on its way.',
            'Delivered' => 'Your order has been delivered successfully. Thank you for your business!',
            'Cancelled' => 'Your order has been cancelled. If you have any questions, please contact us.'
        ];

        return $statusMessages[$status] ?? 'Your order status has been updated.';
    }
}
?>