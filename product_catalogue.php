<?php
require 'db.php';

session_start();

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE buyer_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_count = $cart_result->fetch_assoc()['total'] ?? 0;
}

$categories = array();
$category_result = $conn->query("SELECT * FROM product_categories");
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$products = array();

if ($category_id > 0 && $search != '') {
    $stmt = $conn->prepare("SELECT p.*, u.name, c.category_name 
                            FROM products p
                            JOIN users u ON p.seller_id = u.user_id
                            JOIN product_categories c ON p.category_id = c.category_id
                            WHERE p.category_id = ? AND p.product_name LIKE ?
                            ORDER BY p.created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("is", $category_id, $likeSearch);
    $stmt->execute();
    $product_result = $stmt->get_result();
} elseif ($category_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, u.name, c.category_name 
                            FROM products p
                            JOIN users u ON p.seller_id = u.user_id
                            JOIN product_categories c ON p.category_id = c.category_id
                            WHERE p.category_id = ?
                            ORDER BY p.created_at DESC");
    $stmt->bind_param("i", $category_id); 
    $stmt->execute();
    $product_result = $stmt->get_result();
} elseif ($search != '') {
    $stmt = $conn->prepare("SELECT p.*, u.name, c.category_name 
                            FROM products p
                            JOIN users u ON p.seller_id = u.user_id
                            JOIN product_categories c ON p.category_id = c.category_id
                            WHERE p.product_name LIKE ?
                            ORDER BY p.created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("s", $likeSearch);
    $stmt->execute();
    $product_result = $stmt->get_result();
} else {
    $product_result = $conn->query("SELECT p.*, u.name, c.category_name 
                                    FROM products p
                                    JOIN users u ON p.seller_id = u.user_id
                                    JOIN product_categories c ON p.category_id = c.category_id
                                    ORDER BY p.created_at DESC");
}

if ($product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

$toast_message = isset($_SESSION['toast_message']) ? $_SESSION['toast_message'] : '';
unset($_SESSION['toast_message']);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalogue</title>
    <link rel="stylesheet" href="product_catalogue.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="toast" class="cart-toast"></div>
    <div class="container">
        <header>
            <button class="menu-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <img src="logos/small_logo.png" alt="TradeMart Logo">
            <h1>TRADEMART COLLECTION</h1>
            <section class="header-buttons">
                <a id="view-cart-btn" class="header-icon" style="position: relative; cursor: pointer">
                    <i class="bi bi-cart" style="font-size: 1.5rem;"></i>
                    <span id="cart-count"><?= $cart_count ?></span>
                </a>
                <a href="buyer_dashboard.php" class="back-dashboard"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
            </section>
        </header>
        
        <div class="sidebar">    
            <h2>Categories</h2>
            <hr> <hr>
            <ul>
                <li><a href="product_catalogue.php">All Products</a></li>
                <?php foreach ($categories as $category) : ?>
                    <li>
                        <a href="product_catalogue.php?category=<?= $category['category_id']?>">
                            <?= htmlspecialchars($category['category_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="main">
            <div class="main-header">
                <h3>Clothing Marketplace - Curated for You...</h3>
                <form method="GET" action="">
                    <div class="input-icon" style="position: relative;">
                        <input type="search" name="search" id="search" placeholder="Discover catalogue" value="<?= htmlspecialchars($search)?>">
                        <button type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="product-grid">
                <?php foreach ($products as $product) : ?>
                <?php
                $imagePath = $product['image'];
                $parts = explode('/', $imagePath);
                $encodedParts = array_map('rawurlencode', $parts);
                $encodedImagePath = implode('/', $encodedParts);
                ?>
                <div class="product-card" 
                     data-image="<?= htmlspecialchars($encodedImagePath) ?>" 
                     data-name="<?= htmlspecialchars($product['product_name']) ?>" 
                     data-description="<?= htmlspecialchars($product['description']) ?>" 
                     data-price="<?= htmlspecialchars($product['price']) ?>" 
                     data-seller="<?= htmlspecialchars($product['name']) ?>" 
                     data-id="<?= (int)$product['product_id'] ?>">
                    <img src="<?= $encodedImagePath ?>" alt="Product Image">
                    <div class="product-details">
                        <h4><?= htmlspecialchars($product['product_name']) ?></h4>
                        <h5>R<?= htmlspecialchars($product['price']) ?></h5>
                        <input type="hidden" id="product_name<?= $product['product_id'] ?>" value="<?= htmlspecialchars($product['product_name']) ?>">
                        <input type="hidden" id="price<?= $product['product_id'] ?>" value="<?= htmlspecialchars($product['price']) ?>">
                        <div class="card-cart" data-id="<?= $product['product_id'] ?>"><i class="bi bi-cart-check-fill"></i></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="modal" class="product-modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img id="modal-img" src="" alt="">
                <div class="product-details">
                    <h4 id="modal-product"></h4>
                    <p id="modal-description"></p>
                    <h5 id="modal-price"></h5>
                    <div class="cart-icon"><i class="bi bi-cart-check-fill"></i></div>
                    <p id="modal-seller"></p>
                </div>
            </div>
        </div>

        <div id="cart-modal" class="cart-modal">
            <div class="cart-content">
                <span class="close-cart">×</span>
                <div id="cart-items"></div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        $(document).ready(function() {
            function showToast(message) {
                const $toast = $('#toast').text(message);
                $toast.addClass('show');
                setTimeout(() => {
                    $toast.removeClass('show');
                    setTimeout(() => $toast.empty(), 500);
                }, 3000);
            }

            function updateCartCount() {
                $.post('add_to_cart.php', { action: 'count' })
                    .done(count => $('#cart-count').text(count))
                    .fail(() => showToast("Error updating cart count.", true));
            }

            function addToCart(productId) {
                $.post('add_to_cart.php', {
                    action: 'add',
                    product_id: productId,
                    product_name: $('#product_name' + productId).val(),
                    price: $('#price' + productId).val()
                })
                .done(response => {
                    let data;
                    try {
                        data = JSON.parse(response);
                    } catch {
                        return showToast("Error processing cart response.", true);
                    }

                    showToast(data.message || "Product added to cart!", data.status !== 'added');
                    if (data.status === 'added') {
                        $('#cart-count').text(data.cart_count);
                        if ($('#cart-modal').is(':visible')) {
                            $.get('view_cart.php')
                                .done(cartData => $('#cart-items').html(cartData))
                                .fail(() => showToast("Error refreshing cart.", true));
                        }
                    }
                })
                .fail(() => showToast("Error adding to cart.", true));
            }

            function removeFromCart(productId) {
                $.post('manage_cart.php', {
                    action: 'delete',
                    product_id: productId
                })
                .done(response => {
                    let data;
                    try {
                        data = JSON.parse(response);
                    } catch {
                        return showToast("Error processing cart response.", true);
                    }

                    showToast(data.message || "Item removed from cart.", data.status !== 'deleted');

                    if (data.status === 'deleted') {
                        $.get('view_cart.php')
                            .done(cartData => {
                                $('#cart-items').html(cartData);
                                updateCartCount();
                            })
                            .fail(() => showToast("Error refreshing cart.", true));
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
                    let data;
                    try {
                        data = JSON.parse(response);
                    } catch {
                        return showToast("Error processing cart response.", true);
                    }

                    showToast(data.message || "Quantity updated.", data.status !== 'updated');

                    if (data.status === 'updated') {
                        $.get('view_cart.php')
                            .done(cartData => {
                                $('#cart-items').html(cartData);
                                updateCartCount();
                            })
                            .fail(() => showToast("Error refreshing cart.", true));
                    }
                })
                .fail(() => showToast("Error updating cart.", true));
            }

            $('.product-card').on('click', function(e) {
                if ($(e.target).closest('.card-cart').length) {
                    return; 
                }
                const $this = $(this);
                const image = $this.data('image');
                const name = $this.data('name');
                const description = $this.data('description');
                const price = $this.data('price');
                const seller = $this.data('seller');
                const productId = $this.data('id');
                
                if (!image || !name || !description || !price || !seller || !productId) {
                    showToast('Error opening product details');
                    return;
                }

                $('#modal-img').attr("src", image);
                $('#modal-product').text(name);
                $('#modal-description').text(description);
                $('#modal-price').text("R" + price);
                $('#modal-seller').text("Sold by: " + seller);
                $('#modal .cart-icon').data('product-id', productId);
                $('#modal').addClass('show').fadeIn();
            });

            $('.close-btn').on('click', function() {
                $('#modal').removeClass('show').fadeOut();
            });

            $(window).on('click', function(e) {
                if ($(e.target).is('#modal')) {
                    $('#modal').removeClass('show').fadeOut();
                }
            });

            function viewCart() {
                $('#cart-items').html('<p>Loading...</p>');
                $.get('view_cart.php')
                    .done(data => {
                        $('#cart-items').html(data);
                        $('#cart-modal').show();
                    })
                    .fail(() => {
                        $('#cart-items').html('<p>Error loading cart.</p>');
                    });
            }

            $('#view-cart-btn').on('click', viewCart);


            $('.close-cart').on('click', function() {
                $('#cart-modal').css('display', 'none');
            });

            $(window).on('click', function(event) {
                if ($(event.target).is('#cart-modal')) {
                    $('#cart-modal').css('display', 'none');
                }
            });

            $('.card-cart').on('click', function(e) {
                e.stopPropagation();
                let productId = $(this).data('id');
                if (productId) {
                    addToCart(productId);
                }
            });

            $('#modal .cart-icon').on('click', function() {
                let productId = $(this).data('product-id');
                if (productId) {
                    addToCart(productId);
                }
            });

            $(document).on('click', '.remove-cart-item', function() {
                let productId = $(this).data('id');
                if (productId) {
                    removeFromCart(productId);
                }
            });

            $(document).on('change', '.cart-quantity', function() {
                let productId = $(this).data('id');
                if (productId) {
                    updateCart(productId);
                }
            });
        });
    </script>
</body>
</html>