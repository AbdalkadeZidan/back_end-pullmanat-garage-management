<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method is allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$requiredFields = [
    'departure_city',
    'destination_city',
    'trip_date',
    'trip_time',
    'trip_price',
    'bus_namber',
    'national_id'
];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "$field is required"
        ]);
        exit;
    }
}

$departure_city = $data['departure_city'];
$destination_city = $data['destination_city'];
$trip_date = $data['trip_date'];
$trip_time = $data['trip_time'];
$trip_price = $data['trip_price'];
$bus_namber = $data['bus_namber'];
$national_id = $data['national_id'];

include "../config/connected.php";

try {

    $stmt = $pdo->prepare("
        INSERT INTO trips (
            departure_city,
            destination_city,
            trip_date,
            trip_time,
            trip_price,
            bus_namber,
            national_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $departure_city,
        $destination_city,
        $trip_date,
        $trip_time,
        $trip_price,
        $bus_namber,
        $national_id
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Trip added successfully",
        "trip_id" => $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}