<?php

//MySql
$servername = "127.0.0.1"; // Địa chỉ máy chủ MySQL
$username = "root"; // Tên đăng nhập MySQL
$password = ""; // Mật khẩu MySQL
$database = "QuanLyNhanSu"; // Tên cơ sở dữ liệu

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối thất bại: " . $conn->connect_error]));
}
// echo "<script>console.log('Connected database succesfully');</script>";
?>