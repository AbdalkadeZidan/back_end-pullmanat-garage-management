<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// السماح فقط بطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method is allowed"
    ]);
    exit;
}

// استدعاء الاتصال بقاعدة البيانات
include "../config/connected.php";
$data = json_decode(file_get_contents("php://input"), true);

$requiredFields = [
    'company_name',
    'destinations',
    'phone',
    'email',
    'registration_number'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Missing field: $field"
        ]);
        exit;
    }
}

$company_name = $data['company_name'];
$destinations = $data['destinations'];
$phone = $data['phone'];
$email = $data['email'];
$registration_number = $data['registration_number'];

try {
    // تجهيز 
    $sql = "INSERT INTO company (company_name, destinations, phone, email, registration_number)
            VALUES (:company_name, :destinations, :phone, :email, :registration_number)";

    $stmt = $pdo->prepare($sql);

    // ربط القيم
    $stmt->bindParam(':company_name', $company_name);
    $stmt->bindParam(':destinations', $destinations);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':registration_number', $registration_number);

    // تنفيذ
    $stmt->execute();

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Company added successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}