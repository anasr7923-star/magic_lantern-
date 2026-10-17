<?php
// auth/login.php

// 1. السماح بمرور البيانات من جميع الهواتف (CORS Headers) لمنع أخطاء الاتصال
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. استدعاء ملف التوثيق والاتصال بالسيرفر config.php التراكمي
require_once '../config.php';

// 3. التأكد من أن الطلب القادم من الموبايل هو من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "طريقة الطلب غير مسموح بها. يجب استخدام POST."]);
    exit();
}

// 4. استقبال حزمة البيانات النصية JSON القادمة من الهاتف وفك تشفيرها
$data = json_decode(file_get_contents("php://input"), true);

// 5. التحقق الصارم من وجود الحقول الإلزامية لتسجيل الدخول
if (empty($data['phone_number']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "فشل الدخول! يرجى إدخال رقم الهاتف وكلمة المرور."]);
    exit();
}

// 6. تأمين البيانات وتنقيتها لمنع ثغرات الـ SQL Injection
$phoneNumber = mysqli_real_escape_string($link, $data['phone_number']);
$password    = $data['password'];

// 7. الاستعلام التراكمي الشامل عن المستخدم وحالة مستنداته وتواريخ صلاحيتها
$query = "SELECT u.user_id, u.full_name, u.password_hash, u.account_type, u.is_active, u.national_id_expiry_date 
          FROM users u 
          WHERE u.phone_number = '$phoneNumber' LIMIT 1";

$result = mysqli_query($link, $query);

if (mysqli_num_rows($result) === 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "عذراً، رقم الهاتف هذا غير مسجل بنظام الفانوس السحري."]);
    exit();
}

$user = mysqli_fetch_assoc($result);

// 8. مطابقة كلمة المرور المدخلة مع الهاش المشفر (BCRYPT) المخزن سابقاً بالقاعدة
if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "كلمة المرور التي أدخلتها غير صحيحة، يرجى إعادة المحاولة."]);
    exit();
}

// 9. فحص الامتثال الأمني والتواريخ (هل الحساب موقوف تلقائياً بسبب المستندات؟)
$daysRemaining = null;
$shouldBlock = false;

if (!empty($user['national_id_expiry_date'])) {
    $expiryDate = new DateTime($user['national_id_expiry_date']);
    $today = new DateTime();
    $interval = $today->diff($expiryDate);
    $daysRemaining = (int)$expiryDate->diff($today)->format("%r%a"); // احتساب الأيام المتبقية بدقة
    
    // إذا كان التاريخ منتهياً أو متبقي أقل من يومين، يتم وسم الحساب بالإيقاف الإجباري لحين رفع المستندات الجديدة
    if ($daysRemaining <= 2) {
        $shouldBlock = true;
        // تحديث حالة النشاط تلقائياً في السيرفر لتوثيق الحظر
        mysqli_query($link, "UPDATE users SET is_active = 0 WHERE user_id = " . $user['user_id']);
        $user['is_active'] = 0;
    }
}

// 10. صياغة الرد النهائي المكتمل لإرساله لتطبيق الـ Flutter على الهاتف
http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "تم تسجيل الدخول والتحقق الأمني بنجاح من السيرفر السحابي.",
    "user_data" => [
        "user_id" => (int)$user['user_id'],
        "full_name" => $user['full_name'],
        "account_type" => $user['account_type'],
        "is_active" => (int)$user['is_active'] === 1 ? true : false,
        "days_remaining" => $daysRemaining,
        "should_force_update" => $shouldBlock
    ]
]);

// إغلاق الاتصال بأمان
mysqli_close($link);
?>
