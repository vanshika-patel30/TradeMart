<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$totalOrdersQuery = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$totalOrders = $totalOrdersQuery->fetch_assoc()['total_orders'] ?? 0;

$totalBuyersQuery = $conn->query("SELECT COUNT(*) AS total_buyers FROM users WHERE role = 'Buyer'");
$totalBuyers = $totalBuyersQuery->fetch_assoc()['total_buyers'] ?? 0;

$totalSellersQuery = $conn->query("SELECT COUNT(*) AS total_sellers FROM users WHERE role = 'Seller'");
$totalSellers = $totalSellersQuery->fetch_assoc()['total_sellers'] ?? 0;

$pendingPaymentsQuery = $conn->query("SELECT COUNT(*) AS pending_payments FROM orders WHERE payment_status = 'Unpaid'");
$pendingPayments = $pendingPaymentsQuery->fetch_assoc()['pending_payments'] ?? 0;

$statusQuery = $conn->query("
    SELECT order_status, COUNT(*) as count 
    FROM orders 
    GROUP BY order_status
");

$orderStatus = [];
while ($row = $statusQuery->fetch_assoc()) {
    $orderStatus[$row['order_status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analytics</title>
    <link rel="stylesheet" href="analytics.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>TradeMart Analytics</h2>
            <a href="admin_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
        </header>

        <div class="stats">
            <div class="card">
                <h3>Total Orders</h3>
                <p><?= $totalOrders ?></p>
            </div>
            <div class="card">
                <h3>Total Buyers</h3>
                <p><?= $totalBuyers ?></p>
            </div>
            <div class="card">
                <h3>Total Sellers</h3>
                <p><?= $totalSellers ?></p>
            </div>
            <div class="card">
                <h3>Pending Payments</h3>
                <p><?= $pendingPayments ?></p>
            </div>
        </div>

        <div class="order-status-summary">
            <h3>Orders:</h3>
            <ul>
                <li>Processed: <?= $orderStatus['Processed'] ?? 0 ?></li>
                <li>Shipped: <?= $orderStatus['Shipped'] ?? 0 ?></li>
                <li>Delivered: <?= $orderStatus['Delivered'] ?? 0 ?></li>
            </ul>
        </div>
    </div>
</body>
</html>
