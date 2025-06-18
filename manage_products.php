<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION["user_id"];
$toast_success = null;
$toast_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product_id"])) {
    $delete_id = $_POST["delete_product_id"];

    $delete = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $delete->bind_param("i", $delete_id);

    if ($delete->execute()) {
        $toast_message = "Product deleted successfully.";
        $toast_success = true;
    } else {
        $toast_message = "Error deleting product.";
        $toast_success = false;
    }
}

$stmt = "
    SELECT p.product_id, p.product_name, p.price, p.description, p.image, u.name AS seller_name
    FROM products p
    JOIN users u ON p.seller_id = u.user_id
";
$result = $conn->query($stmt);

$products = array();
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="manage_products.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <header>
                <h2>Listed Products</h2>
                <a href="admin_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
            </header>

            <div id="toast" class="admin-products-toast"></div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Price (R)</th>
                    <th>Seller</th>
                    <th>Image</th>
                    <th>Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product) :?>
                        <?php 
                            $imagePath = $product['image']; 
                            $parts = explode('/', $imagePath);
                            $encodedParts = array_map('rawurlencode', $parts);
                            $encodedImagePath = implode('/', $encodedParts); 
                        ?>
                        <tr>
                            <td data-label="ID"><?= $product['product_id'] ?></td>
                            <td data-label="Product"><?= htmlspecialchars($product['product_name']) ?></td>
                            <td data-label="Price(R)"><?= number_format($product['price'], 2) ?></td>
                            <td data-label="Seller"><?= htmlspecialchars($product['seller_name']) ?></td>
                            <td><img src="<?= $encodedImagePath ?>" class="product-image" alt="Product Image"></td>
                            <td>
                                <a href="edit_product.php?product_id=<?= $product['product_id'] ?>" class="action-btn edit-btn">Edit</a>
                                <button class="action-btn delete-btn" onclick="openModal(<?= $product['product_id'] ?>)">Delete</button>
                            </td>
                        </tr>   
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="modal" id="deleteModal">
            <div class="modal-content">
                <p>Are you sure you want to delete this product?</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_product_id" id="delete_product_id">
                    <button type="submit" class="confirm-btn">Yes, Delete</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const deleteModal = document.getElementById("deleteModal");
        const deleteProductIdInput = document.getElementById("delete_product_id");

        function openModal(productId) {
            deleteProductIdInput.value = productId;
            deleteModal.style.display = "flex";
        }

        function closeModal() {
            deleteModal.style.display = "none";
        }

        window.addEventListener("click", function (e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        <?php if (!empty($toast_message)): ?>
        const toast = document.getElementById('toast');
        toast.textContent = "<?= $toast_message ?>";
        toast.style.backgroundColor = <?= $toast_success ? "'lemonchiffon'" : "'#ffcccc'" ?>;
        toast.style.color = <?= $toast_success ? "'black'" : "'#8a1f1f'" ?>;
        toast.style.display = 'block';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 2000);
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href.split("?")[0]);
        }
        <?php endif; ?>
    </script>
</body>
</html>