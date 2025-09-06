# NotificationManager Usage Examples

## Overview
The `NotificationManager` class provides a clean, professional API for sending notifications throughout the Selling  Poultry Farm system. It handles all the complexity of email and SMS sending, template processing, and error handling.

## Basic Usage

### 1. Include the NotificationManager
```php
require_once('classes/NotificationManager.php');
$notifications = new NotificationManager();
```

### 2. Send Welcome Email (User Registration)
```php
$userData = [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email' => 'john.doe@example.com',
    'contact' => '+250700000000'
];

$result = $notifications->sendWelcomeEmail($userData);

if ($result['success']) {
    echo "Welcome email sent successfully!";
} else {
    echo "Failed to send welcome email: " . $result['message'];
}
```

### 3. Send Payment Confirmation
```php
$paymentData = [
    'user_email' => 'customer@example.com',
    'user_phone' => '+250700000000',
    'user_firstname' => 'John',
    'user_lastname' => 'Doe',
    'amount' => 15000.00,
    'currency' => 'RWF',
    'order_id' => 'ORD-2024-001',
    'payment_method' => 'Mobile Money'
];

$result = $notifications->sendPaymentConfirmation($paymentData);

if ($result['success']) {
    echo "Payment confirmation sent to customer and admin!";
    // Check individual results
    $details = $result['details'];
    if ($details['user_email']['success']) {
        echo "Customer email sent";
    }
    if ($details['user_sms']['success']) {
        echo "Customer SMS sent";
    }
}
```

### 4. Send Order Status Update
```php
$orderData = [
    'user_email' => 'customer@example.com',
    'user_phone' => '+250700000000',
    'user_firstname' => 'John',
    'user_lastname' => 'Doe',
    'order_id' => 'ORD-2024-001',
    'new_status' => 'Out for Delivery'
];

$result = $notifications->sendOrderStatusUpdate($orderData);

if ($result['success']) {
    echo "Order status update sent to customer!";
}
```

### 5. Send Custom Email
```php
$result = $notifications->sendCustomEmail(
    'customer@example.com',
    'Special Offer - {{app_name}}',
    'Hello {{firstname}}, we have a special offer for you!',
    [
        'firstname' => 'John',
        'app_name' => 'Selling  Poultry Farm'
    ]
);
```

### 6. Send Custom SMS
```php
$result = $notifications->sendCustomSMS(
    '+250700000000',
    'Hello {{firstname}}, your order #{{order_id}} is ready!',
    [
        'firstname' => 'John',
        'order_id' => 'ORD-2024-001'
    ]
);
```

### 7. Send Bulk Notifications
```php
$recipients = [
    ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
    ['firstname' => 'Jane', 'lastname' => 'Smith', 'email' => 'jane@example.com']
];

$result = $notifications->sendBulkNotifications($recipients, 'welcome', []);
```

### 8. Test Notification System
```php
$result = $notifications->testSystem();

if ($result['success']) {
    echo "Notification system is working properly!";
    $testResults = $result['results'];
    if ($testResults['email_config']['success']) {
        echo "Email configuration is valid";
    }
    if ($testResults['sms_config']['success']) {
        echo "SMS configuration is valid";
    }
}
```

## Integration Examples

### In Master.php (Registration)
```php
function register() {
    // ... registration logic ...
    
    // Send welcome email
    try {
        $notifications = new NotificationManager();
        $userData = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'contact' => $contact
        ];
        $notifications->sendWelcomeEmail($userData);
    } catch (Exception $e) {
        error_log("Welcome email error: " . $e->getMessage());
    }
    
    // ... rest of function ...
}
```

### In Payment Handler
```php
function processPayment() {
    // ... payment processing logic ...
    
    // Send payment confirmation
    try {
        $notifications = new NotificationManager();
        $paymentData = [
            'user_email' => $user['email'],
            'user_phone' => $user['contact'],
            'user_firstname' => $user['firstname'],
            'user_lastname' => $user['lastname'],
            'amount' => $payment_amount,
            'currency' => 'RWF',
            'order_id' => $order_id,
            'payment_method' => $payment_method
        ];
        $notifications->sendPaymentConfirmation($paymentData);
    } catch (Exception $e) {
        error_log("Payment notification error: " . $e->getMessage());
    }
}
```

### In Order Status Update
```php
function updateOrderStatus() {
    // ... status update logic ...
    
    // Send status update notification
    try {
        $notifications = new NotificationManager();
        $orderData = [
            'user_email' => $order['email'],
            'user_phone' => $order['contact'],
            'user_firstname' => $order['firstname'],
            'user_lastname' => $order['lastname'],
            'order_id' => $order_id,
            'new_status' => $new_status
        ];
        $notifications->sendOrderStatusUpdate($orderData);
    } catch (Exception $e) {
        error_log("Order status notification error: " . $e->getMessage());
    }
}
```

## Error Handling

All methods return a consistent result format:
```php
[
    'success' => true/false,
    'message' => 'Description of result',
    'details' => [] // Optional additional details
]
```

## Template Variables

The following template variables are available in email templates:
- `{{firstname}}` - User's first name
- `{{lastname}}` - User's last name
- `{{email}}` - User's email
- `{{phone}}` - User's phone number
- `{{app_name}}` - Application name
- `{{app_url}}` - Application URL
- `{{registration_date}}` - Registration date
- `{{payment_date}}` - Payment date
- `{{order_id}}` - Order ID
- `{{amount}}` - Payment amount
- `{{currency}}` - Currency
- `{{payment_method}}` - Payment method
- `{{new_status}}` - New order status
- `{{status_message}}` - Status description
- `{{update_date}}` - Status update date

## Best Practices

1. **Always wrap in try-catch**: Notification failures shouldn't break main functionality
2. **Log results**: Use error_log() to track notification success/failure
3. **Validate data**: Ensure required fields are present before calling methods
4. **Test first**: Use testSystem() to verify configuration before sending real notifications
5. **Handle errors gracefully**: Check success status and handle failures appropriately

## Configuration

Make sure your `.env` file contains all required configuration:
```env
# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@smartpoultry.com
MAIL_FROM_NAME=Smart Poultry Farm
MAIL_ENCRYPTION=tls

# SMS Configuration
SMS_USERNAME=your-africas-talking-username
SMS_API_KEY=your-africas-talking-api-key
SMS_SENDER_ID=SMARTPOULTRY
SMS_ENVIRONMENT=sandbox

# Admin Contact
ADMIN_EMAIL=admin@smartpoultry.com
ADMIN_PHONE=+250784424423

# Application
APP_NAME=Smart Poultry Farm
APP_URL=http://localhost/smart-poultry-farm
```
