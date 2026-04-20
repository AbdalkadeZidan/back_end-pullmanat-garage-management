<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// السماح فقط PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only PUT method allowed"
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

// التحقق من station_id
if (!isset($data['station_id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "station_id is required"
    ]);
    exit;
}

$station_id = $data['station_id'];

// القيم (إذا ما انبعثت نخليها نفسها)
$city_name = $data['city_name'] ?? null;
$station_name = $data['station_name'] ?? null;
$station_location = $data['station_location'] ?? null;

try {

    // جلب البيانات الحالية
    $stmt = $pdo->prepare("SELECT * FROM departure_points WHERE station_id = :id");
    $stmt->execute([':id' => $station_id]);

    $station = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$station) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Station not found"
        ]);
        exit;
    }

    // تجهيز القيم (إذا ما انبعتت نخلي القديم)
    $city_name = $city_name ?? $station['city_name'];
    $station_name = $station_name ?? $station['station_name'];
    $station_location = $station_location ?? $station['station_location'];

    // التحديث
    $update = $pdo->prepare("
        UPDATE departure_points 
        SET city_name = :city_name,
            station_name = :station_name,
            station_location = :station_location
        WHERE station_id = :id
    ");

    $update->execute([
        ':city_name' => $city_name,
        ':station_name' => $station_name,
        ':station_location' => $station_location,
        ':id' => $station_id
    ]);

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Station updated successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}