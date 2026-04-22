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
        "message" => "Only GET method is allowed"
    ]);
    exit;
}

include "../config/connected.php";

// قراءة JSON (حتى لو GET)
$data = json_decode(file_get_contents("php://input"));

// استخراج user_id
$user_id = null;

if (isset($data->user_id)) {
    $user_id = $data->user_id;
} elseif (isset($_GET['user_id'])) {
    // fallback احتياطي
    $user_id = $_GET['user_id'];
}

// التحقق
if (!isset($user_id) || !is_numeric($user_id)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "user_id is required"
    ]);
    exit;
}

$user_id = (int)$user_id;

try {

    $stmt = $pdo->prepare("
        SELECT * 
        FROM reservations
        WHERE user_id = :user_id
        ORDER BY res_id DESC
    ");

    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "count" => count($reservations),
        "data" => $reservations
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}