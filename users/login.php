<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// السماح فقط بـ POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method is allowed"
    ]);
    exit;
}

// الاتصال بقاعدة البيانات
include "../config/connected.php";

// قراءة البيانات
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من وجود الحقول
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    // البحث عن المستخدم
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // التحقق
    if ($user) {

        // إذا كنت تستخدم password_hash
        if (password_verify($password, $user['password'])) {

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "data" => [
                    "user_id" => $user['user_id'],
                    "name" => $user['name'],
                    "phone" => $user['phone'],
                    "email" => $user['email'],
                    "image" => $user['image']
                ]
            ]);

        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Incorrect password"
            ]);
        }

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "User not found"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}