<?php
session_start();

$product_id = $_POST['product_id'] ?? null;
$quantity   = $_POST['quantity'] ?? 1;

if (!$product_id || !isset($_SESSION['cart'][$product_id])) {
    header('Location: cart.php');
    exit;
}

$_SESSION['cart'][$product_id]['quantity'] = max(1, (int)$quantity);

header('Location: cart.php');
exit;
