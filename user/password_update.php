<?php
require 'db.php'; // PDO接続

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

/* 基本チェック */
if ($token === '' || $password === '' || $password_confirm === '') {
    exit('不正なリクエストです');
}

/* パスワード一致チェック */
if ($password !== $password_confirm) {
    exit('パスワードと確認用パスワードが一致しません');
}

/* token を hash 化 */
$token_hash = hash('sha256', $token);

/* token & 有効期限チェック */
$stmt = $pdo->prepare(
    'SELECT user_id
     FROM password_resets
     WHERE token_hash = :token_hash
       AND expired_at > NOW()'
);
$stmt->execute([':token_hash' => $token_hash]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    exit('このURLは無効、または有効期限が切れています');
}

$user_id = $reset['user_id'];

/* パスワードを hash 化（超重要） */
$password_hash = password_hash($password, PASSWORD_DEFAULT);

/* users テーブル更新 */
$stmt = $pdo->prepare(
    'UPDATE users
     SET password = :password
     WHERE id = :id'
);
$stmt->execute([
    ':password' => $password_hash,
    ':id' => $user_id
]);

/* token を削除（使い回し防止） */
$stmt = $pdo->prepare(
    'DELETE FROM password_resets
     WHERE token_hash = :token_hash'
);
$stmt->execute([':token_hash' => $token_hash]);

/* 完了画面へ */
header('Location: user_reset_password_complete.html');
exit;
