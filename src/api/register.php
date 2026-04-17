<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    try {
        $conn->beginTransaction();

        $is_admin = (isset($data->is_admin) && $data->is_admin);
        $table = $is_admin ? 'admin' : 'customer';

        // 1. First, create the address record if data is provided
        $address_id = null;
        if (!empty($data->region) && !empty($data->province) && !empty($data->city) && !empty($data->barangay)) {
            $addr_query = "INSERT INTO address (region, province, city, barangay) VALUES (:region, :province, :city, :barangay)";
            $addr_stmt = $conn->prepare($addr_query);
            $addr_stmt->bindParam(':region', $data->region);
            $addr_stmt->bindParam(':province', $data->province);
            $addr_stmt->bindParam(':city', $data->city);
            $addr_stmt->bindParam(':barangay', $data->barangay);
            $addr_stmt->execute();
            $address_id = $conn->lastInsertId();
        }

        // 2. Insert into user table with the address_id
        $query = "INSERT INTO $table (full_name, email, password, contact_number, address_id) VALUES (:full_name, :email, :password, :contact_number, :address_id)";
        $stmt = $conn->prepare($query);

        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
        $full_name = isset($data->full_name) ? $data->full_name : '';
        $contact = isset($data->contact_number) ? $data->contact_number : '';

        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':contact_number', $contact);
        $stmt->bindParam(':address_id', $address_id);

        if ($stmt->execute()) {
            $user_id = $conn->lastInsertId();
            $conn->commit();
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => ($is_admin ? "Admin" : "Customer") . " account created.", "id" => $user_id]);
        } else {
            $conn->rollBack();
            http_response_code(503);
            echo json_encode(["status" => "error", "message" => "Unable to create account."]);
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        if ($e->getCode() == 23000) {
            echo json_encode(["status" => "error", "message" => "Email already exists."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Email and password are required."]);
}
?>
