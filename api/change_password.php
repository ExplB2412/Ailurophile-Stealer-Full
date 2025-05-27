<?php
session_start();
include __DIR__ . "/config.php";

// Kiểm tra xem user_id có tồn tại trong session hay không
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra nếu yêu cầu là POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
    $new_password1 = isset($_POST['password1']) ? trim($_POST['password1']) : '';
    $new_password2 = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Kiểm tra các trường bắt buộc
    if (empty($old_password) || empty($new_password1) || empty($new_password2)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }

    // Kiểm tra mật khẩu mới có khớp nhau không
    if ($new_password1 !== $new_password2) {
        echo json_encode(['status' => 'error', 'message' => 'New passwords do not match']);
        exit();
    }

    // Lấy thông tin người dùng từ cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit();
    }

    // Kiểm tra mật khẩu cũ
    if (!password_verify($old_password, $user['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Old password is incorrect']);
        exit();
    }

    // Mã hóa mật khẩu mới
    $hashed_password = password_hash($new_password1, PASSWORD_DEFAULT);

    // Cập nhật mật khẩu mới vào cơ sở dữ liệu
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);

    echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
