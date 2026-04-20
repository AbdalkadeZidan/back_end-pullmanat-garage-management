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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['trip_id']) || !is_numeric($data['trip_id']) || $data['trip_id'] < 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Valid trip_id is required"
    ]);
    exit;
}

$trip_id = (int)$data['trip_id'];

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

    $check = $pdo->prepare("SELECT trip_id FROM trips WHERE trip_id = ?");
    $check->execute([$trip_id]);

    if ($check->rowCount() == 0) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Trip not found"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE trips SET
            departure_city = :departure_city,
            destination_city = :destination_city,
            trip_date = :trip_date,
            trip_time = :trip_time,
            trip_price = :trip_price,
            bus_namber = :bus_namber,
            national_id = :national_id
        WHERE trip_id = :trip_id
    ");

    $stmt->execute([
        'departure_city' => $departure_city,
        'destination_city' => $destination_city,
        'trip_date' => $trip_date,
        'trip_time' => $trip_time,
        'trip_price' => $trip_price,
        'bus_namber' => $bus_namber,
        'national_id' => $national_id,
        'trip_id' => $trip_id
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Trip updated successfully"
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
}

