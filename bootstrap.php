<?php
// Ensure no output before session_start
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global configuration
define('SITE_ROOT', __DIR__);
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 300);
define('SALT', '2123293dsj2hu2nikhiljdsd');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once('connect.php');