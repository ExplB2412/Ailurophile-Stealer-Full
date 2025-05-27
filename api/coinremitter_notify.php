<?php
require __DIR__ . "/config.php";

// Nhận thông báo thanh toán từ CoinRemitter qua POST
$data = $_POST;

// Ghi lại thông báo nhận được vào file log để kiểm tra và gỡ lỗi
//file_put_contents("log_payment.txt", json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND);

if (isset($data['custom_data1']) && isset($data['status']) && isset($data['status_code']) && isset($data['name'])) {
    $invoice_id = $data['custom_data1'];
    $status = $data['status']; // Trạng thái thanh toán từ CoinRemitter
    $status_code = $data['status_code'];
    $package_name = $data['name'];

    // Xác minh rằng dữ liệu hợp lệ trước khi cập nhật cơ sở dữ liệu
    if (in_array($status, ['Pending', 'Paid', 'Under Paid', 'Expired', 'Cancelled', 'Over Paid'])) {
        // Cập nhật trạng thái hóa đơn trong cơ sở dữ liệu
        $stmt = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
        $stmt->execute([$status, $invoice_id]);

        // Nếu status_code là 1 hoặc 3, thiết lập subscription cho người dùng
         if ($status_code == "1" || $status_code == "3") {
            // Lấy thông tin hóa đơn và người dùng từ cơ sở dữ liệu
            $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invoice) {
                $user_id = $invoice['user_id'];
                $package_id = array_search($package_name, [
                    '1' => 'Standard-month',
                    '2' => 'Standard-6month',
                    '3' => 'Standard-lifetime',
                    '4' => 'Premium-month',
                    '5' => 'Premium-6month',
                    '6' => 'Premium-lifetime',
                    '7' => 'VIP-month',
                    '8' => 'VIP-6month',
                    '9' => 'VIP-lifetime',
                ]);

                if ($package_id !== false) {
                    // Thiết lập user_role và thời gian subscription dựa trên package
                    $role_duration_map = [
                        '1' => ['role' => 2, 'duration' => '+1 month'],
                        '2' => ['role' => 2, 'duration' => '+6 months'],
                        '3' => ['role' => 2, 'duration' => '+999 months'],
                        '4' => ['role' => 3, 'duration' => '+1 month'],
                        '5' => ['role' => 3, 'duration' => '+6 months'],
                        '6' => ['role' => 3, 'duration' => '+999 months'],
                        '7' => ['role' => 4, 'duration' => '+1 month'],
                        '8' => ['role' => 4, 'duration' => '+6 months'],
                        '9' => ['role' => 4, 'duration' => '+999 months'],
                    ];

                    $role = $role_duration_map[$package_id]['role'];
                    $duration = $role_duration_map[$package_id]['duration'];

                    // Cập nhật user_role
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$role, $user_id]);

                    // Kiểm tra và hủy subscription hiện tại nếu còn hạn
                    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND expire_at > NOW() AND status = 'active'");
                    $stmt->execute([$user_id]);
                    $current_subscription = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($current_subscription) {
                        // Hủy subscription hiện tại
                        $stmt = $pdo->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE id = ?");
                        $stmt->execute([$current_subscription['id']]);
                    }

                    // Tạo subscription mới cho người dùng
                    $time_created = date('Y-m-d H:i:s');
                    $expire_at = date('Y-m-d H:i:s', strtotime($duration, strtotime($time_created)));

                    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, invoice_id, time_created, expire_at, status) VALUES (?, ?, ?, ?, 'active')");
                    $stmt->execute([$user_id, $invoice_id, $time_created, $expire_at]);

                    // Trả về phản hồi cho CoinRemitter
                    echo json_encode(['status' => 'success', 'message' => 'Payment notification received and subscription updated.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid package name.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            }
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Payment notification received.']);
        }
    } else {
        // Trạng thái không hợp lệ
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value.']);
    }
} else {
    // Thông báo không hợp lệ
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment notification.']);
}
?>
