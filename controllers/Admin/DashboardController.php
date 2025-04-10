<?php
session_start();
// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Admin/AuthController.php?action=login");
    exit;
}

require_once "../../helpers.php";

include "../../views/Admin/dashboard.php";
?>
