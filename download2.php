<?php 
session_start();
include __DIR__ . "/api/config.php";

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'User not found';
    exit();
}

$username = $user['username'];

$ftp_server = "ftp.greensyssolutions.com";
$ftp_username = "logs@greensyssolutions.com";
$ftp_password = "he;Mi4W5[[zV";
$logFile = 'logftp.txt';

function writeLog($message, $logFile) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    writeLog("Failed to connect to FTP server", $logFile);
    die('Cannot connect to FTP server');
} else {
    writeLog("Connected to FTP server", $logFile);
}

if (!ftp_login($conn_id, $ftp_username, $ftp_password)) {
    writeLog("FTP login failed", $logFile);
    ftp_close($conn_id);
    die('FTP login failed');
} else {
    writeLog("FTP login successful", $logFile);
}

ftp_pasv($conn_id, true);
writeLog("Set passive mode", $logFile);

if (isset($_GET['stub']) && isset($_GET['file'])) {
    echo 'Error: Only one of stub or file can be provided.';
    exit();
}

function checkFileExistsOnFTP($conn_id, $userFolder, $file) {
    if (!@ftp_chdir($conn_id, $userFolder)) {
        return false;
    }
    
    $file_list = ftp_nlist($conn_id, ".");
    if ($file_list === false) {
        return false;
    }
    
    foreach ($file_list as $existingFile) {
        if (basename($existingFile) === $file) {
            return true;
        }
    }

    return false;
}

function downloadFileManually($conn_id, $remoteFile, $localFile, $logFile) {
    $tempHandle = fopen($localFile, 'w');
    if (!$tempHandle) {
        writeLog("Failed to create local file handle", $logFile);
        return false;
    }

    $result = ftp_fget($conn_id, $tempHandle, $remoteFile, FTP_BINARY, 0);
    fclose($tempHandle);

    if ($result) {
        writeLog("File downloaded successfully: $remoteFile", $logFile);
    } else {
        writeLog("Failed to download file: $remoteFile", $logFile);
    }

    return $result;
}

if (isset($_GET['stub'])) {
    $stub = basename($_GET['stub']);
    $userFolder = '/' . $username;

    if (checkFileExistsOnFTP($conn_id, $userFolder, $stub)) {
        writeLog("Stub found on FTP: $stub", $logFile);
        $local_file = tempnam(sys_get_temp_dir(), 'ftp_') . '.rar';
        $ftp_stub_path = $userFolder . '/' . $stub;

        if (downloadFileManually($conn_id, $ftp_stub_path, $local_file, $logFile)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($ftp_stub_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($local_file));

            readfile($local_file);
            unlink($local_file);
        } else {
            echo 'Failed to download file from FTP';
        }

    } else {
        writeLog("Stub not found on FTP: $stub", $logFile);
        echo 'Stub not found on FTP';
    }

} elseif (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $userFolder = '/' . $username;

    if (checkFileExistsOnFTP($conn_id, $userFolder, $file)) {
        writeLog("File found on FTP: $file", $logFile);
        $local_file = tempnam(sys_get_temp_dir(), 'ftp_') . '.rar';
        $ftp_file_path = $userFolder . '/' . $file;

        if (downloadFileManually($conn_id, $ftp_file_path, $local_file, $logFile)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($ftp_file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($local_file));

            readfile($local_file);
            unlink($local_file);
        } else {
            echo 'Failed to download file from FTP';
        }

    } else {
        writeLog("File not found on FTP: $file", $logFile);
        echo 'File not found on FTP';
    }
} else {
    echo 'Error: No file or stub specified.';
}

ftp_close($conn_id);
?>
