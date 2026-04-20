<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// التحقق من نوع الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only DELETE method is allowed"
    ]);
    exit;
}

// قراءة البيانات
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من trip_id
if (!isset($data['trip_id']) || !is_numeric($data['trip_id']) || $data['trip_id'] < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid trip_id is required"
    ]);
    exit;
}

$trip_id = (int)$data['trip_id'];

include "../config/connected.php";

try {

    // التحقق من وجود الرحلة
    $check = $pdo->prepare("SELECT trip_id FROM trips WHERE trip_id = ?");
    $check->execute([$trip_id]);

    if ($check->rowCount() == 0) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Trip not found"
        ]);
        exit;
    }

    // الحذف
    $stmt = $pdo->prepare("DELETE FROM trips WHERE trip_id = ?");
    $stmt->execute([$trip_id]);

    echo json_encode([
        "status" => "success",
        "message" => "Trip deleted successfully"
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}