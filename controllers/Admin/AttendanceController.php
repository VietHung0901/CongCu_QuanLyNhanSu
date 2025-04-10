<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Admin/AuthController.php?action=login");
    exit;
}

require_once "../../models/AttendanceModel.php";
require_once "../../helpers.php";
$attendanceModel = new AttendanceModel($conn);

// Xác định action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = "";
switch ($action) {
    case 'list':
        // Hiển thị danh sách thống kê công làm
        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $attendanceRecords = $attendanceModel->getMonthlyAttendance($month, $year);
        include "../../views/Admin/Attendance/list.php";
        break;

    case 'detail':
        // Lấy chi tiết lịch điểm danh của một nhân viên
        if (!isset($_GET['employee_id'])) {
            echo "Thiếu employee_id!";
            exit;
        }

        $employee_id = intval($_GET['employee_id']);
        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        $employeeAttendance = $attendanceModel->getEmployeeAttendanceByMonth($employee_id, $month, $year);
        include "../../views/Admin/Attendance/detail.php";
        break;

    default:
        echo "Hành động không hợp lệ!";
        break;
}
