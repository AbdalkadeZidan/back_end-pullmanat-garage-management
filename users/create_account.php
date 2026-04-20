<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method allowed"
    ]);
    exit;
}

include "../config/connected.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON"
    ]);
    exit;
}

// قراءة البيانات
$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? '')); // ✅ تمت إضافته
$phone = trim($data['phone'] ?? '');
$passwordRaw = $data['password'] ?? '';
$image = $data['image'] ?? null;

// التحقق من الحقول
if (empty($name) || empty($email) || empty($phone) || empty($passwordRaw)) {
    echo json_encode([
        "status" => "error",
        "message" => "name, email, phone, password required"
    ]);
    exit;
}

// تحقق من صحة الإيميل
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email format"
    ]);
    exit;
}

// تشفير كلمة المرور
$password = password_hash($passwordRaw, PASSWORD_DEFAULT);

try {

    // التحقق من التكرار
    $check = $pdo->prepare("
        SELECT user_id 
        FROM users 
        WHERE email = :email OR phone = :phone
    ");

    $check->execute([
        ':email' => $email,
        ':phone' => $phone
    ]);

    if ($check->fetch()) {
        echo json_encode([
            "status" => "error",
            "message" => "Email or phone already exists"
        ]);
        exit;
    }

    // إدخال البيانات (🔥 الإيميل مضاف هون)
    $sql = "INSERT INTO users 
        (name, email, phone, password, image, account_creation_date)
        VALUES 
        (:name, :email, :phone, :password, :image, NOW())";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,   // ✅ مهم جدًا
        ':phone' => $phone,
        ':password' => $password,
        ':image' => $image
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "User created successfully"
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}