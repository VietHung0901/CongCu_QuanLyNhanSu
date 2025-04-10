<?php
require_once '../../config/config.php';
require_once '../../models/UserModel.php';

class UsersController {
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }

    public function login() {
        // Nhận dữ liệu từ client
        $inputData = file_get_contents("php://input");
        $data = json_decode($inputData, true);

        $username = $data["username"] ?? "";
        $password = $data["password"] ?? "";

        $userModel = new UserModel($this->conn);
        
        $user = $userModel->login($username, $password);

        if (!$user) {
            echo json_encode([
                "status" => "error",
                "message" => "Tài khoản hoặc mật khẩu không đúng"
            ]);
            exit();
        }

        echo json_encode([
            "status" => "success",
            "message" => "Đăng nhập thành công",
            "data" => [
                "user_id" => $user["id"],
                "username" => $user["username"],
                "role" => $user["role"],
            ]
        ]);
    }
}

// Xử lý request
$usersController = new UsersController($conn);
$action = $_GET['action'] ?? '';

if ($action == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $usersController->login();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Hành động không hợp lệ",
        "data" => null
    ]);
}
?>
