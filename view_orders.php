<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "
SELECT o.order_id, o.order_date, o.order_status, o.payment_status, o.total_bill, 
        u.name AS seller_name,
        GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names
    FROM orders o
    LEFT JOIN users u ON o.seller_id = u.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.order_id
    LEFT JOIN products p ON p.product_id = oi.product_id
    WHERE o.buyer_id = ?
    GROUP BY o.order_id, o.order_date, o.order_status, o.payment_status, o.total_bill, u.name
    ORDER BY 
        CASE 
            WHEN o.order_status = 'Shipped' THEN 1
            WHEN o.order_status = 'Processed' THEN 2
            WHEN o.order_status = 'Delivered' THEN 3
        END, o.order_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = array();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All My Orders</title>
    <link rel="stylesheet" href="view_orders.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<div class="container">
    <header>
            <h2>My Orders</h2>
            <a href="buyer_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
    </header>

    <?php if (!empty($orders)): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Total (R)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                        $orderClass = 'status-' . strtolower($order['order_status']);
                        $paymentClass = 'status-' . strtolower($order['payment_status']);
                    ?>
                    <tr>
                        <td data-label="Order ID"><?= $order['order_id'] ?></td>
                        <td data-label="Date"><?= date('F j, Y, g:i A', strtotime($order['order_date'])) ?></td>
                        <td data-label="Product Name(s)"><?= htmlspecialchars($order['product_names']) ?></td>
                        <td data-label="Seller"><?= htmlspecialchars($order['seller_name']) ?></td>
                        <td data-label="Status" class="<?= $orderClass ?>"><?= $order['order_status'] ?></td>
                        <td data-label="Payment" class="<?= $paymentClass ?>"><?= $order['payment_status'] ?></td>
                        <td data-label="Total(R)">R<?= number_format($order['total_bill'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You haven't placed any orders yet.</p>
    <?php endif; ?>
</div>
</body>
</html>