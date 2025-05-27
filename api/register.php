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
    $role = 1; // Giá trị mặc định cho role, có thể thay đổi theo yêu cầu
    $refer_code = bin2hex(random_bytes(5)); // Tạo mã giới thiệu ngẫu nhiên
    $refered = isset($_POST['refered']) ? trim($_POST['refered']) : null;

    // Kiểm tra CAPTCHA
    if ($securimage->check($captcha) == false) {
        echo json_encode(['status' => 'error', 'message' => "Wrong captcha"]);
        exit();
    }

    // Kiểm tra các trường bắt buộc và độ dài
    if (empty($username) || strlen($username) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Username must be more than 6 characters']);
        exit();
    }

    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be more than 6 characters']);
        exit();
    }

    // Kiểm tra ký tự đặc biệt trong tên đăng nhập
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username contains invalid characters']);
        exit();
    }

    // Kiểm tra xem username đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username']);
        exit();
    }

    // Kiểm tra mã giới thiệu (refered)
    if ($refered === '1111111111111') {
        $role = 2; // Nếu ref là "freetrial", chỉnh role thành 2
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Lưu thông tin người dùng vào cơ sở dữ liệu
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, refer_code, refered, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $result = $stmt->execute([$username, $hashed_password, $role, $refer_code, $refered]);

    if ($result) {
        // Lấy ID của người dùng vừa được tạo
        $user_id = $pdo->lastInsertId();

        // Nếu ref là "freetrial", tạo invoice và subscription
        if ($refered === 'mfoasg1240asfpasf1') {
            // Tạo invoice
            $created_at = date('Y-m-d H:i:s');
            $expire_at = date('Y-m-d H:i:s', strtotime('+240 minutes'));

            $stmt = $pdo->prepare("INSERT INTO invoices (user_id, package_name, created_at, expire_at, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, 'freetrial', $created_at, $expire_at, 'paid']);

            // Lấy ID của invoice vừa được tạo
            $invoice_id = $pdo->lastInsertId();

            // Tạo subscription với thời hạn 3 ngày
            $time_created = date('Y-m-d H:i:s');
            $expire_at = date('Y-m-d H:i:s', strtotime('+3 days', strtotime($time_created)));

            $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, invoice_id, time_created, expire_at, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$user_id, $invoice_id, $time_created, $expire_at]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Request invalid']);
}
?>
