<?php
// Bắt đầu session
session_start();

// Include the database connection file
require_once 'config.php';

// Kiểm tra xem session user_id có tồn tại không
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}

// Lấy user_id từ session
$user_id = (int)$_SESSION['user_id'];

$response = [];

// 1. Total bot count (Tổng số bot)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_bots FROM bots WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_bots = $stmt->fetch(PDO::FETCH_ASSOC)['total_bots'];
    $response['total_bots'] = $total_bots;
} catch (PDOException $e) {
    $response['error'] = 'Failed to fetch total bots: ' . $e->getMessage();
}

// 2. Total bots created today (Tổng số bot hôm nay)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_bots_today FROM bots
                           WHERE user_id = :user_id AND DATE(created_at) = CURDATE()");
    $stmt->execute(['user_id' => $user_id]);
    $total_bots_today = $stmt->fetch(PDO::FETCH_ASSOC)['total_bots_today'];
    $response['total_bots_today'] = $total_bots_today;
} catch (PDOException $e) {
    $response['error'] = 'Failed to fetch total bots today: ' . $e->getMessage();
}

// 3. Bot count for the last 30 days (Số lượng bot trong 30 ngày qua - Biểu đồ đường)
try {
    $stmt = $pdo->prepare("SELECT DATE(created_at) AS date, COUNT(*) AS bot_count
                           FROM bots
                           WHERE user_id = :user_id AND created_at >= CURDATE() - INTERVAL 30 DAY
                           GROUP BY DATE(created_at)
                           ORDER BY DATE(created_at)");
    $stmt->execute(['user_id' => $user_id]);
    $bot_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['bot_counts'] = $bot_counts;
} catch (PDOException $e) {
    $response['error'] = 'Failed to fetch bot counts for the last 30 days: ' . $e->getMessage();
}

// 4. Bot country distribution (Phân bố bot theo quốc gia - Biểu đồ tròn)
try {
    $stmt = $pdo->prepare("SELECT bot_country, COUNT(*) AS bot_count
                           FROM bots
                           WHERE user_id = :user_id
                           GROUP BY bot_country");
    $stmt->execute(['user_id' => $user_id]);
    $bot_country_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['bot_country_distribution'] = $bot_country_distribution;
} catch (PDOException $e) {
    $response['error'] = 'Failed to fetch bot country distribution: ' . $e->getMessage();
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
