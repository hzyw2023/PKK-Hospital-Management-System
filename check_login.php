<?php
session_start();
if(!(isset($_SESSION["email"]) && isset($_SESSION["password"]) && isset($_SESSION["user"]))){
    // Store current URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("location:login.php");
    exit(); // Add exit after redirect
}
$myemail = $_SESSION['email'];
?>
