<?php
require_once __DIR__ . '/../config.php';
$phone = $_POST['phone'] ?? '01000000000';
$name = $_POST['name'] ?? 'test';
$otp = rand(1000,9999);
try {
  $pdo->prepare("INSERT INTO users (phone,name,role,otp,is_verified,created_at) VALUES (?,?,?,?,0,NOW()) ON DUPLICATE KEY UPDATE otp=VALUES(otp)")->execute([$phone,$name,'client',$otp]);
  echo json_encode(["status"=>"success","otp"=>$otp]);
} catch(Exception $e){ echo json_encode(["status"=>"error","message"=>$e->getMessage()]); }