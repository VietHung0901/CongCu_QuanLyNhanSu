<?php
session_start();
require_once "../../../config/config.php";
header("Content-Type: application/json");

//Nếu có đăng nhập đăng kí rồi thì dùng đoạn code này

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(["status" => "error", "message" => "Chưa đăng nhập hoặc không có employee_id"]);
    exit;
}
// Lấy employee_id từ session
$employee_id =  $_SESSION['employee_id'];

// Chỗ này code cứng id do chưa có đăng nhập đăng kí để lấy session 
// $employee_id = 50;
// Truy vấn lấy full_name và face_id
$stmt = $conn->prepare("SELECT id,full_name, employee_code, face_id FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $employee]);
} else {
    echo json_encode(["status" => "error", "message" => "Không tìm thấy nhân viên"]);
}

$stmt->close();
?>
