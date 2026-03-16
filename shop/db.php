<?php
try {
  $dsn = 'mysql:host=localhost;dbname=festival_online_order;charset=utf8mb4';
  $user = 'root';
  $pass = ''; // XAMPP初期状態は空

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);

} catch (PDOException $e) {
  exit('DB接続失敗: ' . $e->getMessage());
}
