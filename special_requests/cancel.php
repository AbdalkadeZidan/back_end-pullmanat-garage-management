<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// السماح فقط DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only DELETE method allowed"
    ]);
    exit;
}

include "../config/connected.php";

// التحقق من الاتصال
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// التحقق من request_id
if (!isset($data['request_id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "request_id is required"
    ]);
    exit;
}

$request_id = $data['request_id'];

try {

    // التأكد أن الطلب موجود
    $check = $pdo->prepare("SELECT * FROM special_requests WHERE request_id = :id");
    $check->execute([':id' => $request_id]);

    $request = $check->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Request not found"
        ]);
        exit;
    }

    // حذف الطلب
    $stmt = $pdo->prepare("DELETE FROM special_requests WHERE request_id = :id");
    $stmt->execute([':id' => $request_id]);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Request deleted successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}