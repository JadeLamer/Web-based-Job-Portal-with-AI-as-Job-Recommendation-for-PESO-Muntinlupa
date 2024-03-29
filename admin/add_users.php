<?php
$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

session_start();

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// if user is not admin show 404.php
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}
?>