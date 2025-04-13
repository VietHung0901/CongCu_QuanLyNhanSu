<?php
require_once '../../config/config.php';
require_once '../../models/EmployeeModel.php';
require_once '../../models/AttendanceModel.php';

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $employeeCode = $_POST['employee_code'] ?? '';
    $qrCode       = $_POST['qr_code'] ?? '';
    $latitude  = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';

    if (empty($employeeCode) || empty($qrCode)) {
        echo json_encode(["status" => "error", "message" => "Thiếu thông tin"]);
        exit;
    }

    // Phân tích nội dung mã QR
    $qrParts = explode("|", $qrCode); // dùng để tách chuỗi qrCode thành mảng dựa trên "|"
    if (count($qrParts) !== 5) {
        echo json_encode(["status" => "error", "message" => "Mã QR không hợp lệ"]);
        exit;
    }

    list($prefix, $company_id, $timestamp, $nonce, $hash) = $qrParts; // list() giúp gán giá trị của mảng vào nhiều biến cùng một lúc theo thứ tự của mảng

    // Kiểm tra định dạng QR Code
    if ($prefix !== "CHECKIN" || $company_id !== "HDBANK") {
        echo json_encode(["status" => "error", "message" => "Mã QR không hợp lệ"]);
        exit;
    }

    // Xác thực mã hash
    $secret_key     = "SECRET123";
    $expected_hash  = hash_hmac("sha256", "$company_id|$timestamp|$nonce", $secret_key);
    if ($hash !== $expected_hash) {
        echo json_encode(["status" => "error", "message" => "Mã QR bị giả mạo"]);
        exit;
    }

    // Kiểm tra thời gian hợp lệ (không quá 2 phút)
    $qrTime = strtotime($timestamp);
    if (time() - $qrTime > 120) {
        echo json_encode(["status" => "error", "message" => "Mã QR đã hết hạn"]);
        exit;
    }

    // Kiểm tra qr này đã dùng chưa
    $nonceFile = __DIR__ . '/../nonce.txt';
    $nonceContent = file_get_contents($nonceFile);
    if($nonceContent !== $nonce)
    {
        echo json_encode(["status" => "error", "message" => "Mã QR đã được sử dụng"]);
        exit;
    }

    // Kiểm tra tòa độ có được gửi đến không
    if (empty($latitude) || empty($longitude)) {
        echo json_encode(["status" => "error", "message" => "Thiếu thông tin tọa độ"]);
        exit;
    }

    // Hàm tính khoảng cách
    function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Bán kính Trái Đất (km)

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Vị trí trường HUTECH
    // $company_latitude = 10.85593588396554;
    // $company_longitude = 106.78560604635747;

    $company_latitude = 10.8627;
    $company_longitude = 106.8069;
    
    $max_distance = 0.1; // Giới hạn khoảng cách (km) cho phép chấm công

    $distance = haversineDistance($latitude, $longitude, $company_latitude, $company_longitude);

    if ($distance > $max_distance) {
        echo json_encode(["status" => "error", "message" => "Bạn đang ở quá xa vị trí công ty"]);
        exit;
    }

    $employeeModel = new EmployeeModel($conn);
    $employee = $employeeModel->getEmployeeByEmail($employeeCode);

    if (!$employee) {
        echo json_encode(["status" => "error", "message" => "Mã nhân viên không tồn tại"]);
        exit;
    }
    $employeeId = $employee['id'];

    $attendanceModel = new AttendanceModel($conn);
    $attendanceCheckInToday = $attendanceModel->hasCheckedInToday($employeeId);
    if (!$attendanceCheckInToday) {
        $attendanceInsert = $attendanceModel->checkInEmployee($employeeId);
        if (strtotime($attendanceInsert)) { // strtotime để kiểm tra xem giá trị trả về có phải là thời gian hợp lệ hay không.
            echo json_encode(["status" => "success", "message" => "Check in thành công", "check_in_time" => $attendanceInsert]);
            exit;
        }
        echo json_encode(["status" => "error", "message" => $attendanceInsert]);
        exit;
    }

    $attendanceCheckOutToday = $attendanceModel->hasCheckedOutToday($employeeId);
    if (!$attendanceCheckOutToday) {
        $attendanceUpdate = $attendanceModel->checkOutEmployee($employeeId);
        if (strtotime($attendanceUpdate)) {
            echo json_encode(["status" => "success", "message" => "Check out thành công", "check_in_time" => $attendanceUpdate]);
            exit;
        }
        echo json_encode(["status" => "error", "message" => $attendanceUpdate]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Bạn đã check out cho hôm nay!"]);
    }
}
