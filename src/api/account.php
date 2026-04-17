<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && isset($data->role)) {
    $table = ($data->role == 'admin') ? 'admin' : 'customer';
    
    $query = "SELECT u.id, u.full_name, u.email, u.contact_number, u.created_at, 
                     a.region, a.province, a.city, a.barangay 
              FROM $table u
              LEFT JOIN address a ON u.address_id = a.id
              WHERE u.id = :id LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $data->id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Account details retrieved.",
            "data" => $row
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Account not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. User ID and role are required."]);
}
?>
