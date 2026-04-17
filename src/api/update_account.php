<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->full_name) && !empty($data->email) && isset($data->role)) {
    try {
        $conn->beginTransaction();

        $table = ($data->role == 'admin') ? 'admin' : 'customer';

        // 1. Get current address_id
        $check = $conn->prepare("SELECT address_id FROM $table WHERE id = :id");
        $check->bindParam(':id', $data->id);
        $check->execute();
        $user_row = $check->fetch(PDO::FETCH_ASSOC);
        $address_id = $user_row['address_id'];

        // 2. Insert or Update address
        if ($address_id) {
            $addr_query = "UPDATE address SET region = :region, province = :province, city = :city, barangay = :barangay WHERE id = :id";
            $addr_stmt = $conn->prepare($addr_query);
            $addr_stmt->bindParam(':id', $address_id);
        } else {
            $addr_query = "INSERT INTO address (region, province, city, barangay) VALUES (:region, :province, :city, :barangay)";
            $addr_stmt = $conn->prepare($addr_query);
        }
        
        $addr_stmt->bindParam(':region', $data->region);
        $addr_stmt->bindParam(':province', $data->province);
        $addr_stmt->bindParam(':city', $data->city);
        $addr_stmt->bindParam(':barangay', $data->barangay);
        $addr_stmt->execute();

        if (!$address_id) {
            $address_id = $conn->lastInsertId();
        }

        // 3. Update main table
        $query = "UPDATE $table SET full_name = :full_name, email = :email, contact_number = :contact_number, address_id = :address_id WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $data->full_name);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':contact_number', $data->contact_number);
        $stmt->bindParam(':address_id', $address_id);
        $stmt->bindParam(':id', $data->id);
        $stmt->execute();

        $conn->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Account updated successfully.", "address_id" => $address_id]);

    } catch(PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. ID, role, full name, and email are required."]);
}
?>
