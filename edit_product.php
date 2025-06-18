<?php
    require 'db.php';
    session_start();

    $user_id = $_SESSION['user_id'] ?? null;
    $edit_success = null;
    $edit_message = "";

    $product_id = $_GET['product_id'] ?? null;

    if ($user_id) {
        $check_admin = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $check_admin->bind_param("i", $user_id);
        $check_admin->execute();
        $check_admin_result = $check_admin->get_result();
        $user = $check_admin_result->fetch_assoc();
        $check_admin->close();

        $is_admin = $user && $user['role'] === 'Admin';
    } else {
        $is_admin = false;
    }

    if ($is_admin) {
        $stmt = $conn->prepare("SELECT product_name, description, price, image, category_id FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
    } else {
        $stmt = $conn->prepare("SELECT product_name, description, price, image, category_id FROM products WHERE product_id = ? AND seller_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    $categories = array();
    $category_result = $conn->query("SELECT * FROM product_categories");
    if ($category_result->num_rows > 0) {
        while ($row = $category_result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?" . ($is_admin ? "" : " AND seller_id = ?"));
            $is_admin ? $stmt->bind_param("i", $product_id) : $stmt->bind_param("ii", $product_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $image_path = $result->fetch_assoc()['image'];
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?" . ($is_admin ? "" : " AND seller_id = ?"));
            $is_admin ? $stmt->bind_param("i", $product_id) : $stmt->bind_param("ii", $product_id, $user_id);

            if ($stmt->execute()) {
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
                $edit_success = true;
                $edit_message = "Product deleted successfully!";
            } else {
                $edit_success = false;
                $edit_message = "Failed to delete product!";
            }
            $stmt->close();
        } else {
            $product_name = $_POST['product_name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            $image = $_FILES['image']['name'] ? $_FILES['image'] : null;

            $db_path = $product['image']; 

            if ($image) {
                $stmt = $conn->prepare("SELECT category_name FROM product_categories WHERE category_id = ?");
                $stmt->bind_param("i", $category_id);
                $stmt->execute();
                $stmt->bind_result($category_name);
                $stmt->fetch();
                $stmt->close();

                $folder = "products/" . str_replace([" ", "'"], ["_", ""], strtolower($category_name)) . "/";
                $image_name = basename($image["name"]);
                $image_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
                $target_file = $folder . $image_name;
                $db_path = $target_file;

                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($imageFileType, $allowed_types)) {
                    $edit_success = false;
                    $edit_message = "Invalid image type! Allowed: jpg, jpeg, png, gif.";
                } elseif (!move_uploaded_file($image["tmp_name"], $target_file)) {
                    $edit_success = false;
                    $edit_message = "Failed to upload image.";
                } else {
                    if (!empty($product['image']) && file_exists($product['image'])) {
                        unlink($product['image']);
                    }
                }
            }

            if (!isset($edit_success) || $edit_success !== false) {
                if ($is_admin) {
                    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, image = ?, category_id = ? WHERE product_id = ?");
                    $stmt->bind_param("ssdsii", $product_name, $description, $price, $db_path, $category_id, $product_id);
                } else {
                    $stmt = $conn->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, image = ?, category_id = ? WHERE product_id = ? AND seller_id = ?");
                    $stmt->bind_param("ssdsiii", $product_name, $description, $price, $db_path, $category_id, $product_id, $user_id);
                }

                if ($stmt->execute()) {
                    $edit_success = true;
                    $edit_message = "Product updated successfully.";
                } else {
                    $edit_success = false;
                    $edit_message = "Failed to update product.";
                    if ($image && file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
                $stmt->close();
            }
        }
    }
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="edit_product.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="toast" class="edit-product-toast"></div>

    <div class="edit-product-modal" id="edit-product-modal">
        <span class="close-modal" id="closeModal">
            <a href="<?= $is_admin ? 'manage_products.php' : 'my_products.php' ?>" style="text-decoration:none; color:inherit;">&times;</a>
        </span>
        <h1>EDIT PRODUCT</h1>
        <form id="edit-product-form" method="POST" action="" enctype="multipart/form-data">
            <div class="form-info">
                <label class="label">Product Name:</label>
                <input type="text" name="product_name" id="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                <span class="error" id="nameError">Please enter a valid name!</span>
            </div>
            <div class="form-info">
                <label class="label">Description:</label>
                <textarea name="description" id="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                <span class="error" id="descriptionError">Please enter a valid description!</span>
            </div>
            <div class="form-info">
                <label class="label">Price:</label>
                <input type="number" step="0.01" name="price" id="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                <span class="error" id="priceError">Please enter a valid price!</span>
            </div>
            <div class="form-info">
                <label class="label">Category:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>" <?= $category['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="error" id="categoryError">Please select a category!</span>
            </div>
            <div class="form-info">
                <label class="label">Image:</label>
                <input type="file" name="image" id="image" accept="image/*">
                <span class="error" id="imageError">Please upload a valid image!</span>
                <small>Current: <?= htmlspecialchars(basename($product['image'])) ?></small>
            </div>
            <button type="submit" id="update">Update Product</button>
        </form>
        <form id="delete-form" method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product?');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" id="delete" style="background-color: #dc3545; margin-top: 10px;">Delete Product</button>
        </form>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#ffcccc' : 'a3d8af';
            toast.style.color = isError ? '#8a1f1f' : 'black';
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        }

        $(document).ready(function () {
            $('#edit-product-form').on('submit', function (e) {
                let isValid = true;

                const product_name = $('#product_name').val().trim();
                if (product_name === "") {
                    $('#nameError').show();
                    isValid = false;
                } else {
                    $('#nameError').hide();
                }

                const description = $('#description').val().trim();
                if (description.length < 10) {
                    $('#descriptionError').show();
                    isValid = false;
                } else {
                    $('#descriptionError').hide();
                }

                const price = $('#price').val();
                if (price === "" || parseFloat(price) <= 0) {
                    $('#priceError').show();
                    isValid = false;
                } else {
                    $('#priceError').hide();
                }

                const category = $('#category_id').val();
                if (category === "") {
                    $('#categoryError').show();
                    isValid = false;
                } else {
                    $('#categoryError').hide();
                }

                const image = $('#image').val();
                if (image) {
                    const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                    if (!allowedExtensions.exec(image)) {
                        $('#imageError').show();
                        isValid = false;
                    } else {
                        $('#imageError').hide();
                    }
                } else {
                    $('#imageError').hide();
                }


                if (!isValid) {
                    e.preventDefault();
                }
            });

            <?php if (!empty($edit_message)): ?>
                    showToast("<?= $edit_message ?>", <?= $edit_success ? 'false' : 'true' ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>