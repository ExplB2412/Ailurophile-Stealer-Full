<?php 
session_start();
include __DIR__ . "/config.php";

// Đường dẫn tới file log
$logFilePath = __DIR__ . "/log.txt";

// Thiết lập header JSON
header('Content-Type: application/json');

// Kiểm tra xem user đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in", 3, $logFilePath);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra xem có bots được gửi qua POST không
if (!isset($_POST['bots']) || empty($_POST['bots'])) {
    error_log("No bots selected", 3, $logFilePath);
    echo json_encode(['status' => 'error', 'message' => 'No bots selected']);
    exit();
}

$bots = $_POST['bots'];
$botIds = explode(',', $bots);
$zipFileName = 'bots_' . time() . '.zip'; // Đổi tên file thành .zip
$zipFilePath = __DIR__ . '/../herasvnxailurophile/' . $zipFileName; // Lưu tại thư mục herasvnxailurophile

// Thư mục chứa các bot
$baseDir = __DIR__ . '/../herasvnxailurophile/';

// Đường dẫn đến 7z.exe trên Windows
$sevenZipPath = '"C:\\Program Files\\7-Zip\\7z.exe"';

// Tạo danh sách các file để nén
$fileList = [];
foreach ($botIds as $botId) {
    // Kiểm tra xem bot có thuộc về user không
    $sql = "SELECT path_file FROM bots WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$botId, $user_id]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ghi log kiểm tra từng bot
    error_log("Checking bot ID: $botId, path_file: " . ($bot['path_file'] ?? 'null') . "\n", 3, $logFilePath);

    // Nếu bot tồn tại và thuộc sở hữu của user, kiểm tra xem file có tồn tại hay không
    if ($bot && file_exists($baseDir . $bot['path_file'])) {
        $fileList[] = escapeshellarg($baseDir . $bot['path_file']); // Bảo vệ tên file bằng escapeshellarg
    } else {
        // Nếu file không tồn tại hoặc không thuộc user, ghi log và tiếp tục
        error_log("File does not exist or bot does not belong to user: " . $baseDir . $bot['path_file'] . "\n", 3, $logFilePath);
        continue; // Bỏ qua bot này và tiếp tục với bot tiếp theo
    }
}

// Kiểm tra nếu có file hợp lệ để nén
if (empty($fileList)) {
    error_log("No valid files to compress. List of files: " . print_r($fileList, true) . "\n", 3, $logFilePath);
    echo json_encode(['status' => 'error', 'message' => 'No valid files to compress']);
    exit();
}

// Chuẩn bị lệnh nén với 7-Zip
$command = $sevenZipPath . " a " . escapeshellarg($zipFilePath) . " " . implode(' ', $fileList);

// Thực thi lệnh
exec($command, $output, $returnVar);

// Kiểm tra nếu lệnh thực thi thành công
if ($returnVar !== 0) {
    error_log("7-Zip compression failed. Command: $command\n", 3, $logFilePath);
    echo json_encode(['status' => 'error', 'message' => '7-Zip compression failed']);
    exit();
}

// Kiểm tra nếu file zip tồn tại
if (file_exists($zipFilePath)) {
    error_log("7-Zip file created successfully: $zipFilePath\n", 3, $logFilePath);

    // Trả về kết quả JSON với tên file zip
    echo json_encode(['status' => 'success', 'message' => $zipFileName]);
    exit();
} else {
    error_log("7-Zip file creation failed. Zip path: $zipFilePath\n", 3, $logFilePath);
    echo json_encode(['status' => 'error', 'message' => '7-Zip file creation failed']);
    exit();
}
?>
