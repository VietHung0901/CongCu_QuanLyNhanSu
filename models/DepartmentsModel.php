<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/UserModel.php";

class DepartmentsModel
{
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }
    
    function createDepartment($name)
    {
        $sql = "INSERT INTO departments (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }

    // READ ALL
    public function getAllDepartments()
    {
        // Chuẩn bị câu lệnh SQL
        $sql = "SELECT id, name FROM departments";

        // Thực thi truy vấn
        $result = $this->conn->query($sql);

        // Khởi tạo mảng rỗng để lưu kết quả
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        return $departments;
    }

    // UPDATE
    function updateDepartment($id, $name)
    {
        $sql = "UPDATE departments SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    }
}