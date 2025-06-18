<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$status_success = null;
$status_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['order_status'])) {
    $order_id = intval($_POST['order_id']);
    $order_status = $_POST['order_status'];

    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND seller_id = ?");
    $stmt->bind_param("sii", $order_status, $order_id, $seller_id);
   
    if ($stmt->execute()) {
        $status_success = true;
        $status_message = "Order status updated successfully.";
    } else {
        $status_success = false;
        $status_message = "Failed to update order.";
    }
}

$order = $conn->prepare("
    SELECT o.order_id, o.order_date, o.order_status, o.payment_status, o.total_bill,
           GROUP_CONCAT(p.product_name SEPARATOR ', ') AS products
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.seller_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$order->bind_param("i", $seller_id);
$order->execute();
$result = $order->get_result();

$orders = array();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Orders</title>
    <link rel="stylesheet" href="seller_orders.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h2>My Orders</h2>
            <a href="seller_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
        </header>

        <div id="toast" class="seller-order-toast"></div>

        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Products</th>
                        <th>Total (R)</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="Order ID"><?= $order['order_id'] ?></td>
                            <td data-label="Order Date"><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
                            <td data-label="Product"><?= htmlspecialchars($order['products']) ?></td>
                            <td data-label="Total(R)"><?= number_format($order['total_bill'], 2) ?></td>
                            <td data-label="Payment"><?= $order['payment_status'] ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="order_status">
                                        <option value="Processed" <?= $order['order_status'] == 'Processed' ? 'selected' : '' ?>>Processed</option>
                                        <option value="Shipped" <?= $order['order_status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="Delivered" <?= $order['order_status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                    </select>
                            </td>
                            <td>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#ffcccc' : '#5AB273';
            toast.style.color = isError ? '#8a1f1f' : 'black';
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 1000);
        }

        $(document).ready(function () {
            <?php if ($status_success !== null): ?>
                showToast("<?=  $status_message ?>", <?= $status_success ? 'false' : 'true' ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
