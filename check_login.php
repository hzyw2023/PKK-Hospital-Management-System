<?php
require_once('bootstrap.php');

if(!isset($_SESSION["email"]) || !isset($_SESSION["user"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
