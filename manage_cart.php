<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';


if ($action === 'update') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($product_id <= 0 || $quantity < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID or quantity']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE buyer_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity']);
    }
    exit;
}

if ($action === 'delete') {
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE buyer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['status' => 'deleted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete item']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>