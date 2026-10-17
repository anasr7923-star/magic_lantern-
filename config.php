<?php
// config.php
define('DB_SERVER', '://clever-cloud.com'); // الخادم الظاهر في الصورة
define('DB_USERNAME', 'uzo6fvba8w5bhwln'); // المستخدم الظاهر في الصورة
define('DB_PASSWORD', 'ضع_كلمة_السر_الخاصة_بالقاعدة_هنا'); // كلمة السر المستخرجة من السيرفر
define('DB_NAME', 'bw4jlsdfrulsmwystuqp'); // اسم قاعدة البيانات المكتوب فوق phpMyAdmin
define('DB_PORT', '3306'); // المنفذ القياسي للـ MySQL

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
