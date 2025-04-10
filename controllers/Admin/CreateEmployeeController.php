<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Thông tin API Imgur
define("IMGUR_CLIENT_ID", "0715c64086c9b8d");
define("MAX_RETRIES", 2); // Số lần retry tối đa

function uploadToImgur($imagePath, $retry = 0) {
    $url = "https://api.imgur.com/3/upload";
    
    $imageData = file_get_contents($imagePath);
    $postData = [
        "image" => base64_encode($imageData), // Chuyển ảnh sang base64
        "type" => "base64"
    ];

    $headers = [
        "Authorization: Client-ID " . IMGUR_CLIENT_ID
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    // Nếu upload thành công (HTTP 200 & có link ảnh)
    if ($httpCode === 200 && isset($responseData["data"]["link"])) {
        return $responseData["data"]["link"];
    } 

    // Nếu lỗi và còn số lần retry, thử lại
    if ($retry < MAX_RETRIES) {
        return uploadToImgur($imagePath, $retry + 1);
    }

    return false; // Nếu sau 2 lần vẫn thất bại, trả về false
}
//----------------------------
require_once "../../config/config.php";
require_once "../../models/EmployeeModel.php";
$employeeModel = new EmployeeModel($conn);

// Xác định action
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department_id = $_POST['department_id'];
    $descriptorsJson = $_POST["descriptors"]; 
    $role = $_POST['role'];

    $result = $employeeModel->createEmployee_FaceID($full_name, $email, $phone, $department_id, $descriptorsJson, $role);

    if ($result === true) {
        echo json_encode(["status" => "success", "message" => "Thành công"]);
        // header("Location: EmployeeController.php?action=list");
        exit;
    } else {
        echo json_encode(["status" => "fail", "message" => "Lỗi trong quá trình thu thập khuôn mặt"]);
        $error = $result; // Lưu thông báo lỗi để hiển thị trong HTML
        exit;
    }
}
?>
