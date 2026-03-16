<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
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

/* 受け取り時間確定 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['pickup_time'] = $_POST['pickup_time'] ?? '';
    header('Location: order_confirm.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>受取時間指定</title>

<style>
body{
  margin:0;
  padding-top:80px;
  background:#1e647f;
  font-family: Meiryo, sans-serif;
  color:#fff;
  text-align:center;
}

/* ===== ナビバー ===== */
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
.title{
  font-size:36px;
  margin-top:40px;
  margin-bottom:40px;
}

/* ===== 時間ボタン ===== */
.time-area{
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:20px;
  max-width:600px;
  margin:0 auto;
}

.time-btn{
  background:#2b7c99;
  border:2px solid #000;
  padding:15px 30px;
  font-size:20px;
  cursor:pointer;
  color:#fff;
}

.time-btn:hover{
  background:#3f8eac;
}

input[type="radio"]{
  display:none;
}

input[type="radio"]:checked + label{
  background:#000;
}

/* ===== 確定ボタン ===== */
.submit-btn{
  margin-top:60px;
  padding:15px 60px;
  font-size:22px;
  background:#ddd;
  border:2px solid #000;
  cursor:pointer;
}

/* ===== 戻る ===== */
.back{
  position:fixed;
  bottom:30px;
  left:30px;
  font-size:22px;
}

.back a{
  color:#fff;
  text-decoration:none;
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

<div class="title">受取時間を指定してください</div>

<form method="post">

<div class="time-area">
<select name="pickup_time" required class="time-select">
    <option value="">-- 時間を選択してください --</option>

    <?php
    for ($h = 17; $h <= 20; $h++) {
        for ($m = 0; $m < 60; $m += 10) {

            // 20:00-20:10は作らない
            if ($h == 20 && $m >= 0) break;

            $start = sprintf('%02d:%02d', $h, $m);

            // 終了時間を計算
            $endTime = strtotime("$start +10 minutes");
            $end = date('H:i', $endTime);

            $label = $start . '-' . $end;

            echo "<option value='$label'>$label</option>";
        }
    }
    ?>
</select>
</div>



<button class="submit-btn">時間を確定</button>

</form>

<div class="back">
  <a href="cart.php">← カートへ戻る</a>
</div>

</body>
</html>
