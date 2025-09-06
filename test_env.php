<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$key = getenv('STRIPE_SECRET_KEY');
echo "STRIPE_SECRET_KEY: " . ($key ? '[LOADED]' : '[NOT FOUND]') . "\n";

// Print all environment variables for debugging
foreach ($_ENV as $k => $v) {
    echo "$k=$v\n";
}
