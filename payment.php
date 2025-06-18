<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

$stmt = $conn->prepare("
    SELECT o.order_id, o.total_bill, o.order_date, o.payment_status, o.seller_id,
           GROUP_CONCAT(p.product_name SEPARATOR ', ') as products,
           u.name as seller_name
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN users u ON o.seller_id = u.user_id
    WHERE o.buyer_id = ? AND o.payment_status = 'Unpaid'
    GROUP BY o.order_id, o.seller_id
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pending_orders = [];
$total_amount = 0;
while ($row = $result->fetch_assoc()) {
    $pending_orders[] = $row;
    $total_amount += $row['total_bill'];
}

function createPaymentRecords($conn, $pending_orders, $user_id) {
    $payment_ids = [];
    
    foreach ($pending_orders as $order) {
        $check_stmt = $conn->prepare("SELECT payment_id FROM payments WHERE order_id = ?");
        $check_stmt->bind_param("i", $order['order_id']);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if (!$existing) {
            $seller_id = intval($order['seller_id']);
            
            $seller_check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
            $seller_check->bind_param("i", $seller_id);
            $seller_check->execute();
            $seller_exists = $seller_check->get_result()->fetch_assoc();
            
            if (!$seller_exists) {
                continue; 
            }
            
            $payment_amount = floatval($order['total_bill']);
            $order_id = intval($order['order_id']);
            $buyer_id = intval($user_id);
            
            $insert_stmt = $conn->prepare("
                INSERT INTO payments (payment_amount, payment_date, order_id, buyer_id, seller_id) 
                VALUES (?, NOW(), ?, ?, ?)
            ");
            
            $insert_stmt->bind_param("diii", 
                $payment_amount, 
                $order_id, 
                $buyer_id, 
                $seller_id
            );
            
            if ($insert_stmt->execute()) {
                $payment_ids[] = $conn->insert_id;
            } 
        } else {
            $payment_ids[] = $existing['payment_id'];
        }
    }
    return $payment_ids;
}

$payment_ids = [];
if (!empty($pending_orders)) {
    $payment_ids = createPaymentRecords($conn, $pending_orders, $user_id);
}

$merchant_id = "10039489";
$merchant_key = "prphv3vhfvwgk";
$return_url = "https://a72c-41-71-41-251.ngrok-free.app/TradeMart/success.php";
$cancel_url = "https://a72c-41-71-41-251.ngrok-free.app/TradeMart/cancel.php";
$notify_url = "https://a72c-41-71-41-251.ngrok-free.app/TradeMart/notify.php";

$payment_data = array(
    'merchant_id' => $merchant_id,
    'merchant_key' => $merchant_key,
    'return_url' => $return_url,
    'cancel_url' => $cancel_url,
    'notify_url' => $notify_url,
    'name_first' => !empty($user_data['name']) ? $user_data['name'] : 'Customer',
    'email_address' => !empty($user_data['email']) ? $user_data['email'] : 'customer@example.com',
    'amount' => number_format($total_amount, 2, '.', ''), 
    'item_name' => 'Order Payment - ' . count($pending_orders) . ' orders',
    'item_description' => 'Payment for orders: ' . implode(', ', array_column($pending_orders, 'order_id')),
    'custom_str1' => (string)$user_id,
    'custom_str2' => implode(',', array_column($pending_orders, 'order_id')),
    'custom_str3' => implode(',', $payment_ids),
    'custom_str4' => 'TradeMart_Payment',
    'm_payment_id' => 'TM_' . $user_id . '_' . time(),
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Complete Your Order</title>
    <link rel="stylesheet" href="payment.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Complete Your Payment</h1>
        
        <?php if (!empty($pending_orders)): ?>
            <div class="payment-summary">
                <h3>Orders to Pay</h3>
                <?php foreach ($pending_orders as $order): ?>
                    <div class="order-item">
                        <div>
                            <strong>Order #<?= $order['order_id'] ?></strong><br>
                            <small><?= date('F j, Y g:i A', strtotime($order['order_date'])) ?></small><br>
                            <span><?= htmlspecialchars($order['products']) ?></span><br>
                            <?php if (!empty($order['seller_name'])): ?>
                                <small>Seller: <?= htmlspecialchars($order['seller_name']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong>R<?= number_format($order['total_bill'], 2) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-amount">
                Total Amount: R<?= number_format($total_amount, 2) ?>
            </div>

            <?php if (!empty($payment_ids)): ?>
                <form action="https://sandbox.payfast.co.za/eng/process" method="post" id="payfast-form">
                    <?php foreach ($payment_data as $key => $value): ?>
                        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn-payment">
                        Proceed to PayFast Payment
                    </button>
                </form>
            <?php else: ?>
                <div class="error-info">
                    <p>Unable to process payment at this time. Please try again later.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No Pending Orders</h2>
                <a href="product_catalogue.php" class="btn-product">Continue Shopping</a>
                <a href="my_orders.php" class="btn-order">View My Orders</a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>