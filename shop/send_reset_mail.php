<?php
require 'db.php'; // DB接続（PDO想定）

$email = $_POST['email'] ?? '';

if ($email === '') {
    exit('不正なリクエスト');
}

/* ユーザー取得（存在有無は画面に出さない） */
$stmt = $pdo->prepare('SELECT id FROM stores WHERE email = :email');
$stmt->execute([':email' => $email]);
$store = $stmt->fetch(PDO::FETCH_ASSOC);

if ($store) {
    $store_id = $store['id'];

    // トークン生成（32バイト）
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);

    // 有効期限：30分
    $stmt = $pdo->prepare(
        'INSERT INTO password_resets (store_id, token_hash, expired_at)
         VALUES (:store_id, :token_hash, NOW() + INTERVAL 30 MINUTE)'
    );
    $stmt->execute([
        ':store_id' => $store_id,
        ':token_hash' => $token_hash
    ]);

    // 再設定URL
    $reset_url = "http://localhost/password_reset_form.php?token={$token}";

    // メール送信
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $subject = "【Katachi祭】パスワード再設定のご案内";
    $message = "以下のURLから30分以内にパスワードを再設定してください。\n\n{$reset_url}";
    $headers = "From: no-reply@example.com";

    mb_send_mail($email, $subject, $message, $headers);
}

/* 常に同じ画面へ（存在チェック対策） */
header('Location:shop_reset_password_mailed.php');
exit;
