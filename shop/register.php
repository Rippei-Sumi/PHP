<?php
require 'db.php';

/* ① フォームの値を受け取る */
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  exit('未入力があります');
}

/* ② メールアドレス重複チェック */
$sql = "
  SELECT id
  FROM stores
  WHERE email = ?
    AND is_deleted = 0
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

if ($stmt->fetch()) {
  exit('このメールアドレスは既に登録されています');
}

/* ③ パスワードをハッシュ化 */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* ④ DBに登録 */
$sql = "
  INSERT INTO stores (email, password)
  VALUES (?, ?)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $hashedPassword]);

/* ⑤ 完了 */
header('Location:shop_registration_confirm.php');
exit;
