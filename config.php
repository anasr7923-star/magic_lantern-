<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
$host = getenv('MYSQL_ADDON_HOST');
$db   = getenv('MYSQL_ADDON_DB');
$user = getenv('MYSQL_ADDON_USER');
$pass = getenv('MYSQL_ADDON_PASSWORD');
$port = getenv('MYSQL_ADDON_PORT');
try {
  $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
  $pdo = new PDO($dsn, $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e){
  http_response_code(500);
  echo json_encode(['success'=>false, 'message'=>'DB Error: '.$e->getMessage()]);
  exit;
}
?>
