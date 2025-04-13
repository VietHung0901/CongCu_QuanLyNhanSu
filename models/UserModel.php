<?php
require_once __DIR__ . "/../config/config.php";

class UserModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Tạo tài khoản
    public function createUser($employee_id, $email, $password, $role1)
    {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (employee_id, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $employee_id, $email, $hashed_password, $role1);

        return $stmt->execute();
    }

    // Hàm kiểm tra đăng nhập
    public function login($username, $password)
    {
        $sql = "SELECT id, employee_id, username, password, role FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['password'])) {
                return $user; // Trả về thông tin user nếu thành công
            }
        }
        return false; // Sai tài khoản hoặc mật khẩu
    }

    public function getUserById($id)
    {
        $sql = "SELECT users.id, users.username, users.role, users.password, 
                       users.employee_id, employees.full_name 
                FROM users 
                LEFT JOIN employees ON users.employee_id = employees.id 
                WHERE users.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    public function getUserByEmployeeId($employee_id) {
        $sql = "SELECT * FROM users WHERE employee_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updatePassword($user_id, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newPassword, $user_id);
        
        return $stmt->execute();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getEmployeeById($id) {
        $sql = "SELECT * FROM employees WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}