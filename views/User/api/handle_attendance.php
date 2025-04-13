<?php
require_once "../../../config/config.php";
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);
    $response = [];

    if (isset($attendanceData['employee_id'])) {
        try {
            $employee_id = $attendanceData['employee_id'];

            // Kiểm tra xem nhân viên này đã có Check-in hôm nay chưa
            // Cột status chưa xét đến vì chưa có thời gian làm việc
            $stmt = $conn->prepare("
                SELECT id, check_in_time, check_out_time
                FROM attendance_logs
                WHERE employee_id = ? AND DATE(check_in_time) = DATE(NOW())
            ");
            $stmt->bind_param("i", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $attendance = $result->fetch_assoc();

            if (!$attendance) {
                // Nếu chưa có Check-in, INSERT mới
                $stmt = $conn->prepare("
                    INSERT INTO attendance_logs (employee_id, check_in_time, check_out_time, check_type_in, check_type_out ) 
                    VALUES (?, NOW(), NULL, 'FaceID', NULL)
                ");
                $stmt->bind_param("i", $employee_id);
                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = "Insert check-in attendance successfully.";
                } else {
                    $response['status'] = 'error';
                    $response['message'] = "Error inserting attendance data.";
                }
            } elseif ($attendance['check_out_time'] === null) {
                // ✅ Nếu đã có Check-in nhưng chưa Check-out, UPDATE Check-out
                $stmt = $conn->prepare("
                    UPDATE attendance_logs 
                    SET check_out_time = NOW(), check_type_out = 'FaceID' 
                    WHERE employee_id = ?
                ");
                $stmt->bind_param("i", $employee_id);

                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = "Update check-out attendance successfully.";
                } else {
                    $response['status'] = 'error';
                    $response['message'] = "Error updating attendance data.";
                }
            } else {
                // Nếu đã có cả Check-in và Check-out → Không làm gì
                $response['status'] = 'success';
                $response['message'] = "You already checked in and checked out today.";
            }

            $stmt->close();
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = "Error inserting attendance data: " . $e->getMessage();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "No employee_id data received.";
    }

    echo json_encode($response);
}
