<?php
require_once '../../config/config.php';
require dirname(__DIR__) . '/vendor/autoload.php';

use WebSocket\Client;

class AttendanceModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    // Kiểm tra nhân viên đã điểm danh hôm nay chưa
    public function hasCheckedInToday($employeeId)
    {
        $sql = "SELECT * FROM attendance_logs WHERE employee_id = ? AND DATE(check_in_time) = DATE(NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Trả về dữ liệu nhân viên (ví dụ mảng chứa id)
        }
        return null;
    }

    // Kiểm tra nhân viên đã check-out hôm nay chưa
    public function hasCheckedOutToday($employeeId)
    {
        $sql = "SELECT * FROM attendance_logs WHERE employee_id = ? AND DATE(check_out_time) = DATE(NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Trả về thông tin check-out
        }
        return null; // Chưa check-out
    }

    // Hàm kiểm tra trạng thái của check in (On time, Late)
    function checkAttendanceStatus($employeeId, $checkInTime)
    {

        // Truy vấn lấy ca làm việc của nhân viên hôm nay
        $query = "SELECT s.shift_start, s.id
                      FROM work_schedules_detail wsd
                      JOIN work_schedules ws ON wsd.work_schedule_id = ws.id
                      JOIN shift s ON wsd.shift_id = s.id
                      WHERE ws.employee_id = ? AND wsd.date = DATE(NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row && $row['id'] != 4) {
            $shiftStartTime = $row['shift_start']; // Giờ bắt đầu ca làm

            // So sánh giờ check-in với giờ bắt đầu ca
            if ($checkInTime <= $shiftStartTime) {
                return "On Time";
            } else {
                return "Late";
            }
        } else {
            return "Absent"; // Không có lịch làm việc hôm nay
        }
    }

    // Ghi nhận check in
    public function checkInEmployee($employeeId)
    {
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $checkInTime = $now->format('Y-m-d H:i:s');

        $status = $this->checkAttendanceStatus($employeeId, $checkInTime);

        if ($status == "Absent") {
            return "Hôm nay bạn không có ca làm việc!";
        }

        $sql = "INSERT INTO attendance_logs (employee_id, check_in_time, check_type_in, status) VALUES (?, ?, 'QR', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $employeeId, $checkInTime, $status);
        $result = $stmt->execute();

        if ($result) {
            $query = "SELECT e.employee_code, e.full_name, a.check_in_time, a.check_out_time, a.check_type_in, a.check_type_out 
                  FROM attendance_logs a 
                  JOIN employees e ON a.employee_id = e.id 
                  WHERE a.employee_id = ? 
                  ORDER BY a.check_in_time DESC 
                  LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $employeeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                // Chuẩn bị dữ liệu bảng dưới dạng HTML
                $methodIn = ($row['check_type_in'] == 'QR') ? '<span class="badge bg-primary">QR</span>' : '<span class="badge bg-secondary">FaceID</span>';

                $tableData = "<tr>
                    <td>{$row['employee_code']}</td>
                    <td>{$row['full_name']}</td>
                    <td><span class='badge bg-success'>Check-in</span></td>
                    <td>{$row['check_in_time']}</td>
                    <td>{$methodIn}</td>
                  </tr>";

                // Gửi dữ liệu qua WebSocket Server
                try {
                    $client = new Client("ws://127.0.0.1:8080/");
                    $client->send($tableData);
                    $client->close();
                    return $checkInTime;   // Gửi server thành công
                } catch (Exception $e) {
                    return "Lỗi server";   // Lỗi gửi server
                }
            }
        } else {
            return "Không lưu vào database";   // Lưu vào csdl ko thành công
        }
    }

    // Hàm ghi nhận checkout
    public function checkOutEmployee($employeeId, $checkType = 'QR')
    {
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $checkOutTime = $now->format('Y-m-d H:i:s');
        // Cập nhật thời gian check-out và phương thức check-out trong bảng attendance_logs
        $sql = "UPDATE attendance_logs 
            SET check_out_time = ?, check_type_out = ? 
            WHERE employee_id = ? AND check_out_time IS NULL 
            ORDER BY check_in_time DESC 
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $checkOutTime, $checkType, $employeeId);
        $result = $stmt->execute();

        if ($result && $stmt->affected_rows > 0) {
            // Lấy lại thông tin để gửi cho WebSocket
            $query = "SELECT e.employee_code, e.full_name, a.check_in_time, a.check_out_time, a.check_type_in, a.check_type_out 
                  FROM attendance_logs a 
                  JOIN employees e ON a.employee_id = e.id 
                  WHERE a.employee_id = ? 
                  ORDER BY a.check_out_time DESC 
                  LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $employeeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                // Xác định phương thức chấm công
                $methodOut = ($row['check_type_out'] == 'QR')
                    ? '<span class="badge bg-primary">QR</span>'
                    : '<span class="badge bg-secondary">FaceID</span>';

                // Chuẩn bị dữ liệu HTML để gửi qua WebSocket
                $tableData = "<tr>
                <td>{$row['employee_code']}</td>
                <td>{$row['full_name']}</td>
                <td><span class='badge bg-danger'>Check-out</span></td>
                <td>{$row['check_out_time']}</td>
                <td>{$methodOut}</td>
              </tr>";

                // Gửi dữ liệu qua WebSocket Server
                try {
                    $client = new Client("ws://127.0.0.1:8080/");
                    $client->send($tableData);
                    $client->close();
                    return $checkOutTime;
                } catch (Exception $e) {
                    return "Lỗi server";
                }
            }
        } else {
            return "Không lưu vào database";
        }
    }

    // Thống kê công làm theo tháng
    public function getMonthlyAttendance($month, $year)
    {
        $sql = "SELECT e.id AS employee_id, e.employee_code, e.full_name, 
                   COUNT(DISTINCT DATE(a.check_in_time)) AS total_days,
                   SUM(CASE 
                        WHEN a.check_in_time IS NOT NULL AND a.check_out_time IS NOT NULL 
                        THEN TIMESTAMPDIFF(SECOND, a.check_in_time, a.check_out_time) / 3600 -- Tính tổng giờ làm
                        ELSE 0 
                   END) AS total_hours,
                   SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS late_count,         -- Đếm số lần đi làm trễ
                   SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count     -- Đếm số lần vắng
            FROM attendance_logs a
            JOIN employees e ON a.employee_id = e.id
            WHERE MONTH(a.check_in_time) = ? AND YEAR(a.check_in_time) = ?
            GROUP BY a.employee_id
            ORDER BY a.employee_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getEmployeeAttendanceByMonth($employee_id, $month, $year)
    {
        $sql = "SELECT a.id, a.employee_id, a.check_in_time, 
                   COALESCE(a.check_out_time, 'Chưa check-out') AS check_out_time,
                   a.check_type_in, 
                   COALESCE(a.check_type_out, 'Chưa có dữ liệu') AS check_type_out,
                   a.status
            FROM attendance_logs a
            WHERE a.employee_id = ? 
            AND (MONTH(a.check_in_time) = ? AND YEAR(a.check_in_time) = ?)
            ORDER BY a.check_in_time ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $employee_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}
