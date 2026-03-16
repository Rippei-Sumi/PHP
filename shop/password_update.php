<?php
session_start();
require 'db.php';

if (!isset($_SESSION['shop_id'])) {
    exit('ログインしてください');
}

$shop_id = $_SESSION['shop_id'];

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$new_password_confirm = $_POST['new_password_confirm'] ?? '';

/* 入力チェック */
if ($current_password === '' || $new_password === '' || $new_password_confirm === '') {
    exit('未入力項目があります');
}

if ($new_password !== $new_password_confirm) {
    echo "<script>alert('新しいパスワードが一致していません。');location.href='password_reset_form.php';</script>";
    exit;
}

/* 現在のパスワード取得 */
$stmt = $pdo->prepare("
    SELECT password
    FROM stores
    WHERE id = :id
");
$stmt->execute([':id' => $shop_id]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shop) {
    exit('店舗情報が見つかりません');
}

/* 現在パスワード確認 */
if (!password_verify($current_password, $shop['password'])) {
    echo "<script>alert('現在のパスワードが正しくありません。');location.href='password_reset_form.php';</script>";
    exit;
}

/* 新しいパスワードをハッシュ化 */
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

/* 更新 */
$stmt = $pdo->prepare("
    UPDATE stores
    SET password = :password
    WHERE id = :id
");
$stmt->execute([
    ':password' => $new_hash,
    ':id' => $shop_id
]);

echo "<script>alert('パスワードを変更しました。');location.href='shop_home.php';</script>";
    exit;