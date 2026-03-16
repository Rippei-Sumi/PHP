<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

// 未完了注文数チェック
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE user_id = :user_id
    AND is_completed = 0
");
$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$order_count = $stmt->fetchColumn();

if ($order_count >= 5) {
    echo "<script>alert('未受取の注文が5件あります。先に受け取ってください。');location.href='user_home.php';</script>";
    exit;
}

/* =========================
   カテゴリー取得（ナビ用）
========================= */
$stmt = $pdo->query("
    SELECT id, category
    FROM categories
    ORDER BY id
");

$nav_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$product_id = $_GET['product_id'] ?? 0;
if (!$product_id) {
    exit('商品が指定されていません');
}

/* 商品取得（1件） */
$stmt = $pdo->prepare(
    'SELECT id, product_name, shop_id, price, image
     FROM products
     WHERE id = :id'
);
$stmt->execute([':id' => $product_id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    exit('商品が見つかりません');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>数量選択</title>

<style>
body {
  margin: 0;
  background-color: #155f7f;
  font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
  color: #fff;
}

/* ===== ナビ ===== */
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

/* ===== タイトル ===== */
.title {
  margin-top: 120px;
  text-align: center;
  font-size: 32px;
  font-weight: bold;
}

/* ===== メイン ===== */
.main {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 80px;
  margin-top: 40px;
}

/* 商品 */
.product {
  text-align: center;
}

.product img {
  width: 260px;
  border-radius: 16px;
  background-color: #fff;
}

.product-name {
  margin-top: 20px;
  font-size: 22px;
}

.product-price {
  font-size: 20px;
}

/* 数量操作 */
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

/* 小計 */
.subtotal {
  font-size: 22px;
  text-align: center;
}

/* カート */
.cart-btn {
  display: block;
  margin: 60px auto 0;
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
  <?php foreach ($nav_categories as $c): ?>
      <a href="shop_list.php?category_id=<?= $c['id'] ?>">
        <?= htmlspecialchars($c['category']) ?>
      </a>
    <?php endforeach; ?>
  <a href="user_edit.php">各種設定</a>
  <a href="logout.php">ログアウト</a>
</div>
</div>

<div class="title">数量選択</div>

<div class="main">
  <!-- 商品 -->
  <div class="product">
    
  <img src="img/<?= htmlspecialchars($menu['image'] ?? 'no_image.png') ?>"
  onerror="this.src='images/no_image.png'">

    <div class="product-name">
      <?= htmlspecialchars($menu['product_name']) ?>
    </div>
    <div class="product-price">
      <?= number_format($menu['price']) ?>円
    </div>
  </div>

  <!-- 数量 -->
  <div>
    <div class="qty-area">
      <button class="qty-btn" onclick="changeQty(-1)">−</button>
      <div id="qty" class="qty-num">1</div>
      <button class="qty-btn" onclick="changeQty(1)">＋</button>
    </div>

    <div class="subtotal">
      小計<br>
      <span id="subtotal"><?= $menu['price'] ?></span>円
    </div>
  </div>
</div>

<form action="cart_add.php" method="post">
  <input type="hidden" name="product_id" value="<?= $menu['id'] ?>">
  <input type="hidden" name="quantity" id="quantity_input" value="1">
  <button class="cart-btn">カートに入れる</button>
</form>

<div class="back" onclick="history.back()">↩ メニュー画面へ戻る</div>

<script>
let price = <?= $menu['price'] ?>;
let qty = 1;

function changeQty(val) {
  qty += val;
  if (qty < 1) qty = 1;

  document.getElementById('qty').textContent = qty;
  document.getElementById('subtotal').textContent = qty * price;
  document.getElementById('quantity_input').value = qty;
}
</script>

</body>
</html>
