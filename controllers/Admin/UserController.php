<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Admin/AuthController.php?action=login");
    exit;
}

require_once "../../models/EmployeeModel.php";
require_once "../../models/UserModel.php";
require_once "../../helpers.php";

// $employeeModel = new EmployeeModel($conn);
$userModel = new UserModel($conn);

// Mặc định không có lỗi
$error = "";
$success = "";
$activeTab = "profile-overview"; // Tab mặc định

$action = isset($_GET['action']) ? $_GET['action'] : 'profile';

// Xử lý switch case
switch ($action) {
    case 'profile':
        include "../../views/User/profile.php";
        break;

    case 'change_password':
        // // Lấy thông tin user từ bảng users
        $userData = $userModel->getUserById($_SESSION['user_id']);

        // Kiểm tra nếu không có mật khẩu trong database
        if (!isset($userData['password']) || empty($userData['password'])) {
            $error = "Lỗi: Không tìm thấy mật khẩu của tài khoản này!";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
            $currentPassword = $_POST['current_password'];
            // var_dump($currentPassword); // Kiểm tra dữ liệu
            // exit;
            $newPassword = $_POST['new_password'];
            $renewPassword = $_POST['renew_password'];

            // Kiểm tra mật khẩu mới nhập lại có trùng khớp không
            if ($newPassword !== $renewPassword) {
                $error = "Mật khẩu mới không khớp!";
            } else {
                // Kiểm tra mật khẩu hiện tại
                if (password_verify($currentPassword, $userData['password'])) {
                    // Mã hóa mật khẩu mới
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                    // Cập nhật mật khẩu
                    if ($userModel->updatePassword($_SESSION['user_id'], $hashedPassword)) {
                        $success = "Mật khẩu đã được cập nhật thành công!";
                    } else {
                        $error = "Lỗi khi cập nhật mật khẩu.";
                    }
                } else {
                    $error = "Mật khẩu hiện tại không đúng.";
                }
            }
        }

        // Hiển thị trang profile với tab "Change Password" mở sẵn
        $activeTab = "profile-change-password";
        include "../../views/User/profile.php";
        break;

    default:
        echo "Hành động không hợp lệ!";
        break;
}
?>
