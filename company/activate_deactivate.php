<?php

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// السماح فقط POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method is allowed"
    ]);
    exit;
}

include "../config/connected.php";

if (!isset($pdo)) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

// قراءة البيانات
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من JSON
if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON data"
    ]);
    exit;
}

// التحقق من القيم
if (
    !isset($data['company_id']) || 
    !is_numeric($data['company_id']) ||
    !isset($data['status']) ||
    !in_array($data['status'], [0, 1])
) {
    echo json_encode([
        "status" => "error",
        "message" => "company_id and status (0 or 1) are required"
    ]);
    exit;
}

$company_id = $data['company_id'];
$status = $data['status'];

try {

    // التحقق إذا الشركة موجودة
    $check = $pdo->prepare("SELECT status FROM company WHERE company_id = :id");
    $check->execute([':id' => $company_id]);

    $company = $check->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        echo json_encode([
            "status" => "error",
            "message" => "Company not found"
        ]);
        exit;
    }

    // إذا نفس الحالة ما نحدث
    if ($company['status'] == $status) {
        echo json_encode([
            "status" => "warning",
            "message" => $status == 1 ? "Company already active" : "Company already inactive"
        ]);
        exit;
    }

    // تحديث الحالة
    $sql = "UPDATE company SET status = :status WHERE company_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status' => $status,
        ':id' => $company_id
    ]);

    echo json_encode([
        "status" => "success",
        "message" => $status == 1 
            ? "Company activated successfully" 
            : "Company deactivated successfully"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}