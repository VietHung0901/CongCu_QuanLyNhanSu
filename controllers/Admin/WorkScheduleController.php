<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Admin/AuthController.php?action=login");
    exit;
}

require_once "../../models/WorkScheduleModel.php";
require_once "../../helpers.php";
$workScheduleModel = new WorkScheduleModel($conn);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = "";

switch ($action) {
    case 'list':
        $schedules = $workScheduleModel->getAllWorkSchedules();
        include "../../views/Admin/WorkSchedule/list.php";
        break;

    case 'create':
        $employees = $workScheduleModel->getAllEmployees();
        $shifts = $workScheduleModel->getShifts();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy dữ liệu từ $_POST
            $employeeId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
            $month = isset($_POST['month']) ? (int) $_POST['month'] : 0;
            $year = isset($_POST['year']) ? (int) $_POST['year'] : 0;
            $workDays = isset($_POST['work_days']) ? $_POST['work_days'] : [];

            $result = $workScheduleModel->createFullWorkSchedule($employeeId, $month, $year, $workDays);
            if ($result === true) {
                header("Location: $baseUrl?action=list");
                exit;
            } else {
                $error = $result;
            }
        }
        include "../../views/Admin/WorkSchedule/create.php";
        break;

    case 'update':
        $workScheduleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : '';

        if (!$workScheduleId || !$date) {
            $error = "Thiếu ID lịch làm việc hoặc ngày!";
            include "../../views/Admin/WorkSchedule/update.php";
            break;
        }

        $schedule = $workScheduleModel->getWorkScheduleById($workScheduleId);
        if (!$schedule) {
            $error = "Không tìm thấy lịch làm việc!";
            include "../../views/Admin/WorkSchedule/update.php";
            break;
        }

        $shifts = $workScheduleModel->getShifts();
        $details = $workScheduleModel->getWorkScheduleDetails($workScheduleId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $shiftId = isset($_POST['shift_id']) ? (int) $_POST['shift_id'] : 0;

            if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $error = "Ngày không hợp lệ!";
            } elseif ($shiftId === 0) {
                $workScheduleModel->query("DELETE FROM work_schedules_detail WHERE work_schedule_id = $workScheduleId AND date = '$date'");
                header("Location: $baseUrl?action=detail&id={$schedule['id']}&month={$schedule['month']}&year={$schedule['year']}");
                exit;
            } else {
                $workScheduleModel->updateWorkScheduleDetail($workScheduleId, $date, $shiftId);
                header("Location: $baseUrl?action=detail&id={$schedule['id']}&month={$schedule['month']}&year={$schedule['year']}");
                exit;
            }
        }
        include "../../views/Admin/WorkSchedule/update.php";
        break;

    case 'detail':
        // Lấy dữ liệu từ $_GET
        $month = isset($_GET['month']) ? (int) $_GET['month'] : 0;
        $year = isset($_GET['year']) ? (int) $_GET['year'] : 0;
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0; // Lấy id từ URL

        // Khởi tạo biến $employeeName với giá trị mặc định
        $employeeName = 'Không xác định';

        if (!$month || !$year || !$id) {
            $error = "Thông tin không hợp lệ!";
            $daysInMonth = 0; // Gán mặc định để tránh lỗi undefined
            include "../../views/Admin/WorkSchedule/detail.php";
            break;
        }

        // Lấy thông tin lịch làm việc theo id
        $scheduleInfo = $workScheduleModel->getWorkScheduleById($id);
        
        if (!empty($scheduleInfo)) {
            // Lấy tên nhân viên từ dữ liệu lịch
            $employeeName = $scheduleInfo['full_name'];
            $employee_code = $scheduleInfo['employee_code']; // Để sử dụng trong view
        } else {
            $error = "Không tìm thấy lịch làm việc!";
        }
        
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $scheduleData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $scheduleData[$date] = ['shift_start' => '', 'shift_end' => '', 'shift_id' => '', 'shift_type' => 'Hủy ca', 'employees' => []];
        }

        // Chỉ lấy chi tiết lịch làm việc của id được chọn
        $details = $workScheduleModel->getWorkScheduleDetails($id);
        foreach ($details as $date => $shiftData) {
            $scheduleData[$date] = [
                'shift_start' => $shiftData['shift_start'],
                'shift_end' => $shiftData['shift_end'],
                'shift_id' => $shiftData['shift_id'],
                'shift_type' => $shiftData['shift_type'],
                'employees' => [['full_name' => $employeeName, 'work_schedule_id' => $id]]
            ];
        }

        if (empty(array_filter($scheduleData, fn($data) => !empty($data['employees'])))) {
            $error = "Không có lịch làm việc nào cho nhân viên $employeeName trong tháng $month/$year!";
        }
        
        include "../../views/Admin/WorkSchedule/detail.php";
        break;

    // Các case khác giữ nguyên...
    default:
        $error = "Hành động không hợp lệ!";
        include "../../views/Admin/WorkSchedule/list.php";
        break;
}

