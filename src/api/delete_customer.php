<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->customer_id) && isset($data->is_admin) && $data->is_admin) {
    try {
        $conn->beginTransaction();

        // 1. Get the address_id first
        $getAddrQuery = "SELECT address_id FROM customer WHERE id = :id";
        $getStmt = $conn->prepare($getAddrQuery);
        $getStmt->bindParam(':id', $data->customer_id);
        $getStmt->execute();
        $addrId = $getStmt->fetchColumn();

        // 2. Delete the customer
        $query = "DELETE FROM customer WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $data->customer_id);
        $stmt->execute();

        // 3. Delete the address if it exists
        if ($addrId) {
            $addrQuery = "DELETE FROM address WHERE id = :addr_id";
            $addrStmt = $conn->prepare($addrQuery);
            $addrStmt->bindParam(':addr_id', $addrId);
            $addrStmt->execute();
        }

        $conn->commit();
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Customer and associated address deleted."]);
        
    } catch(PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Unauthorized or empty customer ID."]);
}
?>
