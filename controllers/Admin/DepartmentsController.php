<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Admin/AuthController.php?action=login");
    exit;
}

require_once "../../config/config.php";
require_once "../../models/DepartmentsModel.php";
require_once "../../helpers.php";

$DepartmentsModel = new DepartmentsModel($conn);

// Xác định action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    case 'list':
        $departments = $DepartmentsModel->getAllDepartments();
        include "../../views/Admin/Departments/list.php";
        break;

        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['name'];
                $DepartmentsModel->createDepartment($name);
        
                // Trả về JSON để AJAX xử lý
                header('Content-Type: application/json');
                echo json_encode(["status" => "success", "message" => "Phòng ban đã được tạo thành công"]);
                exit;
            }
            break;        

    case 'edit':
        $departments = $DepartmentsModel->getAllDepartments();
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            foreach ($departments as $dept) {
                if ($dept['id'] == $id) {
                    $currentDept = $dept;
                    break;
                }
            }
        }
        include "../../views/Admin/Departments/list.php";
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $name = $_POST['name'];
            $DepartmentsModel->updateDepartment($id, $name);
            
            // Trả về JSON để AJAX xử lý
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "message" => "Phòng ban đã được tạo thành công"]);
            exit;
        }
        break;

    default:
        echo "Hành động không hợp lệ!";
        break;
}