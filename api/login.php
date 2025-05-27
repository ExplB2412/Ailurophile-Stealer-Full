<?php
// Bắt đầu session
session_start();
if (isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

// Bao gồm cấu hình cơ sở dữ liệu
require __DIR__ . "/config.php";

// Bao gồm xử lý CAPTCHA
require_once __DIR__ . '/../captcha/securimage.php';
$securimage = new Securimage();

// Kiểm tra nếu có yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $captcha = trim($_POST['captcha']);

    // Kiểm tra CAPTCHA
    if ($securimage->check($captcha) == false) {
        echo json_encode(['status' => 'error', 'message' => 'Wrong captcha']);
        exit();
    }

    // Kiểm tra các trường bắt buộc
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username/password']);
        exit();
    }

    // Kiểm tra username trong cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
		$_SESSION['just_logged_in'] = true;
        echo json_encode(['status' => 'success', 'message' => 'Login success']);
    } else {
        // Đăng nhập thất bại
        echo json_encode(['status' => 'error', 'message' => 'Invalid username/password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
