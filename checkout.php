<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayFast...</title>
</head>
<body>
    <h2 style="text-align:center;">Redirecting to PayFast for Payment...</h2>

    <form action="https://sandbox.payfast.co.za/eng/process" method="post" id="payfastForm">
        <input type="hidden" name="merchant_id" value="10000100">
        <input type="hidden" name="merchant_key" value="46f0cd694581a">

        <input type="hidden" name="amount" value="<?= number_format($amount, 2, '.', '') ?>">
        <input type="hidden" name="item_name" value="<?= htmlspecialchars($item_name) ?>">
        <input type="hidden" name="custom_str1" value="<?php echo $_SESSION['user_id']; ?>">
        <input type="hidden" name="custom_str2" value="<?php echo implode(',', $order_ids); ?>">
        <input type="hidden" name="return_url" value="<?= $return_url ?>">
        <input type="hidden" name="cancel_url" value="<?= $cancel_url ?>">
        <input type="hidden" name="notify_url" value="<?= $notify_url ?>">

        <noscript><input type="submit" value="Pay with PayFast"></noscript>
    </form>

    <script>
        document.getElementById("payfastForm").submit();
    </script>
</body>
</html>
