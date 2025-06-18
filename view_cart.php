<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$shipping = 50;
$cart_total = 0;
$user_id = $_SESSION['user_id'];

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
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link rel="stylesheet" href="view_cart.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div id="toast" class="cart-toast"></div>
    <div class="cart-items">

    <h2>MY SHOPPING CART</h2>
    
    <?php if (!empty($cart_items)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Price(R)</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($cart_items as $item):
                $item_total = $item['price'] * $item['quantity'];
                $cart_total += $item_total;
            ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td data-label="Product"><img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image"></td>
                    <td data-label="Name"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td data-label="Price(R)">R<?= number_format($item['price'], 2) ?></td>
                    <td data-label="Quantity">
                        <form onsubmit="updateCart(<?= $item['product_id'] ?>); return false;" style="display:inline;">
                            <input type="number" id="quantity-<?= $item['product_id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                            <button type="submit" id="edit-btn"></button>
                        </form>
                    </td>
                    <td data-label="Total">R<?= number_format($item_total, 2) ?></td>
                    <td><button id="delete-btn" onclick="removeFromCart(<?= $item['product_id'] ?>)"><i class="bi bi-trash-fill"></i></button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="charges">
            <hr>
            <p><strong>Standard Shipping:</strong> R<?= number_format($shipping, 2) ?></p>
            <p><strong>Cart Total:</strong> R<?= number_format($cart_total + $shipping, 2) ?></p>

            <form class="order-form" action="order.php" method="post">
                <button type="submit"class="checkout-btn">Proceed to Checkout <i class="bi bi-arrow-right"></i></button>
            </form>
        </div>
    <?php else: ?> 
        <p>Your cart is empty...</p>
    <?php endif; ?>
    <a href="buyer_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
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


    function removeFromCart(productId) {
        $.post('manage_cart.php', {
            action: 'delete',
            product_id: productId
        })
        .done(response => {
            const data = JSON.parse(response);
            showToast(data.message || "Action complete.", data.status !== 'deleted');

            if (data.status === 'deleted') {
                $.get('view_cart.php', function(cartData) {
                    $('#cart-items').html(cartData);
                    updateCartCount();
                }).fail(() => showToast("Error refreshing cart.", true));
            }
        })
        .fail(() => showToast("Error removing from cart.", true));
    }


    function updateCart(productId) {
    const quantity = $('#quantity-' + productId).val();

        $.post('manage_cart.php', {
            action: 'update',
            product_id: productId,
            quantity: quantity
        })
        .done(response => {
            const data = JSON.parse(response);
            showToast(data.message || "Action complete.", data.status !== 'updated');
            
            if (data.status === 'updated') {
                $.get('view_cart.php', function(cartData) {
                    $('#cart-items').html(cartData);
                    updateCartCount();
                }).fail(() => showToast("Error refreshing cart.", true));
            }
        })
        .fail(() => showToast("Error updating cart.", true));
    }

    function updateCartCount() {
        $.post('add_to_cart.php', { action: 'count' })
        .done(count => $('#cart-count').text(count))
        .fail(() => showToast("Error updating cart count.", true));
    }
</script>
</body>
</html>



