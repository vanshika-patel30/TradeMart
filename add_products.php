<?php
require 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$add_success = null;
$add_message = "";

$seller_id = $user_id;

$categories = array();
$category_result = $conn->query("SELECT * FROM product_categories");
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image = $_FILES['image'];

    $stmt = $conn->prepare("SELECT category_name FROM product_categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($category_name);
    $stmt->fetch();
    $stmt->close();

    $display_folder = str_replace("_", " ", $category_name);
    $safe_folder = str_replace("'", "", $display_folder);
    $actual_folder = "products/" . str_replace(" ", "_", $safe_folder) . "/";
    $db_folder = $actual_folder;

    $image_name = basename($image["name"]);
    $image_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
    $new_image_name = $image_name;
    $target_file = $actual_folder . $new_image_name;
    $db_path = $db_folder . $new_image_name;

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed_types)) {
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, image, category_id, seller_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $product_name, $description, $price, $db_path, $category_id, $seller_id);
            if ($stmt->execute()) {
                $add_success = true;
                $add_message = "Product added successfully!";
            } else {
                $add_success = false;
                $add_message = "Failed to add product!";
                if(file_exists($target_file)) {
                    unlink($target_file);
                }
            }
            $stmt->close();
        } else {
            $add_success = false;
            $add_message = "Failed to add product!";
        }
    } else {
        $add_success = false;
        $add_message = "Failed to add product!";
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products</title>
    <link rel="stylesheet" href="add_products.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="toast" class="add-product-toast"></div>

    <div class="add-modal" id="add-modal">
        <a href="seller_dashboard.php" class="back-dashboard"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
        <h1>ADD PRODUCT</h1>
            <form id="add-form" method="POST" action="" enctype="multipart/form-data">
            <div class="form-info">
                <label class="label">Product Name:</label>
                <input type="text" name="product_name" id="product_name" required>
                <span class="error" id="nameError">Please enter a valid name!</span>
            </div>
            <div class="form-info">
                <label class="label">Description:</label>
                <textarea name="description" id="description" required></textarea>
                <span class="error" id="descriptionError">Please enter a valid description!</span>
            </div>
            <div class="form-info">
                <label class="label">Price:</label>
                <input type="number" step="0.01" name="price" id="price" required>
                <span class="error" id="priceError">Please enter a valid price!</span>
            </div>
            <div class="form-info">
                <label class="label">Category:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="error" id="categoryError">Please select a category!</span>
            </div>
            <div class="form-info">
                <label class="label">Image:</label>
                <input type="file" name="image" id="image" accept="image/*" required>
                <span class="error" id="imageError">Please upload a valid image!</span>
            </div>
            <button type="submit">Add Product</button>
        </form>

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
                $('#add-form').on('submit', function (e) {
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

                <?php if (!empty($add_message)): ?>
                    showToast("<?= $add_message ?>", <?= $add_success ? 'false' : 'true' ?>);
                <?php endif; ?>
            });
        </script>
</body>
</html>