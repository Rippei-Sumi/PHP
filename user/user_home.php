<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: user_login.php');
  exit;
}

require 'db.php';

/* =========================
   ナビ用カテゴリー取得
========================= */
$stmt = $pdo->query("
  SELECT id, category, description
  FROM categories
  ORDER BY id
");

$nav_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   未受取注文取得
========================= */
$stmt = $pdo->prepare("
  SELECT order_number
  FROM orders
  WHERE user_id = :user_id
  AND is_completed = 0
  ORDER BY id DESC
  LIMIT 5
");

$stmt->execute([
  ':user_id' => $_SESSION['user_id']
]);

$current_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* 件数カウント */
$order_count = count($current_orders);
$limit_reached = ($order_count >= 5);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ホーム画面（来場者用）</title>

<style>

body {
  margin: 0;
  font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
  background-color: #155f7f;
  color: #fff;
}

/* ===== ナビ ===== */
.nav {
  background-color: #000;
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.disabled-link {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ===== メイン ===== */
.main {
  padding: 40px;
  margin-top: 80px;
  position: relative;
}

/* 注文番号 */
.order-number {
  background-color: red;
  padding: 10px 20px;
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 10px;
}

.order-number a {
  color: #fff;
  text-decoration: none;
}

/* メニュー */
.menu-area {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  margin-top: 40px;
  max-width: 900px;
}

.menu-box {
  background-color: #cfd8dc;
  color: #000;
  width: 220px;
  padding: 20px;
}

.menu-box h3 {
  margin-top: 0;
}

/* マップ */
.map-wrapper {
  text-align: center;
  margin-top: 60px;
}

.map-title {
  margin-bottom: 20px;
  font-size: 28px;
  font-weight: bold;
}

.map {
  margin: 0 auto;
  background-color: #fff;
  height: 300px;
  width: 900px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 10px;
}

.map img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

</style>
</head>

<body>

<!-- ナビ -->
<div class="nav">

  <div class="nav-title">katachi祭</div>

  <div class="nav-menu">

    <a href="user_home.php">ホーム</a>

<?php foreach ($nav_categories as $c): ?>

<?php if ($limit_reached): ?>

    <span class="disabled-link">
      <?= htmlspecialchars($c['category']) ?>
    </span>

<?php else: ?>

    <a href="shop_list.php?category_id=<?= $c['id'] ?>">
      <?= htmlspecialchars($c['category']) ?>
    </a>

<?php endif; ?>
<?php endforeach; ?>

    <a href="user_edit.php">各種設定</a>
    <a href="logout.php">ログアウト</a>

  </div>

</div>


<div class="main">

<!-- 注文番号 -->
<?php if ($current_orders): ?>

  <div style="position:absolute; top:0; right:40px;">

<?php foreach ($current_orders as $order): ?>

    <div class="order-number">

      <a href="order_complete.php?order_number=<?= urlencode($order['order_number']) ?>">
        注文番号：<?= htmlspecialchars($order['order_number']) ?>
      </a>

    </div>

<?php endforeach; ?>

  </div>

<?php endif; ?>


<?php if ($limit_reached): ?>

  <div style="margin-top:20px;color:#ffcccc;font-weight:bold;">
    現在未受取の注文が5件あります。<br>
    受取完了後に新しい注文が可能になります。
  </div>

<?php endif; ?>


<!-- メニュー説明 -->
<div class="menu-area">

<?php foreach ($nav_categories as $c): ?>

  <div class="menu-box">

    <h3><?= htmlspecialchars($c['category']) ?>メニュー</h3>

    <?= nl2br(htmlspecialchars($c['description'])) ?>

  </div>

<?php endforeach; ?>

</div>


<!-- 会場マップ -->
<div class="map-wrapper">

  <h2 class="map-title">会場マップ</h2>

  <div class="map">
    <img src="venue_map.png">
  </div>

</div>

</div>

</body>
</html>