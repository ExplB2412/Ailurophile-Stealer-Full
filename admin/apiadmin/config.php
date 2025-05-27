<?php
$servername = "localhost";
$username = "ailurophile"; // thay đổi với username của bạn
$password = "Dinokulz123."; // thay đổi với mật khẩu của bạn
$dbname = "ailurophile"; // thay đổi với tên database của bạn
$admin_pass = "MrAnonTestAdmin"; // Mật khẩu cho admin

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Đặt chế độ lỗi PDO thành ngoại lệ
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>
