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

require_once "../../models/EmployeeModel.php";
require_once "../../helpers.php";
$employeeModel = new EmployeeModel($conn);

// Xác định action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = "";
switch ($action) {
    case 'list':
        // Hiển thị danh sách nhân viên
        $employees = $employeeModel->getAllEmployees();
        include "../../views/Admin/Employee/list.php";   // "../" dùng để đi lên 1 tầng thư mục 
        break;

    case 'create':
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //     // Lấy dữ liệu từ form
        //     $full_name = $_POST['full_name'];
        //     $email = $_POST['email'];
        //     $phone = $_POST['phone'];
        //     $department_id = $_POST['department_id'];
        //     $descriptorsJson = $_POST["descriptors"]; 
            
        //     $result = $employeeModel->createEmployee($full_name, $email, $phone, $department_id);

        //     if ($result === true) {
        //         header("Location: EmployeeController.php?action=list");
        //         exit;
        //     } else
        //         $error = $result; // Lưu thông báo lỗi để hiển thị trong HTML
        //     }
        // }
        include "../../views/Admin/Employee/create.php";
        break;

    case 'update':
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "Thiếu ID nhân viên!";
            exit;
        }

        // Lấy thông tin nhân viên từ DB
        $employee = $employeeModel->getEmployeeById($id);
        if (!$employee) {
            echo "Nhân viên không tồn tại!";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $department_id = $_POST['department_id'];

            $result = $employeeModel->updateEmployee($id, $full_name, $email, $phone, $department_id);

            if ($result === true) {
                header("Location: EmployeeController.php?action=list");
                exit;
            } else {
                $error = $result;
            }
        } else {
            // Gán giá trị từ DB vào form
            $full_name = $employee['full_name'];
            $email = $employee['email'];
            $phone = $employee['phone'];
            $department_id = $employee['department_id'];
        }

        include "../../views/Admin/Employee/update.php";
        break;

    default:
        echo "Hành động không hợp lệ!";
        break;
}
