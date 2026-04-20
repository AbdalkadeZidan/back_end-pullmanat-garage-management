<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// التحقق من الميثود
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method allowed"
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

// التحقق من الحقول
if (
    !isset($data['user_id']) ||
    !isset($data['trip_id']) ||
    !isset($data['res_time']) ||
    !isset($data['res_status']) ||
    !isset($data['seat'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

$user_id = $data['user_id'];
$trip_id = $data['trip_id'];
$res_time = $data['res_time'];
$res_status = $data['res_status'];
$seat = $data['seat'];

try {

    // تحقق إن المقعد رقم صحيح
    if (!is_numeric($seat)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Seat must be a number"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, trip_id, res_time, res_status, seat)
        VALUES (:user_id, :trip_id, :res_time, :res_status, :seat)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':trip_id' => $trip_id,
        ':res_time' => $res_time,
        ':res_status' => $res_status,
        ':seat' => $seat
    ]);

    // نجاح
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Reservation added successfully",
        "seat" => $seat
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}