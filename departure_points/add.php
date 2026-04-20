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
    !isset($data['city_name']) ||
    !isset($data['station_name']) ||
    !isset($data['station_location'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

$city_name = $data['city_name'];
$station_name = $data['station_name'];
$station_location = $data['station_location'];

try {

    $stmt = $pdo->prepare("
        INSERT INTO departure_points (
            city_name,
            station_name,
            station_location
        )
        VALUES (
            :city_name,
            :station_name,
            :station_location
        )
    ");

    $stmt->execute([
        ':city_name' => $city_name,
        ':station_name' => $station_name,
        ':station_location' => $station_location
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Departure point added successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}