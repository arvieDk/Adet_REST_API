<?php
header('Content-Type: application/json');
require_once 'db.php';

$query = "SELECT c.id, c.full_name, c.email, c.contact_number, c.created_at, 
                 a.region, a.province, a.city, a.barangay 
          FROM customer c
          LEFT JOIN address a ON c.address_id = a.id
          ORDER BY c.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute();

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode([
    "status" => "success",
    "data" => $customers
]);
?>
