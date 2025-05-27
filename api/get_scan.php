<?php

if (isset($_POST['scan_token'])) {
    $res = check_result($_POST['scan_token']);
    header('Content-Type: application/json');
    echo json_encode($res);
}

function check_result($token) {
    $url = "https://kleenscan.com/api/v1/file/result/" . $token;
    $headers = array(
        "X-Auth-Token: af8fe83e982036aeac8ff6bc6f37cd9dca336c3d205e55ac2e864bfb04c7546d"
    );

    do {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Không xác thực chứng chỉ SSL
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        // Kiểm tra lỗi cURL
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return ["error" => "cURL error: $error_msg"];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Giải mã JSON phản hồi
        $result = json_decode($result, true);

        // Kiểm tra phản hồi API
        if ($httpCode === 200 && isset($result['success']) && $result['success'] === true) {
            // Kiểm tra nếu tất cả status trong `data` là "ok"
            $allOk = true;
            foreach ($result['data'] as $scanResult) {
                if ($scanResult['status'] === 'scanning') {
                    $allOk = false;
                    break;
                }
            }

            // Nếu tất cả đều là "ok", trả về `data`
            if ($allOk) {
                return $result['data'];
            } else {
                // Chờ 2 giây trước khi thử lại
                sleep(2);
            }
        } else {
            // Trả về lỗi nếu không thành công
            return ["error" => isset($result['message']) ? $result['message'] : "Unknown error"];
        }
    } while (true);
}
