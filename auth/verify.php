<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$phone = $data['phone'] ?? '';
$otp = $data['otp'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone=? AND otp=?");
$stmt->execute([$phone, $otp]);
$user = $stmt->fetch();

if($user){
  $pdo->prepare("UPDATE users SET is_verified=1 WHERE phone=?")->execute([$phone]);
  echo json_encode(["status"=>"success","user"=>$user]);
} else {
  echo json_encode(["status"=>"error","message"=>"Invalid OTP"]);
}