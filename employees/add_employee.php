<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// التحقق من الحقول
if (
    !isset($data['employee_name']) ||
    !isset($data['email']) ||
    !isset($data['job']) ||
    !isset($data['company']) ||
    !isset($data['password'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

$employee_name = $data['employee_name'];
$email = $data['email'];
$job = $data['job'];
$company = $data['company'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

try {

    $stmt = $pdo->prepare("
        INSERT INTO employees (
            employee_name,
            email,
            job,
            company,
            password,
            employee_status
        )
        VALUES (
            :employee_name,
            :email,
            :job,
            :company,
            :password,
            :employee_status
        )
    ");

    $stmt->execute([
        ':employee_name' => $employee_name,
        ':email' => $email,
        ':job' => $job,
        ':company' => $company,
        ':password' => $password,
        ':employee_status' => 'active'
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Employee added successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}