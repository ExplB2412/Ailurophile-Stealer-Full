<?php

$servername = "localhost";
$username = "ailurophile"; // thay đổi với username của bạn
$password = "Dinokulz123."; // thay đổi với mật khẩu của bạn
$dbname = "ailurophile"; // thay đổi với tên database của bạn

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Truy vấn tất cả các bot có status = 'active' và xếp theo id giảm dần
$stmt = $pdo->prepare("SELECT id, ip, other_info, last_time_connect FROM botnet_bot WHERE status = 'active' ORDER BY id DESC");
$stmt->execute();
$bots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trả về kết quả dưới dạng JSON
echo json_encode($bots);