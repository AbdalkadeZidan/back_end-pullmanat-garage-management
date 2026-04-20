<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include "../config/connected.php";

// السماح فقط PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only PUT method is allowed"
    ]);
    exit;
}

try {

    // قراءة البيانات من body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['res_id'])) {
        echo json_encode([
            "status" => "error",
            "message" => "res_id is required"
        ]);
        exit;
    }

    $res_id = $data['res_id'];

    // التحقق إذا الحجز موجود
    $check = $pdo->prepare("SELECT * FROM reservations WHERE res_id = :res_id");
    $check->bindParam(':res_id', $res_id);
    $check->execute();

    if ($check->rowCount() == 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Reservation not found"
        ]);
        exit;
    }

    // تحديث الحالة إلى canceled
    $stmt = $pdo->prepare("
        UPDATE reservations
        SET res_status = 'canceled'
        WHERE res_id = :res_id
    ");

    $stmt->bindParam(':res_id', $res_id);
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Reservation canceled successfully"
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}