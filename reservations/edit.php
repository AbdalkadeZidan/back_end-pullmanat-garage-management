<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only PUT method is allowed"
    ]);
    exit;
}

include "../config/connected.php";

// قراءة بيانات PUT
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من res_id
if (!isset($data['res_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "id is required"
    ]);
    exit;
}

$res_id = $data['res_id'];
$user_id = $data['user_id'];
$trip_id = $data['trip_id'];
$res_time = $data['res_time'];
$res_status = $data['res_status'];
$seat = $data['seat'];

try {

    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET user_id = :user_id,
            trip_id = :trip_id,
            res_time = :res_time,
            res_status = :res_status,
            seat = :seat
        WHERE res_id = :res_id
    ");

    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':res_time', $res_time);
    $stmt->bindParam(':res_status', $res_status);
    $stmt->bindParam(':seat', $seat);
    $stmt->bindParam(':res_id', $res_id);

    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Reservation updated successfully"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}