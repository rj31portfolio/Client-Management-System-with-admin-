<?php
require_once __DIR__ . '/../config/db_connect.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sendEmail($to, $subject, $message) {
    // Use PHP mail() or a library like PHPMailer for production
    mail($to, $subject, $message);
}
?>