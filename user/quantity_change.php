<?php
session_start();

require 'db.php';

if (!isset($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$product_id = $_GET['id'] ?? null;

if (!$product_id || !isset($_SESSION['cart'][$product_id])) {
    exit('商品が見つかりません');
}

$item = $_SESSION['cart'][$product_id];

$stmt = $pdo->prepare(
  'SELECT id, product_name, shop_id, price, image
   FROM products
   WHERE id = :id'
);
$stmt->execute([':id' => $product_id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>数量変更</title>

<style>
body {
  margin: 0;
  padding-top: 80px; /* ← ナビバーの高さ分 */
  background-color: #155f7f;
  color: #fff;
  font-family: Meiryo, sans-serif;
}

/* ナビ */
.nav {
  background-color: #000;
  display: flex;
  justify-content: space-between; /* 左右に分ける */
  align-items: center;            /* 縦中央 */
  padding: 15px 40px;

  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.nav-title {
  font-size: 26px;
  font-weight: bold;
}

.nav-menu {
  display: flex;
  gap: 30px;
}

.nav-menu a {
  color: #fff;
  text-decoration: none;
  font-size: 18px;
}

.nav-menu a:hover {
  text-decoration: underline;
}

/* タイトル */
.title {
  margin: 40px;
  font-size: 32px;
  text-align: center;
}

/* メイン */
.main {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 80px;
}

/* 商品 */
.product {
  text-align: center;
}

.product img {
  width: 240px;
  border-radius: 16px;
  background: #fff;
}

.product-name {
  margin-top: 20px;
  font-size: 24px;
}

.product-price {
  font-size: 20px;
}

/* 数量 */
.qty-area {
  display: flex;
  align-items: center;
  gap: 20px;
}

.qty-btn {
  font-size: 40px;
  width: 80px;
  height: 60px;
  cursor: pointer;
}

.qty-num {
  font-size: 28px;
  width: 60px;
  text-align: center;
}

/* 保存 */
.save-btn {
  display: block;
  margin: 50px auto 0;
  padding: 15px 60px;
  font-size: 22px;
  cursor: pointer;
}

/* 戻る */
.back {
  position: fixed;
  bottom: 30px;
  left: 30px;
  font-size: 26px;
  cursor: pointer;
}

.product img {
  width: 150px;
  height: 150px;       /* 高さ固定 */
  object-fit: cover;   /* はみ出しをトリミング */
  border-radius: 16px;
  background-color: #fff;
}
</style>
</head>
<body>

<div class="nav">
  <div class="nav-title">katachi祭</div>

  <div class="nav-menu">
    <a href="user_home.php">ホーム</a>
    <a href="shop_list.php?category_id=1">Food</a>
    <a href="shop_list.php?category_id=2">Drink</a>
    <a href="shop_list.php?category_id=3">Others</a>
    <a href="user_edit.php">各種設定</a>
    <a href="logout.php">ログアウト</a>
  </div>
</div>


<div class="title">数量変更</div>

<div class="main">
  <!-- 商品 -->
  <div class="product">
  <img src="img/<?= htmlspecialchars($menu['image'] ?? 'no_image.png') ?>"
  onerror="this.src='images/no_image.png'">

    <div class="product-name">
      <?= htmlspecialchars($item['product_name']) ?>
    </div>
    <div class="product-price">
      <?= number_format($item['price']) ?>円
    </div>
  </div>

  <!-- 数量 -->
  <div>
    <div class="qty-area">
      <button class="qty-btn" onclick="changeQty(-1)">−</button>
      <div id="qty" class="qty-num"><?= $item['quantity'] ?></div>
      <button class="qty-btn" onclick="changeQty(1)">＋</button>
    </div>
  </div>
</div>

<form action="quantity_update.php" method="post">
  <input type="hidden" name="product_id" value="<?= $product_id ?>">
  <input type="hidden" name="quantity" id="quantity_input" value="<?= $item['quantity'] ?>">
  <button class="save-btn">変更を保存</button>
</form>

<div class="back" onclick="history.back()">↩ カートへ戻る</div>

<script>
let qty = <?= $item['quantity'] ?>;

function changeQty(val) {
  qty += val;
  if (qty < 1) qty = 1;

  document.getElementById('qty').textContent = qty;
  document.getElementById('quantity_input').value = qty;
}
</script>

</body>
</html>
