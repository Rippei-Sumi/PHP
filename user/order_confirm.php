<?php
session_start();

$cart = $_SESSION['cart'] ?? [];
$pickup_time = $_SESSION['pickup_time'] ?? '';
$total = 0;

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

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
<title>注文確認</title>

<style>
body {
  margin: 0;
  background-color: #155f7f;
  color: #fff;
  font-family: Meiryo;
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

.container {
  margin-top: 120px;
  padding: 40px;
}

.title-box {
  background: #a8e6a3;
  color: #0b4d0b;
  font-size: 24px;
  text-align: center;
  padding: 15px;
  margin-bottom: 40px;
  font-weight: bold;
}

.order-item {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:20px;
  border: 2px solid #fff;
  border-radius: 15px;
  padding: 20px;
  margin-bottom: 20px;
}

.order-left {
  font-size: 20px;
}

.order-right {
  font-size: 20px;
}

.item-img {
  width: 120px;
  height: 120px;       /* 高さ固定 */
  object-fit: cover;   /* はみ出しをトリミング */
  border-radius: 16px;
  background-color: #fff;
}

.total-box {
  text-align: center;
  margin-top: 30px;
  font-size: 24px;
}

textarea {
  width: 100%;
  height: 120px;
  margin-top: 30px;
  padding: 10px;
  font-size: 16px;
}

.btn-area {
  display: flex;
  justify-content: center;
  gap: 50px;
  margin-top: 40px;
}

.btn {
  padding: 15px 60px;
  font-size: 20px;
  background: #eee;
  border: none;
  cursor: pointer;
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

<div class="container">

<div class="title-box">
以下の内容で注文を完了してもよろしいですか？
</div>

<?php foreach ($cart as $item): 
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;

    // 単位切り替え
    $unit = ($item['category_id'] ?? 0) == 3 ? '回' : '個';
?>
<div class="order-item">

<img class="item-img"
     src="img/<?= htmlspecialchars($item['image']) ?>"
     onerror="this.src='images/no_image.png'">

<div class="order-left">
  <?= htmlspecialchars($item['product_name']) ?><br>
  <?= $item['quantity'] . $unit ?>
</div>

<div class="order-right">
  <?= number_format($subtotal) ?>円
</div>

</div>
<?php endforeach; ?>

<div class="total-box">
合計：<?= number_format($total) ?>円
</div>

<?php if ($pickup_time): ?>
<div style="text-align:center; margin-top:20px;">
受取時間：<?= htmlspecialchars($pickup_time) ?>
</div>
<?php endif; ?>

<form action="order_complete.php" method="post">
<textarea name="note" placeholder="特記事項"></textarea>

<div class="btn-area">
  <button type="submit" class="btn">完了する</button>
  <button type="button" class="btn" onclick="location.href='cart.php'">
    カートに戻る
  </button>
</div>
</form>

</div>
</body>
</html>
