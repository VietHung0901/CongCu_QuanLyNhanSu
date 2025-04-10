<?php
require_once '../../config/config.php';
require_once '../../models/AttendanceModel.php';
require_once '../../models/UserModel.php';

$attendanceModel = new AttendanceModel($conn);
$userModel = new UserModel($conn);
// Xử lý API request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Kiểm tra và nhận tham số từ query string
    if (isset($_GET['username']) && isset($_GET['month']) && isset($_GET['year'])) {
        $username = $_GET['username'];
        $month = intval($_GET['month']);
        $year = intval($_GET['year']);

        $user = $userModel->getUserByEmail($username);
        $employee_id = $user['employee_id']; // Lấy employee_id từ user
        $attendance = $attendanceModel->getEmployeeAttendanceByMonth($employee_id, $month, $year);


        // Trả về JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $attendance
        ]);
    } else {
        // Trả về lỗi nếu thiếu tham số
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Thiếu tham số employee_id, month hoặc year'
        ]);
    }
} else {
    // Trả về lỗi nếu không phải phương thức GET
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Phương thức không được hỗ trợ'
    ]);
}
