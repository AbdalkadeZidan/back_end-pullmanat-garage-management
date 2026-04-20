<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include "../config/connected.php";
// السماح فقط GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only GET method is allowed"
    ]);
    exit;
}

try {

    // إذا بدك تجيب حجوزات مستخدم معين: ?user_id=1
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

    if ($user_id) {
        $stmt = $conn->prepare("
            SELECT *
            FROM reservations
            WHERE user_id = :user_id
        ");

        $stmt->bindParam(':user_id', $user_id);
    } else {
        $stmt = $pdo->prepare("
            SELECT *
            FROM reservations
        ");
    }

    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "message" => "Reservations fetched successfully",
        "data" => $reservations
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}