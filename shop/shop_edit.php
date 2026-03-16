<?php
session_start();
require 'db.php';

if (!isset($_SESSION['shop_id'])) {
    header('Location: shop_login.php');
    exit;
}

$shop_id = $_SESSION['shop_id'];

/* ユーザー情報取得 */
$stmt = $pdo->prepare(
    'SELECT email FROM stores WHERE id = :id'
);
$stmt->execute([':id' => $shop_id]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shop) {
    exit('ユーザー情報が取得できません');
}

/* メールアドレス伏字 */
$email = $shop['email'];
$masked_email = preg_replace(
    '/(^.).+(@.+$)/',
    '$1*****$2',
    $email
);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>アカウント情報</title>

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
  text-align: center;
  margin: 60px 0;
  font-size: 36px;
  font-weight: bold;
}

/* ===== 情報 ===== */
.info {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  font-size: 24px;
}

/* ===== ボタン ===== */
.buttons {
  display: flex;
  justify-content: center;
  gap: 120px;
  margin-top: 80px;
}

.btn {
  padding: 20px 60px;
  font-size: 22px;
  border: none;
  cursor: pointer;
}

.btn-green {
  background-color: #5cab2f;
  color: #fff;
}

.btn-red {
  background-color: red;
  color: #fff;
}


</style>
</head>
<body>

<div class="nav">
  <div class="nav-title">katachi祭</div>
  <div class="nav-menu">
    <a href="shop_home.php">ホーム</a>
    <a href="shop_completed.php">完了済み注文</a>
    <a href="shop_edit.php">各種設定</a>
    <a href="logout.php">ログアウト</a>
  </div>
</div>

<div class="title">アカウント情報</div>

<div class="info">
  <div>メールアドレス：</div>
  <div><?= htmlspecialchars($masked_email) ?></div>
</div>

<div class="buttons">
  <form action="email_change.php" method="get">
    <button class="btn btn-green">メールアドレス変更</button>
  </form>

  <form action="password_reset_form.php" method="get">
    <button class="btn btn-green">パスワード変更</button>
  </form>
</div>



</body>
</html>
