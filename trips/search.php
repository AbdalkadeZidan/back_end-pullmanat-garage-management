<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// التحقق من نوع الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only GET method is allowed"
    ]);
    exit;
}

// التحقق من وجود trip_id
if (!isset($_GET['trip_id']) || !is_numeric($_GET['trip_id']) || $_GET['trip_id'] < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid trip_id is required"
    ]);
    exit;
}

$trip_id = (int)$_GET['trip_id'];

include "../config/connected.php";

try {

    // البحث باستخدام trip_id
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE trip_id = ?");
    $stmt->execute([$trip_id]);

    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Trip not found"
        ]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "data" => $trip
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}