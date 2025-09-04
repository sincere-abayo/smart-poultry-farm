<?php
/**
 * Notification System Installation Script
 * This script helps set up the notification system for Smart Poultry Farm
 */

echo "<h1>Smart Poultry Farm - Notification System Setup</h1>\n";
echo "<hr>\n";

// Check if composer is available
echo "<h2>Step 1: Checking Dependencies</h2>\n";

if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer dependencies are already installed.<br>\n";
} else {
    echo "❌ Composer dependencies not found.<br>\n";
    echo "Please run: <code>composer install</code> in your project directory.<br>\n";
    echo "<br>\n";
}

// Check if .env file exists
echo "<h2>Step 2: Environment Configuration</h2>\n";

if (file_exists('.env')) {
    echo "✅ .env file exists.<br>\n";
} else {
    echo "❌ .env file not found.<br>\n";
    if (file_exists('env.example')) {
        echo "📋 Please copy env.example to .env and update the configuration:<br>\n";
        echo "<code>cp env.example .env</code><br>\n";
    } else {
        echo "❌ env.example file not found.<br>\n";
    }
    echo "<br>\n";
}

// Check if NotificationService class exists
echo "<h2>Step 3: Notification Service</h2>\n";

if (file_exists('classes/NotificationService.php')) {
    echo "✅ NotificationService class exists.<br>\n";
} else {
    echo "❌ NotificationService class not found.<br>\n";
    echo "<br>\n";
}

// Check if test page exists
echo "<h2>Step 4: Test Page</h2>\n";

if (file_exists('test_notifications.php')) {
    echo "✅ Test page exists.<br>\n";
    echo "🔗 <a href='test_notifications.php' target='_blank'>Open Test Page</a><br>\n";
} else {
    echo "❌ Test page not found.<br>\n";
    echo "<br>\n";
}

// Display configuration instructions
echo "<h2>Step 5: Configuration Instructions</h2>\n";

echo "<h3>Email Configuration (.env file):</h3>\n";
echo "<pre>\n";
echo "# Email Configuration (PHPMailer)\n";
echo "MAIL_HOST=smtp.gmail.com\n";
echo "MAIL_PORT=587\n";
echo "MAIL_USERNAME=your-email@gmail.com\n";
echo "MAIL_PASSWORD=your-app-password\n";
echo "MAIL_FROM_EMAIL=noreply@smartpoultry.com\n";
echo "MAIL_FROM_NAME=Smart Poultry Farm\n";
echo "MAIL_ENCRYPTION=tls\n";
echo "</pre>\n";

echo "<h3>SMS Configuration (.env file):</h3>\n";
echo "<pre>\n";
echo "# SMS Configuration (Africa's Talking)\n";
echo "SMS_USERNAME=your-africas-talking-username\n";
echo "SMS_API_KEY=your-africas-talking-api-key\n";
echo "SMS_SENDER_ID=SMARTPOULTRY\n";
echo "SMS_ENVIRONMENT=sandbox\n";
echo "</pre>\n";

echo "<h2>Step 6: Testing</h2>\n";
echo "<ol>\n";
echo "<li>Update your .env file with your email and SMS credentials</li>\n";
echo "<li>Visit the <a href='test_notifications.php' target='_blank'>test page</a></li>\n";
echo "<li>Test email configuration first</li>\n";
echo "<li>Test SMS configuration</li>\n";
echo "<li>Send test emails and SMS</li>\n";
echo "<li>Test email templates</li>\n";
echo "</ol>\n";

echo "<h2>Step 7: Integration</h2>\n";
echo "<p>Once testing is complete, the notification system can be integrated into:</p>\n";
echo "<ul>\n";
echo "<li>User registration (welcome email)</li>\n";
echo "<li>Payment confirmation (email + SMS to user and admin)</li>\n";
echo "<li>Order status updates (email + SMS to user)</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><strong>Note:</strong> Make sure to test with sandbox environment first before going live with SMS.</p>\n";
?>