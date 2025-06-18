<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <link rel="stylesheet" href="cancel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <div class="cancel-message">
            <h2><i class="bi bi-x"></i> Payment Cancelled</h2>
            <p>Your payment was cancelled and no charges were made to your account.</p>
            <p>Your orders are still pending and waiting for payment.</p>
        </div>

        <div class="info-box">
            <h3>What happens next?</h3>
            <ul>
                <li>Your orders are still in your account and pending payment</li>
                <li>You can try the payment process again anytime</li>
                <li>Your cart items are safe and won't be lost</li>
                <li>No charges were made to your payment method</li>
            </ul>
        </div>

        <div class="btn-section">
            <a href="payment.php" class="btn-payment">Try Payment Again</a>
            <a href="view_orders.php" class="btn-order">View My Orders</a>
            <a href="product_catalogue.php" class="btn-product">Continue Shopping</a>
        </div>
    </div>
</body>
</html>