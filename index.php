<?php
require_once __DIR__ . '/config.php';
echo json_encode(["status"=>"ok","message"=>"Magic Lantern API Running","time"=>date("Y-m-d H:i:s")]);