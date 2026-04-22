<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// السماح فقط POST
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

// التحقق من البيانات
if (
    !isset($data['departure_point']) ||
    !isset($data['arrival_point'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "departure_point and arrival_point are required"
    ]);
    exit;
}

$departure_point = $data['departure_point'];
$arrival_point = $data['arrival_point'];
$notes = $data['notes'] ?? null;

try {

    $stmt = $pdo->prepare("
        INSERT INTO special_requests (
            departure_point,
            arrival_point,
            time,
            date,
            notes
        )
        VALUES (
            :departure_point,
            :arrival_point,
            CURTIME(),
            NOW(),
            :notes
        )
    ");

    $stmt->execute([
        ':departure_point' => $departure_point,
        ':arrival_point' => $arrival_point,
        ':notes' => $notes
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Special request sent successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}