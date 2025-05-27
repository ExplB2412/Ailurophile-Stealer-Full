<?php
session_start();
require __DIR__ . "/config.php";
include_once __DIR__ . '/vendor/autoload.php';
use CoinRemitter\CoinRemitter;

// Cấu hình các tham số cho CoinRemitter
$params = [
    'coin' => 'BTC', // Loại tiền điện tử
    'api_key' => '$2b$10$draANsHqmUclADFRf3atIeF6CY7Bvp4ZNnANHikkSMTVadpkI/266', // Thay YOUR_API_KEY bằng API key từ ví CoinRemitter của bạn
    'password' => 'BaBinh2412' // Thay YOUR_PASSWORD bằng mật khẩu ví CoinRemitter của bạn
];
$obj = new CoinRemitter($params);

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login for purchase']);
    exit();
}

// Lấy thông tin người dùng từ session
$user_id = $_SESSION['user_id'];

// Kiểm tra xem có tham số 'package' trong yêu cầu GET không
if (isset($_GET['package'])) {
    $packages = [
        '1' => ['amount' => '80', 'name' => 'Standard-month', 'description' => 'Standard-month'],
        '2' => ['amount' => '400', 'name' => 'Standard-6month', 'description' => 'Standard-6month'],
        '3' => ['amount' => '1280', 'name' => 'Standard-lifetime', 'description' => 'Standard-lifetime'],
        '4' => ['amount' => '140', 'name' => 'Premium-month', 'description' => 'Premium-month'],
        '5' => ['amount' => '700', 'name' => 'Premium-6month', 'description' => 'Premium-6month'],
        '6' => ['amount' => '2240', 'name' => 'Premium-lifetime', 'description' => 'Premium-lifetime'],
        '7' => ['amount' => '250', 'name' => 'VIP-month', 'description' => 'VIP-month'],
        '8' => ['amount' => '1250', 'name' => 'VIP-6month', 'description' => 'VIP-6month'],
        '9' => ['amount' => '4000', 'name' => 'VIP-lifetime', 'description' => 'VIP-lifetime'],
    ];

    // Kiểm tra xem package có hợp lệ không
    $packageId = $_GET['package'];
    if (array_key_exists($packageId, $packages)) {
        $package = $packages[$packageId];
        
        // Lấy thời gian hiện tại và thời gian hết hạn
        $created_at = date('Y-m-d H:i:s');
        $expire_at = date('Y-m-d H:i:s', strtotime('+240 minutes'));

        // Chuẩn bị câu lệnh SQL để chèn dữ liệu vào bảng
        $stmt = $pdo->prepare("INSERT INTO invoices (user_id, package_name, created_at, expire_at, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $package['name'], $created_at, $expire_at, 'pending']);
        
        // Lấy ID của hóa đơn vừa chèn
        $invoice_id = $pdo->lastInsertId();

        $invoice_params = [
            'amount' => $package['amount'], // Số tiền
            'notify_url' => 'https://ailurophilestealer.com/payment/notify-payment-coinremitter', // URL thông báo
            'name' => $package['name'], // Tên gói dịch vụ
            'currency' => 'USD', // Loại tiền tệ
            'expire_time' => 240, // Thời gian hết hạn (tính bằng phút)
            'description' => $package['description'], // Mô tả
            'custom_data1' => $invoice_id, // ID hóa đơn trong cơ sở dữ liệu của bạn
            'custom_data2' => '' // Dữ liệu tùy chỉnh
        ];

        try {
            // Tạo hóa đơn
            $invoice = $obj->create_invoice($invoice_params);

            // Hiển thị thông tin hóa đơn
			$data_response = $invoice['data'];
			$wallet_payment = $data_response['address'];
			$amount_btc = $data_response['total_amount']['BTC'];
			echo json_encode(['status' => 'success', 'message'=>"Please make a payment within 240 minutes", 'address' => $wallet_payment, 'amount'=>$amount_btc]);
        } catch (Exception $e) {
            // Xử lý lỗi
            echo json_encode(['status' => 'error', 'message'=>"Invalid request"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message'=>"Invalid package"]);
    }
} else {
    echo json_encode(['status' => 'error', 'message'=>"Invalid package"]);
}
?>
