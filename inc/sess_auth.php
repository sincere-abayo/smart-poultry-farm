<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $link = "https";
else
    $link = "http";
$link .= "://";
$link .= $_SERVER['HTTP_HOST'];
$link .= $_SERVER['REQUEST_URI'];
// Sync auth_user and userdata if needed
if (isset($_SESSION['auth_user']) && !isset($_SESSION['userdata'])) {
    $_SESSION['userdata'] = $_SESSION['auth_user'];
} elseif (isset($_SESSION['userdata']) && !isset($_SESSION['auth_user'])) {
    $_SESSION['auth_user'] = $_SESSION['userdata'];
}

// Define public pages that don't require authentication
$public_pages = ['login.php', 'registration.php', 'about', 'view_product', 'products', 'view_categories'];
$is_public_page = false;

// Check if current page is public (check both direct file access and parameter-based routing)
foreach ($public_pages as $public_page) {
    if (strpos($link, $public_page) !== false) {
        $is_public_page = true;
        break;
    }
}

// Also check for parameter-based routing (e.g., ?p=about)
if (isset($_GET['p']) && in_array($_GET['p'], $public_pages)) {
    $is_public_page = true;
}

// Debug: Uncomment the line below to see what's happening
error_log("DEBUG - URL: $link, GET[p]: " . (isset($_GET['p']) ? $_GET['p'] : 'not set') . ", is_public: " . ($is_public_page ? 'true' : 'false') . ", has_session: " . (isset($_SESSION['userdata']) || isset($_SESSION['auth_user']) ? 'true' : 'false'));

// Only redirect to login.php if not on public pages or home/index page
// Allow access to home page (/) and index.php, plus any public pages
$is_home_page = preg_match('/(\/|index\.php)$/', $link);
$has_session = isset($_SESSION['userdata']) || isset($_SESSION['auth_user']);

if (!$has_session && !$is_public_page && !$is_home_page) {
    redirect('login.php');
}
if ((isset($_SESSION['userdata']) || isset($_SESSION['auth_user'])) && strpos($link, 'login.php')) {
    redirect('index.php');
}
