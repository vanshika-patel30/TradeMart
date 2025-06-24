<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$name = "";

$stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = htmlspecialchars($user["name"]);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role'])) {
    $role = $_POST['role'];
    $sql = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $role, $user_id);
    if ($stmt->execute()) {
        if ($role === "Buyer") {
            header("Location: buyer_dashboard.php");
        } else if ($role === "Seller") {
            header("Location: seller_dashboard.php");
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your User Role</title>
    <link rel="stylesheet" href="role_select.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="background">
        <div id="selectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <img src="logos/small_logo.png" alt="TradeMart Logo" class="modal-logo">
                    <h2>WELCOME TO TRADEMART!</h2>
                    <a href="index.php" class="log-out"><i class="bi bi-arrow-bar-left"></i>Log Out</a>
                </div>
                <h3>Hi, <?php echo $name; ?>!</h3>
                <br>
                <p>Pick your Marketplace Role - <br>Then Let the Trading Begin.</p>
                <form method="POST" action="">
                    <button type="submit" name="role" value="Buyer" class="buyer-btn">Buyer</button>
                    <button type="submit" name="role" value="Seller" class="seller-btn">Seller</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
