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

include "../config/connected.php";

try {

    // جلب جميع الرحلات
    $stmt = $pdo->prepare("SELECT * FROM trips");
    $stmt->execute();

    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$trips) {
        echo json_encode([
            "status" => "success",
            "message" => "No trips found",
            "data" => []
        ]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "data" => $trips
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}