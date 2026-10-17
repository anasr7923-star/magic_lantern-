<?php
// auth/signup_screen.php

// 1. السماح بمرور البيانات من أي هاتف (CORS Headers) لمنع أخطاء الاتصال بالموبايل
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. استدعاء ملف الاتصال بالسيرفر الموثق config.php التراكمي
require_once '../config.php';

// 3. التأكد من أن الطلب القادم من تطبيق Flutter هو من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "طريقة الطلب غير مسموح بها. يجب استخدام POST."]);
    exit();
}

// 4. استقبال حزمة البيانات النصية JSON القادمة من الهاتف وفك تشفيرها
$data = json_decode(file_get_contents("php://input"), true);

// 5. التحقق الصارم من وجود الحقول الإلزامية لإنشاء الحساب القانوني
if (
    empty($data['full_name']) ||
    empty($data['phone_number']) ||
    empty($data['password_hash']) ||
    empty($data['account_type'])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "فشل التسجيل! يرجى إدخال الحقول الإلزامية بالكامل."]);
    exit();
}

// 6. تنظيف وتأمين البيانات المدخلة لمنع ثغرات اختراق الـ SQL Injection
$fullName   = mysqli_real_escape_string($link, $data['full_name']);
$phoneNumber= mysqli_real_escape_string($link, $data['phone_number']);
$password   = password_hash($data['password_hash'], PASSWORD_BCRYPT); // تشفير كلمة المرور لحماية الخصوصية
$accountType= mysqli_real_escape_string($link, $data['account_type']);

// بيانات المنظومة التأمينية المحدثة للسائق ومقدمي الخدمات
$jobStatus        = isset($data['job_status']) ? mysqli_real_escape_string($link, $data['job_status']) : 'self_employed';
$insuranceStatus  = isset($data['has_existing_insurance']) ? mysqli_real_escape_string($link, $data['has_existing_insurance']) : 'no';
$vehicleCategory  = isset($data['vehicle_category']) ? mysqli_real_escape_string($link, $data['vehicle_category']) : 'private_car';
$createdAt        = date('Y-m-d H:i:s');

// 7. التحقق من عدم تكرار رقم الهاتف في جدول قاعدة البيانات الموثق
$checkPhoneQuery = "SELECT user_id FROM users WHERE phone_number = '$phoneNumber' LIMIT 1";
$phoneResult = mysqli_query($link, $checkPhoneQuery);

if (mysqli_num_rows($phoneResult) > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "عذراً، رقم الهاتف هذا مسجل بالفعل في نظام الفانوس السحري."]);
    exit();
}

// 8. بدء المعاملة المالية والقانونية لإدخال البيانات في جدول المستخدمين الرئيسي (Users)
$insertUserQuery = "INSERT INTO users (full_name, phone_number, password_hash, account_type, created_at, is_active) 
                    VALUES ('$fullName', '$phoneNumber', '$password', '$accountType', '$createdAt', 1)";

if (mysqli_query($link, $insertUserQuery)) {
    // الحصول على المعرف الفرعي التلقائي للمستخدم الذي تم إنشاؤه للتو
    $newUserId = mysqli_insert_id($link);

    // إنشاء المحفظة المالية والأرباح المنفصلة تلقائياً فور نجاح التسجيل (تراكمي مالي)
    $insertWalletQuery = "INSERT INTO wallets (user_id, wallet_type, current_balance, cash_debt_balance) 
                          VALUES ('$newUserId', '{$accountType}_earnings', 0.0, 0.0)";
    mysqli_query($link, $insertWalletQuery);

    // 9. إذا كان الحساب من نوع سائق (driver)، يتم إدخال بياناته التأمينية والوظيفية في الجدول الإضافي
    if ($accountType === 'driver') {
        $insertDriverQuery = "INSERT INTO driver_profiles (driver_id, job_status, has_existing_insurance, vehicle_category, review_status, cash_orders_blocked) 
                              VALUES ('$newUserId', '$jobStatus', '$insuranceStatus', '$vehicleCategory', 'pending', 0)";
        mysqli_query($link, $insertDriverQuery);
        
        // ربط السائق بمحفظة التأمينات المستقلة وتحديد التزام دورة التحصيل الافتراضية
        $insertInsuranceQuery = "INSERT INTO insurance_accounts (driver_id, target_amount, collected_amount, self_charged_amount, period_start, period_end, insurance_status) 
                                 VALUES ('$newUserId', 900.0, 0.0, 0.0, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'pending_review')";
        mysqli_query($link, $insertInsuranceQuery);
    }

    // إرجاع رد النجاح النهائي متوافقاً مع الـ App على الموبايل
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "تم إنشاء الحساب وتوثيق محافظ الأرباح والتأمينات بنجاح في السيرفر السحابي.",
        "user_id" => $newUserId
    ]);
} else {
    // في حال حدوث خطأ تقني غير متوقع بالخادم
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "فشل إدخال البيانات في السيرفر: " . mysqli_error($link)]);
}

// إغلاق الاتصال بالسيرفر بأمان بعد انتهاء التسوية
mysqli_close($link);
?>
