<?php
session_start();
require_once('initialize.php');
require_once('classes/SystemSettings.php');

echo "Current Session Data:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['auth_user'])) {
    // Copy auth_user data to userdata if it doesn't exist
    if (!isset($_SESSION['userdata'])) {
        $_SESSION['userdata'] = $_SESSION['auth_user'];
    }

    // Initialize system settings
    $settings = new SystemSettings();
    $settings->load_system_info();

    echo "Session synchronized successfully";
} else {
    echo "No auth_user session found";
}
