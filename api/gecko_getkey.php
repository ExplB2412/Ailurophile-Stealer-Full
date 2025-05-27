<?php

// Hàm giải mã AES
function decrypt_aes($decoded_item, $master_password, $global_salt) {
    $entry_salt = $decoded_item[0][0][1][0][1][0];
    $iteration_count = $decoded_item[0][0][1][0][1][1];
    $encoded_password = hash('sha1', $global_salt . $master_password, true);
    $key = hash_pbkdf2('sha256', $encoded_password, $entry_salt, $iteration_count, 32, true);
    $init_vector = "\x04\x0e" . $decoded_item[0][0][1][1][1];
    $encrypted_value = $decoded_item[0][1];
    return openssl_decrypt($encrypted_value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $init_vector);
}

// Hàm giải mã 3DES
function decrypt3DES($globalSalt, $masterPassword, $entrySalt, $encryptedData) {
    $hp = hash('sha1', $globalSalt . $masterPassword, true);
    $pes = str_pad($entrySalt, 20, "\x00");
    $chp = hash('sha1', $hp . $entrySalt, true);
    $k1 = hash_hmac('sha1', $pes . $entrySalt, $chp, true);
    $k2 = hash_hmac('sha1', hash_hmac('sha1', $pes, $chp, true) . $entrySalt, $chp, true);
    $key = $k1 . $k2;
    $iv = substr($key, -8);
    $key = substr($key, 0, 24);
    return openssl_decrypt($encryptedData, 'DES-EDE3-CBC', $key, OPENSSL_RAW_DATA, $iv);
}

// Hàm lấy key từ file key4.db
function getKey($dbPath, $masterPassword = "") {
    if (!file_exists($dbPath)) {
        throw new Exception("File key4.db not found.");
    }

    $conn = new SQLite3($dbPath);

    // Truy vấn metadata
    $metadata = $conn->querySingle("SELECT item1, item2 FROM metadata", true);
    if (!$metadata) {
        throw new Exception("Metadata not found in database.");
    }

    $globalSalt = $metadata['item1'];
    $item2 = $metadata['item2'];

    // Xác định phương pháp mã hóa
    try {
        $decodedItem2 = decode($item2);
        $encryptionMethod = '3DES';
        $entrySalt = $decodedItem2[0][1][0];
        $cipherT = $decodedItem2[1];
    } catch (Exception $e) {
        $encryptionMethod = 'AES';
        $decodedItem2 = decode($item2);
    }

    // Truy vấn nssPrivate
    $stmt = $conn->prepare("SELECT a11, a102 FROM nssPrivate WHERE a102 = ?");
    $a102Value = hex2bin("f800000000000000000000000000000000000001");
    $stmt->bindValue(1, $a102Value, SQLITE3_BLOB);
    $result = $stmt->execute();

    $privateData = $result->fetchArray(SQLITE3_ASSOC);
    if (!$privateData) {
        throw new Exception("No private data found in nssPrivate.");
    }

    $a11 = $privateData['a11'];

    // Giải mã key
    if ($encryptionMethod === 'AES') {
        $decodedA11 = decode($a11);
        $key = decrypt_aes($decodedA11, $masterPassword, $globalSalt);
    } elseif ($encryptionMethod === '3DES') {
        $decodedA11 = decode($a11);
        $entrySalt = $decodedA11[0][1][0];
        $cipherT = $decodedA11[1];
        $key = decrypt3DES($globalSalt, $masterPassword, $entrySalt, $cipherT);
    }

    return substr($key, 0, 24); // Trả về 24 byte đầu tiên
}

// Hàm decode dữ liệu ASN.1
function decode($data) {
    // Đây là một placeholder. Thay bằng thư viện xử lý ASN.1 hoặc phương pháp giải mã phù hợp.
    return $data; // Cần sửa đổi nếu cấu trúc dữ liệu phức tạp
}

// Xử lý file tải lên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['key4db'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($_FILES['key4db']['name']);
    if (move_uploaded_file($_FILES['key4db']['tmp_name'], $filePath)) {
        try {
            $key = getKey($filePath);
            echo json_encode([
                'message' => 'success',
                'key' => bin2hex($key),
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'message' => 'failed',
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'message' => 'failed',
            'error' => 'File upload failed.'
        ]);
    }
} else {
    echo json_encode([
        'message' => 'failed',
        'error' => 'Invalid request.'
    ]);
}
?>
