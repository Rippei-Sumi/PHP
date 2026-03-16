<?php
session_start();
if (!isset($_SESSION['shop_id'])) {
    header('Location: shop_login.php');
    exit;
}

require 'db.php';



/* -------------------------
   未受取注文取得
------------------------- */
$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE shop_id = :shop_id
    AND is_completed = 0
    ORDER BY receiving_time ASC
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
<title>ホーム画面（店舗用）</title>

<style>
body{
    margin:0;
    background:#1e647f;
    font-family:Meiryo;
    color:#fff;
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

.container{
    padding:40px;
    margin-top:90px; /* ← これを追加 */
}

.order-box{
    background:#ddd;
    color:#000;
    padding:20px;
    margin-bottom:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.order-info {
    width: 80%;
    display: flex;
    justify-content: space-between;
}

.order-left {
    width: 60%;
}

.order-right {
    width: 35%;
}

.order-number{
    font-size:24px;
    margin-bottom:10px;
}

.complete-btn{
    padding:10px 20px;
    font-size:18px;
    background:#fff;
    border:2px solid #000;
    text-decoration:none;
    color:#000;
    cursor:pointer;
}


.order-list{
  text-align: center;
  font-size: 30px;
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

<div class='order-list'>注文一覧</div>
<?php foreach ($orders as $order): ?>

<?php
/* 注文詳細取得 */
$stmt_detail = $pdo->prepare("
    SELECT od.*, p.product_name, s.category_id
    FROM order_detail od
    JOIN products p ON od.product_id = p.id
    JOIN shops s ON p.shop_id = s.id
    WHERE od.order_id = :order_id
");
$stmt_detail->execute([':order_id' => $order['id']]);
$details = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="order-box">

<div class="order-info">

  <div class="order-left">
    <div class="order-number">
      注文番号：<?= htmlspecialchars($order['order_number']) ?>
    </div>

    <div>
      受取時間：<?= htmlspecialchars($order['receiving_time']) ?>
    </div>

    <br>

    <?php foreach ($details as $item): ?>

<?php
  $unit = ($item['category_id'] == 3) ? '回' : '個';
?>

<div>
  <?= htmlspecialchars($item['product_name']) ?>
  × <?= $item['amount'] . $unit ?>
</div>

<?php endforeach; ?>
  </div>

  <?php if (!empty($order['note'])): ?>
  <div class="order-right" style="background:#fff3cd; padding:10px;">
      <strong>特記事項</strong><br>
      <div style="white-space:pre-line;">
        <?= htmlspecialchars($order['note']) ?>
      </div>
  </div>
  <?php endif; ?>

</div>

<a href="takeout_confirm.php?order_number=<?= urlencode($order['order_number']) ?>"
   class="complete-btn">
完了
</a>



</div>

<?php endforeach; ?>

</div>

</body>
</html>
