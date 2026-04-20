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
        "message" => "Only PUT method allowed"
    ]);
    exit;
}

include "../config/connected.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON"
    ]);
    exit;
}

// user_id المطلوب تعديله
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        "status" => "error",
        "message" => "user_id is required"
    ]);
    exit;
}

// القيم القابلة للتعديل
$name = $data['name'] ?? null;
$phone = $data['phone'] ?? null;
$image = $data['image'] ?? null;

try {

    // التأكد أن المستخدم موجود
    $check = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
    $check->execute([':id' => $user_id]);

    $user = $check->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => "error",
            "message" => "User not found"
        ]);
        exit;
    }

    $fields = [];
    $params = [':id' => $user_id];

    if (!empty($name)) {
        $fields[] = "name = :name";
        $params[':name'] = $name;
    }

    if (!empty($phone)) {

        // منع التكرار
        $checkPhone = $pdo->prepare("
            SELECT user_id FROM users 
            WHERE phone = :phone AND user_id != :id
        ");

        $checkPhone->execute([
            ':phone' => $phone,
            ':id' => $user_id
        ]);

        if ($checkPhone->fetch()) {
            echo json_encode([
                "status" => "error",
                "message" => "Phone already used by another user"
            ]);
            exit;
        }

        $fields[] = "phone = :phone";
        $params[':phone'] = $phone;
    }

    if (!empty($image)) {
        $fields[] = "image = :image";
        $params[':image'] = $image;
    }

    if (empty($fields)) {
        echo json_encode([
            "status" => "error",
            "message" => "No data to update"
        ]);
        exit;
    }

    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "status" => "success",
        "message" => "User updated successfully (admin)"
    ]);

    exit;

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}