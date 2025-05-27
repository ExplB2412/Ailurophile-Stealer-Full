<?php

function getAutofillData($uploadedDbFile) {
    $autofillData = [];
    $totalAutofills = 0;

    // Kiểm tra xem file db có được tải lên hay không
    if (!file_exists($uploadedDbFile)) {
        echo "Database file not found.\n";
        return 0;
    }

    // Sao chép file tải lên vào thư mục tạm
    $autofillDbPath = sys_get_temp_dir() . '/uploaded_autofill.db';
    if (!copy($uploadedDbFile, $autofillDbPath)) {
        echo "Failed to copy the database file.\n";
        return 0;
    }

    // Kết nối tới cơ sở dữ liệu SQLite
    $db = new SQLite3($autofillDbPath);
    if (!$db) {
        echo "Failed to connect to the database.\n";
        return 0;
    }

    // Truy vấn dữ liệu từ bảng autofill
    $query = 'SELECT name, value FROM autofill';
    $results = $db->query($query);

    // Duyệt qua các bản ghi autofill
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        if (empty($row['name']) || empty($row['value'])) {
            continue; // Bỏ qua nếu không có dữ liệu autofill hợp lệ
        }

        // In ra định dạng yêu cầu
        echo $row['name'] . "|" . $row['value'] . "\n";
        $totalAutofills++;
    }

    $db->close();

    // Trả về tổng số autofill tìm được
    return $totalAutofills;
}

// Phần main xử lý yêu cầu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dbfile'])) {
    $uploadedDbFile = $_FILES['dbfile']['tmp_name'];

    // Gọi hàm getAutofillData với file tải lên
    $totalAutofills = getAutofillData($uploadedDbFile);

    // In ra tổng số autofills đã lấy được
if($totalAutofills==0){echo "No data found";}
} else {
    // Trả về thông báo lỗi nếu yêu cầu không đúng
    echo "Please provide a database file.";
}
