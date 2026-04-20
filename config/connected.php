<?php

$host = "localhost";
$dbname = "bulmanat";
$username = "root";
$password = "";

try {
    // إنشاء الاتصال باستخدام PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // تفعيل عرض الأخطاء (مهم للتطوير)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // استخدام fetch بشكل associative
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "فشل الاتصال: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

