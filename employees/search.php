<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// السماح فقط GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only GET method allowed"
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

// قراءة JSON (حتى لو GET)
$data = json_decode(file_get_contents("php://input"));

// استخراج employee_id
$employee_id = null;

if (isset($data->employee_id)) {
    $employee_id = $data->employee_id;
} elseif (isset($_GET['employee_id'])) {
    // fallback
    $employee_id = $_GET['employee_id'];
}

// التحقق
if (!isset($employee_id) || !is_numeric($employee_id)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "employee_id is required"
    ]);
    exit;
}

$employee_id = (int)$employee_id;

try {

    $stmt = $pdo->prepare("
        SELECT 
            employee_id,
            employee_name,
            email,
            job,
            company,
            employee_status
        FROM employees
        WHERE employee_id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $employee_id]);

    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Employee not found"
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $employee
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}