<?php
session_start();
require_once "../../config/config.php";
require_once "../../models/UserModel.php";
require_once "../../helpers.php";

// Kết nối database
$userModel = new UserModel($conn);

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$error = "";
$success = "";
$token = isset($_GET['token']) ? $_GET['token'] : null;

// Hàm tạo token ngẫu nhiên
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}


function sendResetLink($email, $token)
{
    require_once "../../vendor/autoload.php";
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);


    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'doquangvu.official@gmail.com'; // Thay bằng email thực tế
        $mail->Password = 'zphn csgh ayad xjap';    // Thay bằng App Password
        $mail->SMTPSecure = 'ssl'; // Thử SSL
        $mail->Port = 465;        // Cổng 465
        $mail->CharSet = 'UTF-8'; // Đặt UTF-8

        $mail->setFrom('doquangvu.official@gmail.com', 'Your App');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/QuanLyNhanSu/controllers/Admin/AuthController.php?action=reset_password&token=" . $token;
        $mail->Subject = 'Đặt lại mật khẩu';
        $mail->Body = "Nhấp vào liên kết sau để đặt lại mật khẩu: <a href='$resetLink'>$resetLink</a>. Liên kết này có hiệu lực trong 1 giờ.";

        $mail->send();

        return true;
    } catch (Exception $e) {

        echo "Email sending failed: " . $mail->ErrorInfo . "<br>";
        return false;
    }
}
// Lưu token vào bảng users
function saveResetToken($userId, $token)
{
    global $conn;
    // $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $sql = "UPDATE users SET reset_token = ?, reset_expires_at = NOW() + INTERVAL 1 HOUR  WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $token, $userId);
    return $stmt->execute();
}

// Xóa token sau khi sử dụng
function deleteResetToken($token)
{
    global $conn;
    $sql = "UPDATE users SET reset_token = NULL, reset_expires_at = NULL WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    return $stmt->execute();
}

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $user = $userModel->login($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['role'] = $user['role'];

                $user1 = $userModel->getUserById($user['id']);
                $_SESSION['name'] = $user1['full_name'];

                if ($user['role'] === 'Admin') {
                    header("Location: ../Admin/DashboardController.php");
                    exit;
                } else if ($user['role'] === 'Employee') {
                    header("Location: ../User/attendance_checkController.php");
                    exit;
                } else {
                    session_destroy();
                    $error = "Tài khoản không có quyền truy cập!";
                }
            } else {
                $error = "Sai tài khoản hoặc mật khẩu!";
            }
        }
        include "../../views/Admin/Auth/login.php";
        break;
    case 'forgot_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $user = $userModel->getUserByEmail($email);

            if ($user) {
                $token = generateToken();
                $employeeEmail = $userModel->getEmployeeById($user['employee_id'])['email'];
                if ($employeeEmail && saveResetToken($user['id'], $token) && sendResetLink($employeeEmail, $token)) {
                    $success = "Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn!";
                } else {
                    $error = "Không thể gửi liên kết. Vui lòng thử lại! Lỗi: " . (isset($mail) ? $mail->ErrorInfo : 'Không xác định');
                }
            } else {
                $error = "Email không tồn tại trong hệ thống!";
            }
        }
        include "../../views/Admin/Auth/forgot_password.php";
        break;

    case 'reset_password':
        if ($token) {
            $sql = "SELECT id, username FROM users WHERE reset_token = ? AND reset_expires_at > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $new_password = $_POST['new_password'];
                    $renew_password = $_POST['renew_password'];

                    if ($new_password !== $renew_password) {
                        $error = "Mật khẩu mới không khớp!";
                    } else {
                        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                        if ($userModel->updatePassword($user['id'], $hashedPassword)) {
                            deleteResetToken($token);
                            $success = "Mật khẩu đã được đặt lại thành công!";
                            header("Location: AuthController.php?action=login");
                            exit;
                        } else {
                            $error = "Lỗi khi đặt lại mật khẩu!";
                        }
                    }
                }
            } else {
                $error = "Liên kết không hợp lệ hoặc đã hết hạn!";
            }
        } else {
            $error = "Không có token được cung cấp!";
        }
        include "../../views/Admin/Auth/reset_password.php";
        break;

    case 'logout':
        session_destroy();
        header("Location: AuthController.php?action=login");
        exit;
        break;

    default:
        echo "Hành động không hợp lệ!";
        break;
}
