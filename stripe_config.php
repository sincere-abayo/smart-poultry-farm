<?php
// Load .env if not already loaded
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? null);
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? null);
define('STRIPE_CURRENCY', $_ENV['STRIPE_CURRENCY'] ?? 'rwf');
define('STRIPE_ENVIRONMENT', $_ENV['STRIPE_ENVIRONMENT'] ?? 'test');
define('STRIPE_API_URL', $_ENV['STRIPE_API_URL'] ?? 'https://api.stripe.com/v1');