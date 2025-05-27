<?php

function scan_file() {
    $url = "https://kleenscan.com/api/v1/file/scan";
    $path = "C:\\xampp\\htdocs\\ailurophilepy\\dist\\7z2408-x64.exe";
    
    // Kiểm tra xem file có tồn tại không trước khi gửi request
    if (!file_exists($path)) {
        return "Scan error";
    }

    $file = new \CURLFile($path);
    $params = array(
        "avList" => "all",
        "path" => $file
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: multipart/form-data',
        'X-Auth-Token: af8fe83e982036aeac8ff6bc6f37cd9dca336c3d205e55ac2e864bfb04c7546d'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $result = curl_exec($ch);

    // Kiểm tra lỗi cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "cURL error: $error_msg";
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($result, true);

    // Kiểm tra phản hồi và mã HTTP
    if ($httpCode === 200 && isset($result['success']) && $result['success'] === true && isset($result['data']['scan_token'])) {
        return $result['data']['scan_token']; // Trả về scan_token nếu thành công
    } else {
        return "Scan error"; // Nếu không thành công, trả về thông báo lỗi
    }
}

	/*
	
	 ["success"]=>
  bool(true)
  ["httpResponseCode"]=>
  int(200)
  ["message"]=>
  string(2) "OK"
  ["data"]=>
  array(2) {
    [0]=>
    array(4) {
      ["avname"]=>
      string(7) "adaware"
      ["status"]=>
      string(2) "scanning"
      ["flagname"]=>
      string(10) "Undetected"
      ["lastupdate"]=>
      string(10) "2024-10-31"
    }
    [1]=>
    array(4) {
      ["avname"]=>
      string(5) "alyac"
      ["status"]=>
      string(2) "ok"
      ["flagname"]=>
      string(10) "Undetected"
      ["lastupdate"]=>
      string(10) "2024-10-30"
    }
	
	*/
	


/*$token = scan_file();
var_dump($token);
$res = check_result($token);
var_dump($res);*/

?>