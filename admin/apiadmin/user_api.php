<?php
session_start();
require_once __DIR__ . '/config.php';

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Lấy danh sách người dùng từ bảng users
    $stmt = $pdo->prepare("SELECT id, username, role, created_at FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Với mỗi người dùng, tính tổng số bot trong bảng `bots`
    foreach ($users as &$user) {
        $bot_stmt = $pdo->prepare("SELECT COUNT(*) AS total_bots FROM bots WHERE user_id = :user_id");
        $bot_stmt->execute(['user_id' => $user['id']]);
        $bot_result = $bot_stmt->fetch(PDO::FETCH_ASSOC);
        $user['total_bots'] = $bot_result['total_bots'] ?? 0; // Gán tổng số bots hoặc 0 nếu không có
    }

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
