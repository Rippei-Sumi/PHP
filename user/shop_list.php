<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

/* =========================
   未完了注文数チェック
========================= */
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


/* =========================
   選択されたカテゴリー
========================= */
$category_id = $_GET['category_id'] ?? 1;


/* =========================
   カテゴリー名取得
========================= */
$stmt = $pdo->prepare("
    SELECT category
    FROM categories
    WHERE id = :id
");

$stmt->execute([
    ':id' => $category_id
]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

$category_name = $category['category'] ?? '不明';


/* =========================
   店舗取得
========================= */
$stmt = $pdo->prepare(
    'SELECT id, shop_name, description
     FROM shops
     WHERE category_id = :category_id
       AND is_open = 1'
);

$stmt->execute([':category_id' => $category_id]);

$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>店舗選択</title>

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

/* タイトル */
.title {
  margin-top: 120px;
  margin-bottom: 10px;
  text-align: center;
  font-size: 32px;
  font-weight: bold;
}

/* サブタイトル */
.subtitle {
  text-align: center;
  font-size: 22px;
  margin-bottom: 40px;
}

/* 店舗一覧 */
.shop-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
  padding: 0 60px 60px;
}

/* 店舗カード */
.shop-card {
  border: 3px solid #fff;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: 0.2s;
}

.shop-card:hover {
  background-color: rgba(255,255,255,0.1);
  transform: scale(1.03);
}

.shop-name {
  font-size: 28px;
  margin-bottom: 20px;
}

.shop-desc {
  font-size: 20px;
}

/* 戻る */
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
店舗選択（<?= htmlspecialchars($category_name) ?>）
</div>

<div class="subtitle">
どの店舗で受け取りますか？
</div>

<div class="shop-grid">
<?php foreach ($shops as $shop): ?>
  <div class="shop-card"
       onclick="location.href='menu_list.php?shop_id=<?= $shop['id'] ?>'">
    <div class="shop-name">
      <?= htmlspecialchars($shop['shop_name']) ?>
    </div>
    <div class="shop-desc">
      （<?= htmlspecialchars($shop['description']) ?>）
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="back" onclick="history.back()">↩</div>

</body>
</html>