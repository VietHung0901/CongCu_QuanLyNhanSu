<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/UserModel.php";

class EmployeeModel
{
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }

    public function getEmployeeByEmail($email) {
        $sql = "SELECT id FROM employees WHERE email = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $result->fetch_assoc(); // Trả về dữ liệu nhân viên (ví dụ mảng chứa id)
            }
        }
        return null; // Trả về null nếu không tìm thấy
    }

    // Lấy thông tin nhân viên theo ID
    public function getEmployeeById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Lấy thông tin danh sách nhân viên
    public function getAllEmployees()
    {
        $sql = "SELECT e.id, e.employee_code, e.full_name, e.email, e.phone, d.name AS department 
            FROM employees e 
            LEFT JOIN departments d ON e.department_id = d.id";
        $result = $this->conn->query($sql);

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        return $employees;
    }

    // Tạo nhân viên
    // public function createEmployee($full_name, $email, $phone, $department_id, )
    // {
    //     // Tạo mã nhân viên tự động
    //     $employee_code = $this->generateEmployeeCode($full_name);

    //     if ($this->mailDaTonTai($email))
    //         return "Email đã tồn tại. Vui lòng nhập email khác!";

    //     $sql = "INSERT INTO employees (employee_code, full_name, email, phone, department_id)   /*Đây là một câu lệnh SQL INSERT để thêm dữ liệu vào bảng employees.*/
    //             VALUES (?, ?, ?, ?, ?)";    /*Dấu ? là placeholder để sử dụng prepared statements giúp tránh lỗi SQL Injection.*/

    //     $stmt = $this->conn->prepare($sql); /*Kết nối database (được truyền vào từ constructor).*/

    //     $stmt->bind_param("ssssi", $employee_code, $full_name, $email, $phone, $department_id); /*bind_param() giúp gán giá trị vào từng dấu ? trong câu SQL.*/
    //     // "ssssi": Xác định kiểu dữ liệu của các tham số

    //     if ($stmt->execute()) {
    //         $employee_id = $stmt->insert_id;

    //         // Gọi UserModel để tạo tài khoản
    //         $userModel = new UserModel($this->conn);
    //         $password = $employee_code . $phone;

    //         if ($userModel->createUser($employee_id, $email, $password)) {
    //             return true;
    //         }

    //         return "Lỗi khi tạo tài khoản user!";
    //     } else {
    //         return "Lỗi khi thêm nhân viên: " . $stmt->error;
    //     }   /*execute() sẽ chạy câu lệnh SQL với dữ liệu đã bind vào.
    //         Nếu thực thi thành công → Trả về true, nếu thất bại → Trả về false.*/
    // }

    // Tạo nhân viên
    public function createEmployee_FaceID($full_name, $email, $phone, $department_id, $face_descriptor, $role)
    {
        // Tạo mã nhân viên tự động
        $employee_code = $this->generateEmployeeCode($full_name);

        // Check for duplicate registration number
        $count = 0;
        $checkQuery = $this->conn->prepare("SELECT COUNT(*) FROM employees WHERE employee_code = ?");
        $checkQuery->bind_param("s", $employee_code);
        $checkQuery->execute();
        $checkQuery->bind_result($count);
        $checkQuery->fetch();
        $checkQuery->close();

        if ($count > 0) {
            $_SESSION['message'] = "Employee with the given Employee_code No: $employee_code already exists!";
            return;
        } 

        if ($this->mailDaTonTai($email))
            return "Email đã tồn tại. Vui lòng nhập email khác!";

        $sql = "INSERT INTO employees (employee_code, full_name, email, phone, department_id, face_id)   /*Đây là một câu lệnh SQL INSERT để thêm dữ liệu vào bảng employees.*/
                VALUES (?, ?, ?, ?, ?, ?)";    /*Dấu ? là placeholder để sử dụng prepared statements giúp tránh lỗi SQL Injection.*/

        $stmt = $this->conn->prepare($sql); /*Kết nối database (được truyền vào từ constructor).*/

        $stmt->bind_param("ssssis", $employee_code, $full_name, $email, $phone, $department_id, $face_descriptor); /*bind_param() giúp gán giá trị vào từng dấu ? trong câu SQL.*/
        // "ssssi": Xác định kiểu dữ liệu của các tham số

        if ($stmt->execute()) {
            $employee_id = $stmt->insert_id;

            // Gọi UserModel để tạo tài khoản
            $userModel = new UserModel($this->conn);
            $password = $employee_code . $phone;

            if ($userModel->createUser($employee_id, $email, $password, $role)) {
                $_SESSION['message'] = "Student: $employee_code added successfully!";
                return true;
            }

            return "Lỗi khi tạo tài khoản user!";
        } else {
            return "Lỗi khi thêm nhân viên: " . $stmt->error;
        }   /*execute() sẽ chạy câu lệnh SQL với dữ liệu đã bind vào.
            Nếu thực thi thành công → Trả về true, nếu thất bại → Trả về false.*/
    }

    // Cập nhật nhân viên
    public function updateEmployee($id, $full_name, $email, $phone, $department_id)
    {
        if ($this->mailDaTonTai($email, $id)) {
            return "Email đã tồn tại!";
        }

        $sql = "UPDATE employees SET full_name = ?, email = ?, phone = ?, department_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssii", $full_name, $email, $phone, $department_id, $id);
        return $stmt->execute() ? true : "Lỗi khi cập nhật nhân viên!";
    }

    // Tạo mã nhân viên
    private function generateEmployeeCode($fullName)
    {
        // Tách tên thành các từ
        $words = explode(" ", trim($fullName));
        $code = "";

        // Lấy chữ cái đầu của mỗi từ trong tên
        foreach ($words as $word) {
            $code .= strtoupper(mb_substr($word, 0, 1, "UTF-8")); // Đảm bảo hỗ trợ tiếng Việt
        }

        // Kiểm tra mã nhân viên đã tồn tại chưa
        $index = 0;
        $employeeCode = "";
        $count = 0;
        do {
            $employeeCode = $code . str_pad($index, 2, "0", STR_PAD_LEFT); // Định dạng 2 chữ số (00, 01, 02)

            // Truy vấn kiểm tra sự tồn tại
            $query = "SELECT COUNT(*) FROM employees WHERE employee_code = ?";
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                die("Lỗi prepare: " . $this->conn->error);
            }

            $stmt->bind_param("s", $employeeCode);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            $index++;
        } while ($count > 0); // Tìm mã chưa tồn tại

        return $employeeCode;
    }

    // Kiểm tra email tồn tại (dùng chung cho cả create và update)
    private function mailDaTonTai($email, $id = null)
    {
        $count = 0;
        // Nếu $id là null => kiểm tra toàn bộ bảng (dùng cho create)
        // Nếu $id có giá trị => Loại trừ nhân viên đang update
        $checkQuery = "SELECT COUNT(*) FROM employees WHERE email = ?" . ($id ? " AND id != ?" : "");

        $stmt = $this->conn->prepare($checkQuery);

        if ($id) {
            $stmt->bind_param("si", $email, $id);
        } else {
            $stmt->bind_param("s", $email);
        }

        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            return true; // Báo lỗi nếu email đã có
        }
        return false;
    }
}
