<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$payment_id = $_GET['payment_id'] ?? null;
$pf_payment_id = $_GET['pf_payment_id'] ?? null;
$payment_status = $_GET['payment_status'] ?? null;

$user_id = $_GET['custom_str1'] ?? $_SESSION['user_id'] ?? null;
$order_ids = $_GET['custom_str2'] ?? null;
$payment_record_ids = $_GET['custom_str3'] ?? null;

if ($user_id && $order_ids && $payment_status === 'Paid') {
    $order_id_array = explode(',', $order_ids);
    $payment_record_id_array = $payment_record_ids ? explode(',', $payment_record_ids) : [];
    
    if (!empty($payment_record_id_array)) {
        foreach ($payment_record_id_array as $payment_record_id) {
            $update_payment_stmt = $conn->prepare("
                UPDATE payments 
                SET payfast_payment_id = ?,
                    payment_date = NOW()
                WHERE payment_id = ?
            ");
            $update_payment_stmt->bind_param("si", $pf_payment_id, $payment_record_id);
            $update_payment_stmt->execute();
        }
    }
    foreach ($order_id_array as $order_id) {
        $update_order_stmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
        $update_order_stmt->bind_param("i", $order_id);
        $update_order_stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="success.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="success-container">
        <div class="success-icon"><i class="bi bi-check2-circle"></i></div>
        <h1>Payment Successful!</h1>
        
        <div class="btn-section" style="margin-top: 30px;">
            <a href="view_orders.php" class="btn-order">View My Orders</a>
            <a href="product_catalogue.php" class="btn-shopping">Continue Shopping</a>
        </div>
    </div>
</body>
</html>