<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT payment_id, payment_amount, payment_date, order_id, payfast_payment_id
    FROM payments
    WHERE seller_id = ?
    ORDER BY payment_date DESC
");

$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$sales = array();
while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}

$total_stmt = $conn->prepare("SELECT SUM(payment_amount) AS total_sales FROM payments WHERE seller_id = ?");
$total_stmt->bind_param("i", $seller_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_sales = $total_row['total_sales'] ?? 0;
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sales</title>
    <link rel="stylesheet" href="view_sales.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
         <header>
            <h2>My Orders</h2>
            <a href="seller_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
        </header>

        <div class="summary">
            <strong>Total Sales:</strong> R<?= number_format($total_sales, 2) ?>
        </div>

        <?php if (empty($sales)): ?>
            <p>No sales found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Amount (R)</th>
                        <th>Payment Date</th>
                        <th>PayFast Ref</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td data-label="Payment ID"><?= htmlspecialchars($sale['payment_id']) ?></td>
                            <td data-label="Order ID"><?= htmlspecialchars($sale['order_id']) ?></td>
                            <td data-label="Amount"><?= number_format($sale['payment_amount'], 2) ?></td>
                            <td data-label="Payment Date"><?= date('Y-m-d H:i', strtotime($sale['payment_date'])) ?></td>
                            <td data-label="Payfast Ref"><?= htmlspecialchars($sale['payfast_payment_id']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
    
