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
    !isset($data['res_id']) ||
    !isset($data['payment_method']) ||
    !isset($data['amount_paid'])
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

$res_id = $data['res_id'];
$payment_method = $data['payment_method'];
$amount_paid = $data['amount_paid'];

try {

    $stmt = $pdo->prepare("
        INSERT INTO payments (
            res_id,
            payment_method,
            amount_paid,
            payment_date,
            payment_status
        )
        VALUES (
            :res_id,
            :payment_method,
            :amount_paid,
            NOW(),
            :payment_status
        )
    ");

    $stmt->execute([
        ':res_id' => $res_id,
        ':payment_method' => $payment_method,
        ':amount_paid' => $amount_paid,
        ':payment_status' => 'paid'
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Payment completed successfully"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}