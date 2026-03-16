<?php
session_start();
require 'db.php';

/* =========================
   ① 入力値チェック
========================= */
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity   = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0) {
    exit('商品が指定されていません');
}

if ($quantity <= 0) {
    $quantity = 1;
}

/* =========================
   ② 商品取得（image追加）
========================= */
$stmt = $pdo->prepare(
  'SELECT p.id,
          p.product_name,
          p.shop_id,
          p.price,
          p.image,            -- ← 追加
          s.category_id
   FROM products p
   JOIN shops s ON p.shop_id = s.id
   WHERE p.id = :id'
);

$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    exit('商品が存在しません');
}

/* =========================
   ③ カート初期化
========================= */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* =========================
   ④ 同一店舗チェック
========================= */
if (!empty($_SESSION['cart'])) {
    $first_item = array_values($_SESSION['cart'])[0];

    if ($first_item['shop_id'] != $product['shop_id']) {
        exit('別店舗の商品は同時に注文できません');
    }
}

/* =========================
   ⑤ カート追加処理
========================= */
if (isset($_SESSION['cart'][$product_id])) {

    $_SESSION['cart'][$product_id]['quantity'] += $quantity;

} else {

    $_SESSION['cart'][$product_id] = [
        'product_id'   => $product['id'],
        'product_name' => $product['product_name'],
        'price'        => $product['price'],
        'quantity'     => $quantity,
        'shop_id'      => $product['shop_id'],
        'category_id'  => $product['category_id'],
        'image'        => $product['image'] ?? 'no_image.png' // ← 追加
    ];
}

/* =========================
   ⑥ カート画面へ
========================= */
header('Location: cart.php');
exit;