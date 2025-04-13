<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/UserModel.php";


class WorkScheduleModel
{
    private $conn;

    // Khởi tạo đối tượng với kết nối database
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function createFullWorkSchedule($employeeId, $month, $year, $workDays)
    {
        $shifts = $this->getShifts(); 
        $currentYear = (int) date('Y');
        $errors = []; // Mảng lưu trữ thông báo lỗi

        // Kiểm tra dữ liệu đầu vào
        if (!$employeeId)
            $errors[] = "Vui lòng chọn nhân viên!";
        if (!$month || $month < 1 || $month > 12)
            $errors[] = "Tháng không hợp lệ (1-12)!";
        if (!$year || $year != $currentYear)
            $errors[] = "Chỉ tạo lịch trong năm hiện tại ($currentYear)!";
        if ($employeeId && $month && $year && $this->isScheduleDuplicate($employeeId, $month, $year)) {
            $errors[] = "Nhân viên đã có lịch trong tháng $month/$year!";
        }

        // Nếu không có lỗi, tiến hành tạo lịch
        if (empty($errors)) {
            $workScheduleId = $this->createWorkSchedule($employeeId, $month, $year); // Tạo bản ghi trong work_schedules
            if ($workScheduleId) {
                foreach ($workDays as $date => $shiftName) { // Duyệt qua từng ngày trong mảng workDays
                    if ($shiftName !== 'Không làm') { // Chỉ thêm chi tiết nếu không phải "Không làm"
                        $shiftId = array_search($shiftName, array_column($shifts, 'name')); // Tìm shift_id từ tên ca
                        if ($shiftId !== false) {
                            $shiftId = $shifts[$shiftId]['id'];
                            $this->createWorkScheduleDetail($workScheduleId, $date, $shiftId); // Thêm chi tiết lịch
                        } else {
                            $errors[] = "Ca làm việc '$shiftName' không tồn tại cho ngày $date!";
                        }
                    }
                }
                if (empty($errors)) {
                    return true; // Trả về true nếu mọi thứ thành công
                }
            }
            $errors[] = "Không thể tạo lịch làm việc!";
        }
        return implode("<br>", $errors); // Trả về chuỗi lỗi nếu có vấn đề
    }

    public function createWorkSchedule($employeeId, $month, $year)
    {
        $stmt = $this->conn->prepare("INSERT INTO work_schedules (employee_id, month, year) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iii", $employeeId, $month, $year);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
        $id = $this->conn->insert_id; // Lấy ID của bản ghi vừa tạo
        $stmt->close();
        return $id;
    }

    // Tạo hoặc cập nhật chi tiết lịch làm việc trong bảng work_schedules_detail
    public function createWorkScheduleDetail($workScheduleId, $date, $shiftId)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO work_schedules_detail (work_schedule_id, date, shift_id)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE shift_id = ?
        ");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("isii", $workScheduleId, $date, $shiftId, $shiftId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Lấy tất cả lịch làm việc từ DB, kèm tên nhân viên
    public function getAllWorkSchedules()
    {
        $result = $this->conn->query("
            SELECT ws.id, ws.employee_id, e.employee_code, e.full_name, ws.month, ws.year 
            FROM work_schedules ws 
            JOIN employees e ON ws.employee_id = e.id
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : []; // Trả về mảng kết quả hoặc mảng rỗng
    }


    // Thêm vào WorkScheduleModel
    public function getWorkScheduleById($id)
    {
        $stmt = $this->conn->prepare("
            SELECT ws.id, ws.employee_id, ws.month, ws.year, e.employee_code, e.full_name
            FROM work_schedules ws 
            JOIN employees e ON ws.employee_id = e.id
            WHERE ws.id = ?
        ");
        if (!$stmt) return null;
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    // Lấy chi tiết lịch làm việc theo work_schedule_id
    // Trả về mảng chi tiết cho tất cả các ngày trong tháng
    public function getWorkScheduleDetails($workScheduleId)
    {
        $stmt = $this->conn->prepare("SELECT month, year FROM work_schedules WHERE id = ?");
        if (!$stmt) return [];
        $stmt->bind_param("i", $workScheduleId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) return [];
        $month = $result['month'];
        $year = $result['year'];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $stmt = $this->conn->prepare("
            SELECT wsd.date, s.id AS shift_id, s.name AS shift_type, s.shift_start AS shift_start, s.shift_end AS shift_end
            FROM work_schedules_detail wsd
            JOIN shift s ON wsd.shift_id = s.id
            WHERE wsd.work_schedule_id = ?
            ORDER BY wsd.created_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $workScheduleId);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $shifts = $this->getShifts();
        $defaultShift = array_filter($shifts, fn($s) => $s['name'] === 'Không làm');
        $defaultShift = !empty($defaultShift) ? reset($defaultShift) : ['id' => 0, 'shift_start' => '00:00', 'shift_end' => '00:00'];

        $scheduleData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
            $scheduleData[$date] = [
                'shift_id' => $defaultShift['id'], 
                'shift_type' => 'Hủy ca', 
                'shift_start' => $defaultShift['shift_start'], 
                'shift_end' => $defaultShift['shift_end']
            ];
        }

        foreach ($details as $detail) {
            $scheduleData[$detail['date']] = [
                'shift_id' => $detail['shift_id'],
                'shift_type' => $detail['shift_type'] === 'Không làm' ? 'Hủy ca' : $detail['shift_type'],
                'shift_start' => isset($detail['shift_start']) ? $detail['shift_start'] : $defaultShift['shift_start'],
                'shift_end' => isset($detail['shift_end']) ? $detail['shift_end'] : $defaultShift['shift_end']
            ];
        }
        return $scheduleData;
    }

    // Lấy danh sách tất cả ca làm việc từ bảng shift
    public function getShifts()
    {
        $result = $this->conn->query("SELECT id, name, shift_start, shift_end FROM shift");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Cập nhật chi tiết lịch làm việc (thêm mới hoặc sửa)
    public function updateWorkScheduleDetail($workScheduleId, $date, $shiftId)
    {
        // Kiểm tra xem bản ghi đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT id FROM work_schedules_detail WHERE work_schedule_id = ? AND date = ?");
        $stmt->bind_param("is", $workScheduleId, $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            // Nếu tồn tại, cập nhật
            $stmt = $this->conn->prepare("UPDATE work_schedules_detail SET shift_id = ? WHERE work_schedule_id = ? AND date = ?");
            $stmt->bind_param("iis", $shiftId, $workScheduleId, $date);
            $stmt->execute();
            $stmt->close();
        } else {
            // Nếu không tồn tại, thêm mới
            $stmt = $this->conn->prepare("INSERT INTO work_schedules_detail (work_schedule_id, date, shift_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $workScheduleId, $date, $shiftId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Lấy danh sách tất cả nhân viên từ bảng employees
    public function getAllEmployees()
    {
        $result = $this->conn->query("SELECT id, employee_code, full_name FROM employees");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Kiểm tra xem nhân viên đã có lịch trong tháng/năm cụ thể chưa
    public function isScheduleDuplicate($employeeId, $month, $year)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM work_schedules WHERE employee_id = ? AND month = ? AND year = ?");
        if (!$stmt)
            return false;
        $stmt->bind_param("iii", $employeeId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['count'] > 0; // Trả về true nếu đã tồn tại
    }

    // Thực thi câu lệnh SQL trực tiếp (dùng trong update case của controller)
    public function query($sql)
    {
        return $this->conn->query($sql);
    }
}