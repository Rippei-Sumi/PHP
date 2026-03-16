<?php
session_start();
$cart = $_SESSION['cart'] ?? [];

require 'db.php';

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

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>カートの中身</title>
<style>
body {
  background-color: #155f7f;
  color: #fff;
  font-family: Meiryo;
  margin: 0;
  padding-top: 90px; /* ← これを追加 */
}

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

.cart-item {
  display: flex;
  align-items: center;
  gap: 30px;
  border: 2px solid #fff;
  border-radius: 20px;
  padding: 20px;
  margin: 30px;
}

.cart-item img {
  width: 140px;
  border-radius: 10px;
}

.actions a {
  color: #fff;
  margin-left: 20px;
  text-decoration: underline;
}

.order-btn {
  display: block;
  width: 260px;
  margin: 40px auto 60px;   /* 中央寄せ */
  padding: 18px 0;

  background-color: #ffcc00;
  color: #000;
  text-align: center;
  font-size: 22px;
  font-weight: bold;
  text-decoration: none;

  border-radius: 40px;
  box-shadow: 0 6px 0 #d4aa00;
  transition: 0.15s;
}

.order-btn:hover {
  background-color: #ffd633;
}

.order-btn:active {
  transform: translateY(4px);
  box-shadow: 0 2px 0 #d4aa00;
}

.back {
  position: fixed;
  bottom: 30px;
  left: 30px;
  font-size: 26px;
  cursor: pointer;
}

.cart-item img {
  width: 120px;
  height: 120px;       /* 高さ固定 */
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

<h1>カートの中身</h1>

<?php if (empty($cart)): ?>
  <p>カートは空です</p>
<?php endif; ?>

<?php foreach ($cart as $item): ?>
  <div class="cart-item">
    <img src="img/<?= htmlspecialchars($item['image'] ?? 'no_image.png') ?>">
    <div>
    <?= htmlspecialchars($item['product_name']) ?>

<?php
$unit = ($item['category_id'] ?? 0) == 3 ? '回' : '個';
?>

<?= $item['quantity'] . $unit ?>
    </div>
    <div class="actions">
      <a href="quantity_change.php?id=<?= $item['product_id'] ?>">数量変更</a>
      <a href="cart_delete.php?id=<?= $item['product_id'] ?>">削除</a>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!empty($cart)): ?>
  <a href="select_time.php" class="order-btn">注文へ</a>
<?php else: ?>
  <div class="order-btn" style="opacity:0.5; pointer-events:none;">
    注文へ
  </div>
<?php endif; ?>
<div class="back" onclick="history.back()">↩ 数量選択画面へ戻る</div>

</body>
</html>
