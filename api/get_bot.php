<?php
session_start();
include __DIR__ . "/config.php";

// Kiểm tra xem user_id có tồn tại trong session hay không
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy tham số page từ yêu cầu GET và kiểm tra giá trị hợp lệ
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$page = ($page > 5) ? 5 : $page; // Tối đa là 5 trang

$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Lấy 10 bots từ cơ sở dữ liệu theo user_id và phân trang
    $sql = "SELECT * FROM bots WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $limit, PDO::PARAM_INT);
    $stmt->bindParam(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $bots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $bots, 'page' => $page]);

} catch (PDOException $e) {
    // Trả về lỗi trong trường hợp gặp lỗi cơ sở dữ liệu
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
?>
