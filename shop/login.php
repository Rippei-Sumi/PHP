<?php
session_start();
require 'db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: shop_login.html?error=1');
    exit;
}

/* ユーザー検索 */
$stmt = $pdo->prepare(
    'SELECT id, password
     FROM stores
     WHERE email = :email'
);
$stmt->execute([':email' => $email]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

/* ログイン判定 */
if ($shop && password_verify($password, $shop['password'])) {

    $_SESSION['shop_id'] = $shop['id'];
    $_SESSION['email'] = $email;

    header('Location: shop_home.php');
    exit;

} else {
    // ★ ここでエラー付きで戻す
    header('Location: shop_login.php?error=1');
    exit;
}
