<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $is_admin = isset($data->is_admin) ? $data->is_admin : false;
    
    if ($is_admin) {
        $query = "SELECT id, password FROM admin WHERE email = :email LIMIT 1";
    } else {
        $query = "SELECT id, password, full_name FROM customer WHERE email = :email LIMIT 1";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($data->password, $row['password'])) {
            http_response_code(200);
            $response = [
                "status" => "success",
                "message" => "Login successful.",
                "user" => [
                    "id" => $row['id'],
                    "role" => $is_admin ? "admin" : "customer"
                ]
            ];
            if (!$is_admin) {
                $response["user"]["name"] = $row['full_name'];
            }
            echo json_encode($response);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Incorrect password."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Email and password are required."]);
}
?>
