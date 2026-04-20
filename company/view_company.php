<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
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

// الاتصال بقاعدة البيانات
include "../config/connected.php";

if (!isset($pdo)) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

try {

    // جلب جميع الشركات
    $sql = "SELECT * FROM company";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($companies) > 0) {
        echo json_encode([
            "status" => "success",
            "data" => $companies
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "data" => [],
            "message" => "No companies found"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}