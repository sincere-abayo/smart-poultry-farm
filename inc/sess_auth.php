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

// Only redirect to login.php if not on home/index page
if ((!isset($_SESSION['userdata']) && !isset($_SESSION['auth_user'])) && !strpos($link, 'login.php') && !preg_match('/(\/|index\.php)$/', $link)) {
    redirect('login.php');
}
if ((isset($_SESSION['userdata']) || isset($_SESSION['auth_user'])) && strpos($link, 'login.php')) {
    redirect('index.php');
}
