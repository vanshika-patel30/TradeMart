<?php
require 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';

$products = array();

if ($search != '') {
    $stmt = $conn->prepare("SELECT p.* FROM products p WHERE p.seller_id = ? AND p.product_name LIKE ? ORDER BY p.created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("is", $user_id, $likeSearch);
    $stmt->execute();
    $product_result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT p.* FROM products p WHERE p.seller_id = ? ORDER BY p.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
}

if ($product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products</title>
    <link rel="stylesheet" href="my_products.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <img src="logos/small_logo.png" alt="TradeMart Logo">
            <h1>MY PRODUCTS</h1>
            <section class="header-buttons">
                <a href="add_products.php" class="add-products"><i class="bi bi-bag-plus-fill"></i>Add Products</a>
                <a href="seller_dashboard.php" class="back-dashboard"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
            </section>
        </header>

        <div class="main">
            <div class="main-header">
                <h3>Products You're Selling...</h3>
                    <form method="GET" action="">
                        <div class="input-icon" style="position: relative;">
                            <input type="search" name="search" id="search" placeholder="Search Products" value="<?= htmlspecialchars($search)?>">
                            <button type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
            </div>
            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <h4>No products found.</h4>
                <?php else: ?>
                    <?php foreach ($products as $product) : ?>
                    <?php
                    $imagePath = $product['image'];
                    $parts = explode('/', $imagePath);
                    $encodedParts = array_map('rawurlencode', $parts);
                    $encodedImagePath = implode('/', $encodedParts);
                    $date_created = date('d-m-Y', strtotime($product['created_at']));
                    ?>
                    <div class="product-card"
                        data-image="<?= htmlspecialchars($encodedImagePath) ?>" 
                        data-name="<?= htmlspecialchars($product['product_name']) ?>" 
                        data-description="<?= htmlspecialchars($product['description']) ?>" 
                        data-price="<?= htmlspecialchars($product['price']) ?>"
                        data-date="<?= $date_created ?>"
                        data-id="<?= (int)$product['product_id'] ?>">
                            <img src="<?= $encodedImagePath ?>" alt="Product Image">
                        <div class="product-details">
                            <h5><?= htmlspecialchars($product['product_name']) ?></h5>
                            <h6>R<?= htmlspecialchars($product['price']) ?></h6>
                            <p>Listed on: <?= $date_created ?></p> 
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="modal" class="product-modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <img id="modal-img" src="" alt="Product Image">
                <div class="modal-product-details">
                    <h4 id="modal-product"></h4>
                    <p id="modal-description"></p>
                    <h5 id="modal-price"></h5>
                    <a id="modal-edit"><i class="bi bi-pencil-fill"></i>Edit Product</a>
                    <p id="modal-date"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
         $('.product-card').on('click', function(e) {
                const $this = $(this);
                const image = $this.data('image');
                const name = $this.data('name');
                const description = $this.data('description');
                const price = $this.data('price');
                const date_created = $this.data('date');
                const productId = $this.data('id');

                $('#modal-img').attr("src", image);
                $('#modal-product').text(name);
                $('#modal-description').text(description);
                $('#modal-price').text("R" + price);
                $('#modal-date').text("Listed on: " + date_created);
                $('#modal-edit').attr("href", "edit_product.php?product_id=" + productId);
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

        $('#search').on('search', function() {
            if (!$(this).val()) {
                window.location.href = 'my_products.php';
            }
        });
    </script>
</body>
</html>