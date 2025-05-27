<?php
session_start();
include __DIR__ . "/api/config.php";

// Kiểm tra xem user_id có tồn tại trong session hay không
if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra xem có cả `stub` và `file` trong yêu cầu GET hay không
if (isset($_GET['stub']) && isset($_GET['file'])) {
    echo 'Error: Only one of stub or file can be provided.';
    exit();
}

// Kiểm tra nếu có yêu cầu GET với `stub`
if (isset($_GET['stub'])) {
    $file_path = __DIR__ . '/builded/' . basename($_GET['stub']); 

    if (!file_exists($file_path)) {
        echo 'File not found';
        exit();
    }
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
    exit();

} elseif (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Sử dụng `basename` để ngăn chặn LFI

    if (empty($file)) {
        echo 'No file specified';
        exit();
    }

    // Kiểm tra xem file có thuộc về user_id hay không
    $stmt = $pdo->prepare("SELECT * FROM bots WHERE user_id = ? AND path_file = ?");
    $stmt->execute([$user_id, $file]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bot) {
        echo 'File not found or access denied';
        exit();
    }

    // Đường dẫn tới file cần tải
    $file_path = __DIR__ . '/herasvnxailurophile/' . $file;

    // Kiểm tra xem file có tồn tại không
    if (!file_exists($file_path)) {
        echo 'File not found';
        exit();
    }

    // Thiết lập tiêu đề tải xuống
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Đọc và gửi nội dung tệp tới người dùng
    readfile($file_path);
    exit();

} else {
    echo 'Error: No file or stub specified.';
    exit();
}
?>
