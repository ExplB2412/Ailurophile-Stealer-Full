<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "ailurophile";
$password = "Dinokulz123.";
$dbname = "ailurophile";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Kiểm tra nếu user_id được gửi qua phương thức GET
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Truy vấn để lấy role của user
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Lấy dữ liệu từ kết quả truy vấn
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra nếu có dữ liệu
    if ($result) {
        // Trả về JSON role của user
        echo json_encode(['role' => $result['role']]);
    } else {
        // Nếu không tìm thấy user
        echo json_encode(['error' => 'User not found']);
    }
} else {
    // Nếu không có user_id trong request
    echo json_encode(['error' => 'No user_id provided']);
}
?>
