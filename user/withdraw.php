<?php
session_start();
require 'db.php';

/* =========================
   ① ログイン確認
========================= */
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   ② 未受取注文チェック
========================= */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE user_id = :user_id
      AND is_completed = 0
");
$stmt->execute([':user_id' => $user_id]);
$order_count = $stmt->fetchColumn();

if ($order_count > 0) {
    exit('未受取の注文があるため退会できません。');
}

/* =========================
   ③ 退会処理
========================= */
$pdo->beginTransaction();

try {

    // ユーザー削除
    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE id = :id
    ");
    $stmt->execute([':id' => $user_id]);

    $pdo->commit();

    // セッション破棄
    $_SESSION = [];
    session_destroy();

    header('Location: user_login.php?withdraw=1');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();
    echo "エラーが発生しました。";
    exit;
}