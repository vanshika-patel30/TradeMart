<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';


if ($action === 'add') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $product_name = $_POST['product_name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE buyer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + 1;
        $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE buyer_id = ? AND product_id = ?");
        $update_stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
        $update_stmt->execute();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO cart_items (buyer_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert_stmt->bind_param("ii", $user_id, $product_id);
        $insert_stmt->execute();
    }

    $cart_query = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE buyer_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_count = $cart_result->fetch_assoc()['total'] ?? 0;

    echo json_encode(['status' => 'added', 'cart_count' => $cart_count]);
    exit;
}

if ($action === 'count') {
    $cart_query = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE buyer_id = ?");
    $cart_query->bind_param("i", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    $cart_count = $cart_result->fetch_assoc()['total'] ?? 0;
    echo $cart_count;
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>