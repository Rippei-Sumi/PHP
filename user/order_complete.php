<?php
session_start();
require 'db.php';

/* ------------------------------
   ① 注文直後の場合（cartあり）
------------------------------ */
if (!empty($_SESSION['cart'])) {

    $cart = $_SESSION['cart'];
    $receiving_time = $_SESSION['pickup_time'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $note = $_POST['note'] ?? '';

    if (!$user_id) {
        exit('ログインしてください');
    }

    $total = 0;
    $shop_id = null;

    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
        $shop_id = $item['shop_id'];
    }

    /* ===============================
       トランザクション開始
    =============================== */
    $pdo->beginTransaction();

    try {

        /* ---------- category取得 ---------- */
        $stmt_cat = $pdo->prepare("
            SELECT c.id AS category_id, c.prefix
            FROM shops s
            JOIN categories c ON s.category_id = c.id
            WHERE s.id = :shop_id
        ");

        $stmt_cat->execute([
            ':shop_id' => $shop_id
        ]);

        $data = $stmt_cat->fetch(PDO::FETCH_ASSOC);

        $category_id = $data['category_id'];
        $prefix = $data['prefix'];

        /* ===================================
           連番取得（トランザクション内）
        =================================== */
        $stmt_max = $pdo->prepare("
            SELECT MAX(order_number)
            FROM orders
            WHERE order_number LIKE :prefix
            FOR UPDATE
        ");
        $stmt_max->execute([
            ':prefix' => $prefix . '%'
        ]);

        $max_order_number = $stmt_max->fetchColumn();

        if ($max_order_number) {
            $number = (int)substr($max_order_number, 1);
            $number++;
        } else {
            $number = 1;
        }

        $order_number = $prefix . sprintf('%04d', $number);

        $order_time = date('Y/m/d H:i');

        /* ---------- orders INSERT ---------- */
        $stmt = $pdo->prepare("
            INSERT INTO orders
            (user_id, shop_id, order_time, receiving_time, order_number, note, is_completed)
            VALUES
            (:user_id, :shop_id, :order_time, :receiving_time, :order_number, :note, 0)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':shop_id' => $shop_id,
            ':order_time' => $order_time,
            ':receiving_time' => $receiving_time,
            ':order_number' => $order_number,
            ':note' => $note
        ]);

        $order_id = $pdo->lastInsertId();

        /* ---------- order_detail INSERT ---------- */
        $stmt_detail = $pdo->prepare("
            INSERT INTO order_detail
            (order_id, product_id, amount, price_at_order)
            VALUES
            (:order_id, :product_id, :amount, :price)
        ");

        foreach ($cart as $item) {
            $stmt_detail->execute([
                ':order_id' => $order_id,
                ':product_id' => $item['product_id'],
                ':amount' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }

        $pdo->commit();

        /* カート削除 */
        unset($_SESSION['cart']);
        unset($_SESSION['pickup_time']);

        header("Location: order_complete.php?order_number=" . urlencode($order_number));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo $e->getMessage();
        exit;
    }

/* ------------------------------
   ② ホームから来た場合
------------------------------ */
} else {

    $order_number = $_GET['order_number'] ?? '';

    if (!$order_number) {
        exit('注文情報がありません');
    }

    $stmt = $pdo->prepare("
        SELECT * FROM orders
        WHERE order_number = :order_number
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':order_number' => $order_number,
        ':user_id' => $_SESSION['user_id']
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        exit('注文が見つかりません');
    }

    $note = $order['note'];
    $order_id = $order['id'];
    $receiving_time = $order['receiving_time'];
    $order_number = $order['order_number'];

    $stmt_detail = $pdo->prepare("
        SELECT od.*, 
               p.product_name,
               s.category_id
        FROM order_detail od
        JOIN products p ON od.product_id = p.id
        JOIN shops s ON p.shop_id = s.id
        WHERE od.order_id = :order_id
    ");

    $stmt_detail->execute([':order_id' => $order_id]);
    $cart = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
}

/* ------------------------------
   ③ 受け取り完了ボタン
------------------------------ */
if (isset($_POST['complete_order'])) {

    $order_id = $_POST['order_id'] ?? null;
    $user_id  = $_SESSION['user_id'] ?? null;

    if (!$order_id || !$user_id) {
        exit('不正なアクセスです');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE orders
            SET is_completed = 1
            WHERE id = :order_id
            AND user_id = :user_id
        ");

        $stmt->execute([
            ':order_id' => $order_id,
            ':user_id' => $user_id
        ]);

        header("Location: user_home.php");
        exit;

    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>注文完了</title>
<style>
body{
  margin:0;
  background:#1e647f;
  color:#fff;
  font-family:Meiryo;
  text-align:center;
}

.container{
  margin-top:100px;
}

.success-box{
  background:#a8e6a3;
  color:#0b4d0b;
  padding:20px;
  font-size:24px;
  margin-bottom:40px;
}

.order-number{
  font-size:60px;
  margin:20px 0;
}

.btn{
  margin-top:40px;
  padding:15px 60px;
  font-size:20px;
  background:#eee;
  border:none;
  cursor:pointer;
}
</style>
</head>
<body>

<div class="container">

<div class="success-box">
注文を承りました。<br>
受取時間になりましたら、該当店舗までお越しください。
</div>

<div>
注文番号：
<div class="order-number">
<?= htmlspecialchars($order_number) ?>
</div>
</div>

<div>
受取時間：<?= htmlspecialchars($receiving_time) ?>
</div>

<h2 style="margin-top:40px;">注文内容</h2>

<?php foreach ($cart as $item): ?>
<p>
<?php
$unit = ($item['category_id'] ?? 0) == 3 ? '回' : '個';
?>

<?= htmlspecialchars($item['product_name']) ?>
× <?= $item['amount'] . $unit ?>
</p>
<?php endforeach; ?>

<?php if (!empty($note)): ?>
<div style="margin-top:30px;">
  <h3>特記事項</h3>
  <div style="white-space:pre-line;">
    <?= htmlspecialchars($note) ?>
  </div>
</div>
<?php endif; ?>

<button class="btn" onclick="location.href='user_home.php'">
ホーム画面へ
</button>

</div>

<form method="post" 
      style="position:fixed; bottom:30px; right:30px;"
      onsubmit="return confirmComplete();">

    <input type="hidden" name="order_id" 
           value="<?= htmlspecialchars($order_id) ?>">

    <button type="submit" name="complete_order"
        style="
            padding:15px 30px;
            font-size:18px;
            background:#ffcc00;
            border:none;
            cursor:pointer;
            border-radius:8px;
        ">
        受け取りました
    </button>
</form>

<script>
function confirmComplete() {
    return confirm("本当に受け取りましたか？\nこの操作は取り消せません。");
}
</script>


</body>
</html>
