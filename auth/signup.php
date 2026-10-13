<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$phone = $_POST['phone'] ?? json_decode(file_get_contents('php://input'), true)['phone'] ?? '';
$name = $_POST['name'] ?? json_decode(file_get_contents('php://input'), true)['name'] ?? 'Client';
$role = $_POST['role'] ?? 'client';

if(empty($phone)){
  echo json_encode(["status"=>"error","message"=>"Phone required"]);
  exit;
}

$otp = rand(1000,9999);

try{
  $stmt = $pdo->prepare("INSERT INTO users (phone, name, role, otp, is_verified, created_at) VALUES (?, ?, ?, ?, 0, NOW()) ON DUPLICATE KEY UPDATE otp=?, name=?");
  $stmt->execute([$phone, $name, $role, $otp, $otp, $name]);
  
  echo json_encode([
    "status"=>"success",
    "message"=>"OTP sent",
    "otp"=>$otp,
    "phone"=>$phone
  ]);
} catch(Exception $e){
  echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}