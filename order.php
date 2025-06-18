<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$shipping = 50;
$order_created = false;
$order_details = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("
            SELECT c.product_id, c.quantity, p.product_name, p.price, p.seller_id, p.image
            FROM cart_items c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.buyer_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cart_items = [];
        $cart_total = 0;
        
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
            $cart_total += $row['price'] * $row['quantity'];
        }
        
        if (empty($cart_items)) {
            throw new Exception("Cart is empty");
        }
        
        $total_bill = $cart_total + $shipping;

        $sellers = [];
        foreach ($cart_items as $item) {
            $seller_id = $item['seller_id'];
            if (!isset($sellers[$seller_id])) {
                $sellers[$seller_id] = [];
            }
            $sellers[$seller_id][] = $item;
        }
        
        $order_ids = array();

        foreach ($sellers as $seller_id => $seller_items) {
            $seller_total = 0;
            foreach ($seller_items as $item) {
                $seller_total += $item['price'] * $item['quantity'];
            }
            
            $order_total = $seller_total + (empty($order_ids) ? $shipping : 0);
 
            $order_stmt = $conn->prepare("
                INSERT INTO orders (order_date, order_status, total_bill, buyer_id, seller_id, payment_status)
                VALUES (NOW(), 'Processed', ?, ?, ?, 'Unpaid')
            ");
            $order_stmt->bind_param("dii", $order_total, $user_id, $seller_id);
            $order_stmt->execute();
            
            $order_id = $conn->insert_id;
            $order_ids[] = $order_id;
            
            $item_stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($seller_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
        }
        
        $clear_cart = $conn->prepare("DELETE FROM cart_items WHERE buyer_id = ?");
        $clear_cart->bind_param("i", $user_id);
        $clear_cart->execute();
        
        $conn->commit();
        
        $order_created = true;
        $order_details = [
            'order_ids' => $order_ids,
            'total_bill' => $total_bill,
            'cart_items' => $cart_items,
            'order_date' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error processing order: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("
    SELECT c.product_id, c.quantity, p.product_name, p.price, p.image
    FROM cart_items c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.buyer_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = array();
$cart_total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $cart_total += $row['price'] * $row['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Your Order</title>
    <link rel="stylesheet" href="order.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <?php if ($order_created): ?>
            <div class="success-message">
                <h2><i class="bi bi-check2-circle"></i> Order Placed Successfully!</h2>
                <p>Thank you for your order. Your order has been placed and is being processed.</p>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php foreach ($order_details['cart_items'] as $item): ?>
                    <div class="order-item">
                        <div class="product-info">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image" class="product-image">
                            <div>
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                <span>Quantity: <?= $item['quantity'] ?></span>
                            </div>
                        </div>
                        <div>
                            <strong>R<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-section">
                    <div class="subtotal">
                        <span>Subtotal:</span>
                        <span>R<?= number_format($order_details['total_bill'] - $shipping, 2) ?></span>
                    </div>
                    <div class="shipping">
                        <span>Shipping:</span>
                        <span>R<?= number_format($shipping, 2) ?></span>
                    </div>
                    <hr>
                    <div class="total">
                        <span>Total:</span>
                        <span>R<?= number_format($order_details['total_bill'], 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="btn-section">
                <a href="product_catalogue.php" class="btn-back">Continue Shopping</a>
                <a href="view_orders.php" class="btn-orders">View My Orders</a>
                <a href="payment.php" class="btn-pay">Pay Now</a>
            </div>

        <?php elseif (isset($error_message)): ?>
            <div class="error-message">
                <h2><i class="bi bi-x"></i>Order Failed</h2>
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
            <div style="text-align: center;">
                <a href="view_cart.php" class="btn">Back to Cart</a>
            </div>

        <?php else: ?>
            <h1>Checkout</h1>
            
            <?php if (empty($cart_items)): ?>
                <div class="error-message">
                    <p>Your cart is empty. Please add items to your cart before checkout.</p>
                </div>
                <div style="text-align: center;">
                    <a href="index.php" class="btn">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="order-summary">
                    <h3>Review Your Order</h3>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image" class="product-image">
                                <div>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                    <span>Quantity: <?= $item['quantity'] ?></span>
                                </div>
                            </div>
                            <div>
                                <strong>R<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="total-section">
                        <div class="subtotal">
                            <span>Subtotal:</span>
                            <span>R<?= number_format($cart_total, 2) ?></span>
                        </div>
                        <div class="shipping">
                            <span>Shipping:</span>
                            <span>R<?= number_format($shipping, 2) ?></span>
                        </div>
                        <hr>
                        <div class="total">
                            <span>Total:</span>
                            <span>R<?= number_format($cart_total + $shipping, 2) ?></span>
                        </div>
                    </div>
                </div>

                <form class="order-form" method="POST">
                    <input type="hidden" name="checkout" value="1">
                    <button type="submit" class="btn-place">
                        Place Order - R<?= number_format($cart_total + $shipping, 2) ?>
                    </button>
                    <a href="view_cart.php" class="btn-cart">Back to Cart</a>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>