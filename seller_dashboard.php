<?php
session_start();

require 'db.php';

$user_id = $_SESSION["user_id"];
$name = "";

$stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = htmlspecialchars($user["name"]);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="seller_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <img src="logos/small_logo.png" alt="TradeMart Logo">
            <h1>SELLER DASHBOARD</h1>
        <a href="role_select.php" class="back-role"><i class="bi bi-arrow-bar-left"></i>Change Role</a>
    </header>

    <div class="container">
        <div class="heading">
            <h2>Seller <?php echo $name; ?>, Choose your activity.</h2>
        </div>

        <div class="grid">
            <div class="grid-card">
                <a href="view_sales.php">
                    <i class="bi bi-cash-coin"></i>
                    View Sales
                </a>
            </div>
            <div class="grid-card">
                <a href="my_products.php">
                    <i class="bi bi-basket3-fill"></i>
                    View My Products
                </a>
            </div>
            <div class="grid-card">
                <a href="add_products.php">
                    <i class="bi bi-cart-plus-fill"></i>
                    Add Products
                </a>
            </div>
            <div class="grid-card">
                <a href="edit_profile.php">
                    <i class="bi bi-person-square"></i>
                    Edit Profile
                </a>
            </div>
            <div class="grid-card">
                <a href="seller_orders.php">
                    <i class="bi bi-box2-fill"></i>
                    Orders
                </a>
            </div>
            <div class="grid-card">
                <a href="mailto:support@trademart.co.za">
                    <i class="bi bi-people-fill"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>