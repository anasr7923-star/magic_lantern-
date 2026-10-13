<?php
require_once __DIR__ . '/config.php';
$sql = "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, phone VARCHAR(20) UNIQUE, name VARCHAR(100), role VARCHAR(20), otp VARCHAR(10), is_verified TINYINT DEFAULT 0, created_at DATETIME)";
$pdo->exec($sql);
echo json_encode(["status"=>"success","message"=>"Tables created"]);