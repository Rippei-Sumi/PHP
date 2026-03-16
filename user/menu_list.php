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

$shop_id = $_GET['shop_id'] ?? null;
if (!$shop_id) {
    exit('店舗が指定されていません');
}

/* 店舗名取得 */
$stmt = $pdo->prepare(
    'SELECT shop_name FROM shops WHERE id = :shop_id'
);
$stmt->execute([':shop_id' => $shop_id]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

/* 商品取得 */
$stmt = $pdo->prepare(
    'SELECT id, product_name, shop_id, price
     FROM products
     WHERE shop_id = :shop_id'
);
$stmt->execute([':shop_id' => $shop_id]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($shop['shop_name']) ?> 商品一覧</title>

<style>
body {
  margin: 0;
  background-color: #155f7f;
  font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
  color: #fff;
}

 /* ===== ヘッダー ===== */
 .header {
      background-color: #1a6787;
      padding: 20px 40px;
      font-size: 32px;
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

.title {
  margin-top: 120px;
  text-align: center;
  font-size: 32px;
  font-weight: bold;
}

.menu-list {
  max-width: 900px;
  margin: 40px auto;
}

.menu-item {
  border-bottom: 2px solid #fff;
  padding: 20px 0;
  cursor: pointer;
}

.menu-name {
  font-size: 24px;
}

.menu-price {
  font-size: 20px;
  margin-top: 5px;
}

.menu-desc {
  font-size: 16px;
  opacity: 0.9;
}

.back {
  position: fixed;
  bottom: 30px;
  left: 30px;
  font-size: 28px;
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

<div class="title">
  <?= htmlspecialchars($shop['shop_name']) ?>
</div>

<div class="menu-list">
<?php foreach ($menus as $menu): ?>
  <div class="menu-item"
       onclick="location.href='quantity_select.php?product_id=<?= $menu['id'] ?>'">
       
    <div class="menu-name">
      <?= htmlspecialchars($menu['product_name']) ?>
    </div>

    <div class="menu-price">
      ¥<?= number_format($menu['price']) ?>
    </div>

  </div>
<?php endforeach; ?>
</div>

<div class="back" onclick="history.back()">↩</div>

</body>
</html>
