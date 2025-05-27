<?php
session_start();
require_once __DIR__ . '/config.php';

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Lấy `user_id` từ yêu cầu GET hoặc POST
$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'Missing user ID']);
    exit;
}

// Xử lý thay đổi thông tin người dùng nếu có POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];

    // Thay đổi vai trò người dùng
     // Thay đổi vai trò người dùng
    if (isset($_POST['role'])) {
        $new_role = (int)$_POST['role'];
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :user_id");
            $stmt->execute(['role' => $new_role, 'user_id' => $user_id]);
            $response['success'] = 'User role updated successfully'; // Thay đổi phản hồi thành success
        } catch (PDOException $e) {
            $response['error'] = 'Failed to update user role: ' . $e->getMessage();
        }
    }

    // Thay đổi mật khẩu người dùng
if (isset($_POST['new_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT); // Mã hóa mật khẩu mới
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        $stmt->execute(['password' => $new_password, 'user_id' => $user_id]);
        $response['success'] = 'Password updated successfully'; // Phản hồi cho JavaScript để xử lý thành công
    } catch (PDOException $e) {
        $response['error'] = 'Failed to update password: ' . $e->getMessage();
    }
}

    // Trả về kết quả cập nhật
    echo json_encode($response);
    exit; // Dừng lại sau khi xử lý POST
}

try {
    // 1. Lấy thông tin người dùng từ bảng `users`
    $user_stmt = $pdo->prepare("SELECT id, username, role, refer_code, refered, created_at, updated_at FROM users WHERE id = :user_id");
    $user_stmt->execute(['user_id' => $user_id]);
    $user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // 2. Lấy danh sách hóa đơn đã thanh toán thành công của người dùng từ bảng `invoice`
    $invoice_stmt = $pdo->prepare("SELECT id, package_name, created_at, expire_at, status FROM invoices WHERE user_id = :user_id AND status = 'paid'");
    $invoice_stmt->execute(['user_id' => $user_id]);
    $invoices = $invoice_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Lấy dữ liệu biểu đồ bot của người dùng từ bảng `bots`
    // Lấy số lượng bot trong 30 ngày qua
    $bots_stmt = $pdo->prepare("SELECT DATE(created_at) AS date, COUNT(*) AS bot_count
                                FROM bots
                                WHERE user_id = :user_id AND created_at >= CURDATE() - INTERVAL 30 DAY
                                GROUP BY DATE(created_at)
                                ORDER BY DATE(created_at)");
    $bots_stmt->execute(['user_id' => $user_id]);
    $bots_data = $bots_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Lấy tổng số bot của người dùng (để hiển thị biểu đồ tổng)
    $total_bots_stmt = $pdo->prepare("SELECT COUNT(*) AS total_bots FROM bots WHERE user_id = :user_id");
    $total_bots_stmt->execute(['user_id' => $user_id]);
    $total_bots = $total_bots_stmt->fetch(PDO::FETCH_ASSOC)['total_bots'];

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode([
        'user_info' => $user_info,
        'invoices' => $invoices,
        'bots_data' => $bots_data,
        'total_bots' => $total_bots
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
