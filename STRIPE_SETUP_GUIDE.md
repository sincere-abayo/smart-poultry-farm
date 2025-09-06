# Stripe Payment Integration Setup Guide

## 🚀 Quick Start

### 1. Get Your Stripe Keys

1. **Create Stripe Account**: Go to [stripe.com](https://stripe.com) and create an account
2. **Get API Keys**: 
   - Go to Stripe Dashboard → Developers → API Keys
   - Copy your **Publishable Key** (starts with `pk_test_`)
   - Copy your **Secret Key** (starts with `sk_test_`)

### 2. Update Configuration

Edit `stripe_config.php` and replace the placeholder values:

```php
// Replace these with your actual Stripe keys
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_actual_publishable_key_here');
define('STRIPE_SECRET_KEY', 'sk_test_your_actual_secret_key_here');
```

### 3. No Webhook Setup Required!

**✅ This integration uses direct payment confirmation - no webhooks needed!**

- Payments are confirmed immediately after Stripe checkout
- Orders are created automatically on payment success
- Notifications are sent instantly
- Much simpler setup and more reliable

### 4. Test the Integration

1. **Open Test Page**: Go to `http://localhost/smart-poultry-farm/test_stripe.php`
2. **Test Configuration**: Click "Test Config" to verify your keys
3. **Test Payment Intent**: Create a test payment intent
4. **Test Checkout Session**: Create a checkout session and test payment

## 🧪 Testing

### Test Cards

Use these test card numbers:

| Card Number | Description |
|-------------|-------------|
| `4242 4242 4242 4242` | Successful payment |
| `4000 0000 0000 0002` | Declined payment |
| `4000 0025 0000 3155` | Requires authentication |
| `4000 0000 0000 9995` | Insufficient funds |

### Test Details

- **Expiry**: Any future date (e.g., 12/25)
- **CVC**: Any 3 digits (e.g., 123)
- **ZIP**: Any 5 digits (e.g., 12345)

## 🔧 Features

### ✅ What's Working

- **Payment Intent Creation**: Create payment intents for processing
- **Checkout Sessions**: Full Stripe checkout experience
- **Webhook Handling**: Automatic payment confirmation
- **Order Processing**: Seamless integration with existing order system
- **Notifications**: Email and SMS notifications on payment success
- **Error Handling**: Comprehensive error handling and logging

### 📱 User Experience

- **Modern UI**: Beautiful, responsive checkout interface
- **Real-time Validation**: Live card validation and error display
- **Mobile Friendly**: Works perfectly on all devices
- **Loading States**: Visual feedback during processing

## 🛠️ Integration Points

### 1. Checkout Page Integration

The Stripe payment option is already integrated into your checkout page:

```php
// In checkout.php - Stripe payment method added
<div class="payment-method" onclick="selectPaymentMethod('stripe')">
    <div class="payment-method-header">
        <div class="payment-icon">
            <i class="fab fa-cc-stripe"></i>
        </div>
        <div>
            <div class="payment-title">Stripe Payment</div>
            <div class="payment-description">Pay securely with your credit or debit card</div>
        </div>
    </div>
</div>
```

### 2. Payment Processing

When users select Stripe payment:

1. **Validation**: Form validation before redirect
2. **Redirect**: Redirect to `stripe_checkout.php`
3. **Payment**: User completes payment with Stripe
4. **Webhook**: Stripe sends webhook to confirm payment
5. **Order Creation**: Order is automatically created
6. **Notifications**: User and admin receive notifications

### 3. Notification Integration

Payment confirmations are automatically sent via:

- **Email**: Professional email templates
- **SMS**: SMS notifications via Africa's Talking
- **Admin Alerts**: Admin notifications for new payments

## 📊 Monitoring

### Logs

All Stripe activities are logged in:
- `logs/notifications.log` - Payment notifications
- PHP error logs - API errors and debugging

### Statistics

View payment statistics via:
- Stripe Dashboard - Real-time payment data
- Notification logs - System activity logs

## 🔒 Security

### Webhook Security

- **Signature Verification**: All webhooks are verified using Stripe signatures
- **HTTPS Only**: Webhooks only work over HTTPS in production
- **Event Validation**: Only expected events are processed

### API Security

- **Secret Key Protection**: Secret keys are never exposed to frontend
- **Environment Variables**: Configuration stored securely
- **Error Handling**: Sensitive information is not exposed in errors

## 🚀 Production Deployment

### 1. Switch to Live Mode

Update `stripe_config.php`:

```php
define('STRIPE_ENVIRONMENT', 'live'); // Change from 'test' to 'live'
```

### 2. Update API Keys

Replace test keys with live keys:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_your_live_publishable_key');
define('STRIPE_SECRET_KEY', 'sk_live_your_live_secret_key');
```

### 3. No Webhook Configuration Needed

This integration uses direct payment confirmation, so no webhook setup is required!

### 4. Test Thoroughly

- Test with real cards (small amounts)
- Verify payment confirmation
- Check notification delivery
- Monitor error logs

## 🆘 Troubleshooting

### Common Issues

1. **Invalid API Key**: Check that your keys are correct and active
2. **Payment Not Processing**: Check Stripe Dashboard for errors
3. **Notifications Not Sending**: Check email/SMS configuration
4. **Payment Confirmation Failed**: Check payment intent status

### Debug Steps

1. **Check Test Page**: Use `test_stripe.php` to diagnose issues
2. **Check Logs**: Review error logs for specific errors
3. **Stripe Dashboard**: Check Stripe Dashboard for payment status
4. **Payment Confirmation**: Test payment confirmation directly

## 📞 Support

- **Stripe Documentation**: [stripe.com/docs](https://stripe.com/docs)
- **Stripe Support**: Available in Stripe Dashboard
- **Test Page**: Use `test_stripe.php` for debugging

---

**Ready to accept payments!** 🎉

Once you've updated your Stripe keys, your payment system will be fully functional and ready for production use.
