<?php
session_start();
if (!isset($_SESSION['shop_id'])) {
    header('Location: shop_login.php');
    exit;
}

require 'db.php';

/* 完了済み注文取得 */
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE shop_id = :shop_id
    AND is_completed = 1
    ORDER BY receiving_time DESC, order_time DESC
");

$stmt->execute([
    ':shop_id' => $_SESSION['shop_id']
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>完了済み注文</title>
<style>
body{
    margin:0;
    background:#1e647f;
    font-family:Meiryo;
    color:#fff;
}
.container{
    padding:40px;
    margin-top:60px;
}
.order-box{
    background:#ddd;
    color:#000;
    padding:20px;
    margin-bottom:20px;
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

<div class="container">
<h1>完了済み注文</h1>

<?php if (empty($orders)): ?>
<p>完了済みの注文はありません。</p>
<?php endif; ?>

<?php foreach ($orders as $order): ?>

<?php
$stmt_detail = $pdo->prepare("
    SELECT od.*, p.product_name
    FROM order_detail od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = :order_id
");
$stmt_detail->execute([':order_id' => $order['id']]);
$details = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="order-box">

<div>
<strong>注文番号：</strong>
<?= htmlspecialchars($order['order_number']) ?>
</div>

<div>
<strong>受取時間：</strong>
<?= htmlspecialchars($order['receiving_time']) ?>
</div>

<br>

<?php foreach ($details as $item): ?>
<div>
<?= htmlspecialchars($item['product_name']) ?>
× <?= $item['amount'] ?>
</div>
<?php endforeach; ?>

</div>

<?php endforeach; ?>

</div>
</body>
</html>