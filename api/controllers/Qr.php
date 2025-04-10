<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;   // Dùng để tạo QR
use chillerlan\QRCode\QROptions;    // Dùng để cấu hình QR

// ====== Cấu hình thông tin QR Code ======
$checkinType   = "CHECKIN";
$companyName   = "HDBANK";
$timestamp     = date("Y-m-d\TH:i:s\Z"); // Thời gian hiện tại chuẩn ISO 8601
$secretKey     = "SECRET123"; // Khóa bí mật dùng để tạo hash
$nonce         = bin2hex(random_bytes(8)); // chuỗi random
$authToken     = hash_hmac('sha256', "$companyName|$timestamp|$nonce", $secretKey); // Mã hash bảo mật

// Tạo nội dung QR Code
$data = sprintf("%s|%s|%s|%s|%s", $checkinType, $companyName, $timestamp, $nonce, $authToken);

// ====== Ghi nonce vào file trước khi render ======
$nonceFile = __DIR__ . '/../nonce.txt';
file_put_contents($nonceFile, $nonce); // Ghi đè, chỉ giữ lại nonce hiện tại

// ====== Cấu hình QR Code ======
$options = new QROptions([
    'outputType'  => QRCode::OUTPUT_IMAGE_PNG, 
    'eccLevel'    => QRCode::ECC_H, 
    'scale'       => 10, 
    'imageBase64' => false, 
]);

$qr = new QRCode($options);

// Xuất ảnh QR trực tiếp
header('Content-Type: image/png');
echo $qr->render($data);
?>
