# Smart Poultry Farm - Notification System Setup

This document provides step-by-step instructions for setting up the email and SMS notification system.

## Prerequisites

- PHP 7.4 or higher
- Composer installed
- XAMPP or similar local server environment
- Email account (Gmail, Outlook, etc.)
- Africa's Talking account for SMS

## Installation Steps

### 1. Install Dependencies

The required dependencies have already been installed via Composer:
- PHPMailer (for email sending)
- vlucas/phpdotenv (for environment variables)

### 2. Environment Configuration

1. Copy the example environment file:
   ```bash
   cp env.example .env
   ```

2. Edit the `.env` file with your credentials:

#### Email Configuration
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@smartpoultry.com
MAIL_FROM_NAME=Smart Poultry Farm
MAIL_ENCRYPTION=tls
```

**For Gmail:**
- Enable 2-factor authentication
- Generate an "App Password" for your application
- Use the app password in `MAIL_PASSWORD`

#### SMS Configuration
```env
SMS_USERNAME=your-africas-talking-username
SMS_API_KEY=your-africas-talking-api-key
SMS_SENDER_ID=SMARTPOULTRY
SMS_ENVIRONMENT=sandbox
```

**For Africa's Talking:**
- Sign up at [Africa's Talking](https://africastalking.com)
- Get your username and API key from the dashboard
- Start with sandbox environment for testing

### 3. Testing the System

1. Visit the test page: `http://localhost/smart-poultry-farm/test_notifications.php`

2. Test the configuration:
   - Click "Test Email Config" to verify email settings
   - Click "Test SMS Config" to verify SMS settings

3. Send test messages:
   - Send a test email to your email address
   - Send a test SMS to your phone number
   - Test email templates with sample data

### 4. Integration Points

The notification system is ready to be integrated into:

#### User Registration
- Welcome email sent to new users
- Location: `classes/Master.php` (register function)

#### Payment Confirmation
- Email + SMS to user confirming payment
- Email + SMS to admin notifying of payment
- Location: `classes/Master.php` (place_order function)

#### Order Status Updates
- Email + SMS to user for status changes
- Location: `classes/Master.php` (update_order_status function)

## File Structure

```
smart-poultry-farm/
├── classes/
│   └── NotificationService.php    # Main notification class
├── vendor/                        # Composer dependencies
├── .env                          # Environment configuration
├── env.example                   # Environment template
├── test_notifications.php        # Test interface
├── install_notifications.php     # Installation helper
├── composer.json                 # Composer configuration
└── NOTIFICATION_SETUP.md         # This file
```

## Email Templates

The system includes pre-built templates for:

1. **Welcome Email** - Sent to new users
2. **Payment Confirmation** - Sent to users after payment
3. **Order Status Update** - Sent when order status changes
4. **Admin Payment Notification** - Sent to admin for new payments

## SMS Templates

SMS messages are shorter and more direct:
- Payment confirmations
- Order status updates
- Welcome messages

## Troubleshooting

### Email Issues
- Check SMTP settings in `.env`
- Verify email credentials
- Check firewall/antivirus blocking SMTP
- For Gmail, ensure app password is used

### SMS Issues
- Verify Africa's Talking credentials
- Check phone number format (include country code)
- Ensure sufficient SMS credits
- Test with sandbox first

### Common Errors
- "Connection refused" - Check SMTP host/port
- "Authentication failed" - Check username/password
- "Invalid phone number" - Check number format
- "Insufficient balance" - Add SMS credits

## Security Notes

- Never commit `.env` file to version control
- Use app passwords for email accounts
- Test with sandbox before going live
- Monitor SMS costs and usage

## Support

For issues with:
- **Email**: Check PHPMailer documentation
- **SMS**: Check Africa's Talking documentation
- **System**: Check PHP error logs

## Next Steps

After successful testing:
1. Integrate notifications into user registration
2. Add payment confirmation notifications
3. Implement order status update notifications
4. Monitor and optimize notification delivery
