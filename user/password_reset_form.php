<?php
require 'db.php'; // PDO接続

$token = $_GET['token'] ?? '';

if ($token === '') {
    exit('無効なURLです');
}

/* tokenをhash化 */
$token_hash = hash('sha256', $token);

/* DB照合 & 有効期限チェック */
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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>新しいパスワード設定</title>
</head>
<body>

<h1>新しいパスワードを設定</h1>

<form action="password_update.php" method="post">
  <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

  <div>
    <input type="password" name="password" placeholder="新しいパスワード" required>
  </div>

  <div>
    <input type="password" name="password_confirm" placeholder="パスワード確認" required>
  </div>

  <button type="submit">登録</button>
</form>

</body>
</html>
