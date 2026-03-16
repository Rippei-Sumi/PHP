<?php
session_start();
if (!isset($_SESSION['shop_id'])) {
    header('Location: shop_login.php');
    exit;
}

require 'db.php';

$order_number = $_GET['order_number'] ?? '';
$shop_id = $_SESSION['shop_id'];

if (!$order_number) {
    exit('注文番号がありません');
}

/* 注文取得 */
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE order_number = :order_number
    AND shop_id = :shop_id
");
$stmt->execute([
    ':order_number' => $order_number,
    ':shop_id' => $shop_id
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('注文が見つかりません');
}

/* Yes押下処理 */
if (isset($_POST['yes'])) {

    $stmt = $pdo->prepare("
        UPDATE orders
        SET is_completed = 1
        WHERE id = :id
        AND shop_id = :shop_id
    ");
    $stmt->execute([
        ':id' => $order['id'],
        ':shop_id' => $shop_id
    ]);

    header('Location: shop_home.php');
    exit;
}

/* No押下処理 */
if (isset($_POST['no'])) {
    header('Location: shop_home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>商品受け渡し確認</title>

<style>
body{
    margin:0;
    font-family:Meiryo;
}

.header{
    background:#e0e0e0;
    padding:40px;
    font-size:48px;
}

.main{
    background:#1e647f;
    min-height:100vh;
    padding-top:90px;   /* ← これ追加 */
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
}

.message{
    background:#f2c6e8;
    color:#a02080;
    padding:20px 40px;
    font-size:28px;
    margin-bottom:80px;
}

.buttons{
    display:flex;
    gap:150px;
}

button{
    padding:15px 80px;
    font-size:24px;
    background:#ddd;
    border:2px solid #000;
    cursor:pointer;
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
  color: #fff;
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

.confirm{
    text-align: center;
    color : #fff;
    font-size: 30px;
    padding-bottom: 30px;
}
</style>
</head>

<body>

<div class="nav">
  <div class="nav-title">katachi祭</div>

  <div class="nav-menu">
  <a href="shop_home.php">ホーム</a>
  <a href="shop_edit.php">各種設定</a>
  <a href="logout.php">ログアウト</a>
</div>
</div>



<div class="main">

<div class='confirm'>受け渡し確認</div>
<div class="message">
注文番号 <?= htmlspecialchars($order_number) ?> の受け渡しは完了していますか？
</div>

<form method="post" class="buttons">
    <button type="submit" name="yes">Yes</button>
    <button type="submit" name="no">No</button>
</form>

</div>

</body>
</html>
