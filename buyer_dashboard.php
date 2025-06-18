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
    <link rel="stylesheet" href="buyer_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <img src="logos/small_logo.png" alt="TradeMart Logo">
            <h1>BUYER DASHBOARD</h1>
        <a href="role_select.php" class="back-role"><i class="bi bi-arrow-bar-left"></i>Change Role</a>
    </header>

    <div class="container">
        <div class="heading">
            <h2>Buyer <?php echo $name; ?>, Choose your activity.</h2>
        </div>

        <div class="grid">
            <div class="grid-card">
                <a href="product_catalogue.php">
                    <i class="bi bi-shop-window"></i>
                    View Products
                </a>
            </div>
            <div class="grid-card">
                <a href="edit_profile.php" class="edit-profile">
                    <i class="bi bi-person-square"></i>
                    Edit Profile
                </a>
            </div>
            <div class="grid-card">
                <a href="view_orders.php">
                    <i class="bi bi-bag-check"></i>
                    View Orders
                </a>
            </div>
            <div class="grid-card">
                <a href="track_orders.php">
                    <i class="bi bi-truck"></i>
                    Track Orders
                </a>
            </div>
            <div class="grid-card">
                <a href="view_cart.php">
                    <i class="bi bi-cart"></i>
                    View Cart
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