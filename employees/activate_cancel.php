<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only PUT method allowed"
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
if (!isset($data['employee_id']) || !isset($data['employee_status'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "employee_id and employee_status are required"
    ]);
    exit;
}

$employee_id = $data['employee_id'];
$status_input = $data['employee_status'];

// تحويل 0 و 1 إلى active / inactive
if ($status_input == 1) {
    $new_status = 'active';
} elseif ($status_input == 0) {
    $new_status = 'inactive';
} else {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "employee_status must be 0 or 1"
    ]);
    exit;
}

try {

    // التأكد أن الموظف موجود
    $check = $pdo->prepare("SELECT employee_id FROM employees WHERE employee_id = :id");
    $check->execute([':id' => $employee_id]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Employee not found"
        ]);
        exit;
    }

    // تحديث الحالة
    $update = $pdo->prepare("
        UPDATE employees 
        SET employee_status = :status 
        WHERE employee_id = :id
    ");

    $update->execute([
        ':status' => $new_status,
        ':id' => $employee_id
    ]);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Employee status updated successfully",
        "new_status" => $new_status
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}