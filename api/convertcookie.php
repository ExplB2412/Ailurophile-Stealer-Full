<?php

// API nhận request AJAX tại /api/convert
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nhận dữ liệu từ POST
    $cookieString = $_POST['cookie'] ?? '';

    // Function chuyển đổi cookie sang JSON (như đã viết ở trên)
    function convertCookiesToJson($cookieString) {
        $cookies = explode("\n", $cookieString);
        $result = [];

        foreach ($cookies as $cookie) {
            // Bỏ qua dòng trống hoặc dòng không hợp lệ
            if (trim($cookie) === '') {
                continue;
            }

            // Tách các phần của cookie dựa trên ký tự tab (\t)
            $parts = explode("\t", $cookie);
            
            // Kiểm tra nếu dòng có đủ 7 phần (định dạng cookie hợp lệ)
            if (count($parts) < 7) {
                continue;
            }

            // Tạo đối tượng cookie
            $cookieData = [
                'domain' => $parts[0],
                'path' => $parts[2],
                'name' => $parts[5],
                'value' => $parts[6]
            ];

            // Thêm vào mảng kết quả
            $result[] = $cookieData;
        }

        // Sắp xếp cookies theo domain và name
        usort($result, function($a, $b) {
            $domainComparison = strcmp($a['domain'], $b['domain']);
            if ($domainComparison === 0) {
                return strcmp($a['name'], $b['name']);
            }
            return $domainComparison;
        });

        // Trả về kết quả dưới dạng JSON
        return $result;
    }

    // Gửi lại kết quả dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode(convertCookiesToJson($cookieString));
}
