<?php
// Thông tin kết nối cơ sở dữ liệu
$dsn = 'mysql:host=localhost;dbname=ailurophile;charset=utf8';
$username = 'root';
$password = '';

try {
    // Tạo kết nối PDO
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy thời gian hiện tại
    $currentDate = date('Y-m-d H:i:s');

    // Câu truy vấn để tìm các subscription đã hết hạn nhưng vẫn còn đang active (status khác 'expired')
    $sql = "SELECT user_id FROM subscriptions WHERE expire_at <= :currentDate AND status != 'expired'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $stmt->execute();
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cập nhật role và trạng thái subscription của các user đã hết hạn
    $updateUserSql = "UPDATE users SET role = 1 WHERE id = :userId";
    $updateSubscriptionSql = "UPDATE subscriptions SET status = 'expired' WHERE user_id = :userId";

    $updateUserStmt = $pdo->prepare($updateUserSql);
    $updateSubscriptionStmt = $pdo->prepare($updateSubscriptionSql);

    foreach ($subscriptions as $subscription) {
        $userId = $subscription['user_id'];
        
        // Cập nhật role của user thành 1
        $updateUserStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $updateUserStmt->execute();

        // Cập nhật trạng thái subscription thành 'expired'
        $updateSubscriptionStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $updateSubscriptionStmt->execute();
    }

    echo "Subscription check completed.";

} catch (PDOException $e) {
    // Xử lý lỗi kết nối cơ sở dữ liệu
    echo "Connection failed: " . $e->getMessage();
}
?>
