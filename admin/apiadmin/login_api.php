<?php
session_start();
require_once __DIR__ . '/config.php'; // Kết nối database

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';

    // Kiểm tra mật khẩu admin
    if ($password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true; // Lưu session đăng nhập
        $response['status'] = 'success';
        $response['message'] = 'Đăng nhập thành công!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Mật khẩu không đúng!';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Phương thức không hợp lệ!';
}

// Trả về phản hồi JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
