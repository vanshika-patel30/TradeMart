<?php
require 'db.php';

$pf_payment_id = $_POST['pf_payment_id'] ?? null;
$payment_status = $_POST['payment_status'] ?? null;
$amount_gross = $_POST['amount_gross'] ?? null; 

$user_id = $_POST['custom_str1'] ?? null;
$order_ids = $_POST['custom_str2'] ?? null;
$payment_record_ids = $_POST['custom_str3'] ?? null;

if ($payment_status === 'COMPLETE' && $pf_payment_id && $user_id && $order_ids) {
    $order_id_array = explode(',', $order_ids);
    $payment_record_id_array = $payment_record_ids ? explode(',', $payment_record_ids) : [];

    if (!empty($payment_record_id_array)) {
        foreach ($payment_record_id_array as $payment_record_id) {
            if (is_numeric($payment_record_id)) {
                $update_payment_stmt = $conn->prepare("
                    UPDATE payments
                    SET payfast_payment_id = ?,
                        payment_date = NOW(),
                        payment_amount= ?
                    WHERE payment_id = ?
                ");
                $update_payment_stmt->bind_param("sdi", $pf_payment_id, $amount_gross, $payment_record_id);
                $update_payment_stmt->execute();
            }
        }
    }
    
    foreach ($order_id_array as $order_id) {
        if (is_numeric($order_id)) {
            $update_order_stmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
            $update_order_stmt->bind_param("i", $order_id);
            $update_order_stmt->execute();
        }
    }
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "ERROR";
}
?>