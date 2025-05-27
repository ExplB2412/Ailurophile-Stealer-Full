<?php
session_start();
include __DIR__ . "/apiadmin/config.php";

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: /users.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit User - Ailurophile Stealer Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Ailurophile Stealer Dashboard" name="keywords">
    <meta content="Ailurophile Stealer Dashboard" name="description">

    <!-- Favicon -->
    <link href="ico.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        .role-standard {
            color: blue;
            font-weight: bold;
        }

        .role-premium {
            color: green;
            font-weight: bold;
        }

        .role-vip {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Content Start -->
        <div class="content">
		<!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="index.html" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-cat"></i> Ailurophile</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="/../cat.png" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">Admin</h6>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="users.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Users</a>
                    <a href="invoices" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Invoices</a>
					<a href="stats.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Stats</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-user-edit"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="cat.png" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex">Admin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <!-- Dropdown items -->
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Main Content Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <!-- User Information -->
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">User Information</h6>
<form id="user-info-form">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" readonly>
    </div>
    <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-control" id="role" name="role">
            <option value="1">1 - Free</option>
            <option value="2">2 - Standard</option>
            <option value="3">3 - Premium</option>
            <option value="4">4 - VIP</option>
        </select>
    </div>
    <!-- Thêm nút Submit -->
    <button type="button" class="btn btn-primary" id="submit-role-change">Update Role</button>
</form>


                        </div>
                    </div>

                    <!-- Change Password -->
                   <div class="col-sm-12 col-xl-6">
    <div class="bg-secondary rounded h-100 p-4">
        <h6 class="mb-4">Change Password</h6>
        <form id="change-password-form">
            <div class="mb-3">
                <label for="password1" class="form-label">New password</label>
                <input type="password" class="form-control" id="password1" name="password1" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
        <div id="message" class="mt-3"></div>
    </div>
</div>

                    <!-- Invoices Section -->
                    <div class="col-sm-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Successful Invoices</h6>
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-white">
                                        <th scope="col">ID</th>
                                        <th scope="col">Package Name</th>
                                        <th scope="col">Created At</th>
                                        <th scope="col">Expire At</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceTableBody">
                                    <!-- Nội dung hóa đơn sẽ được cập nhật bởi JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bots Chart -->
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Bots Activity (Last 30 Days)</h6>
                            <canvas id="botChart"></canvas> <!-- Biểu đồ sẽ được cập nhật bởi JavaScript -->
                        </div>
                    </div>

                    <!-- Total Bots -->
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Total Bots</h6>
                            <h2 id="total-bots">0</h2> <!-- Tổng số bots sẽ được cập nhật bởi JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content End -->

    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- JavaScript to Fetch User Info and Update Chart -->
	<script>
    document.getElementById('submit-role-change').addEventListener('click', function(event) {
        // Ngăn chặn hành động mặc định của nút submit
        event.preventDefault();

        // Lấy giá trị từ các trường trong form
        const userId = <?php echo json_encode($user_id); ?>; // user_id được truyền từ PHP
        const role = document.getElementById('role').value;  // Lấy giá trị của role mới

        // Gửi yêu cầu AJAX đến API
        fetch('/admin/apiadmin/edit_user_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&role=${role}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User role updated successfully');
            } else {
                console.error('Error:', data.error);
                alert('Failed to update user role');
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
<script>
    document.getElementById('change-password-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Ngăn chặn hành động mặc định của form (nạp lại trang)

        // Lấy giá trị mật khẩu mới
        const newPassword = document.getElementById('password1').value;
        const userId = <?php echo json_encode($user_id); ?>; // Lấy user_id từ PHP

        // Gửi yêu cầu AJAX đến API
        fetch('/admin/apiadmin/edit_user_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&new_password=${encodeURIComponent(newPassword)}`
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('message');
            if (data.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">Password updated successfully</div>';
            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
            }
        })
        .catch(error => {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            console.error('Error:', error);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userId = <?php echo json_encode($user_id); ?>; // Lấy user_id từ PHP

        // Hàm để lấy thông tin người dùng từ API
        function fetchUserInfo() {
            fetch('/admin/apiadmin/edit_user_api.php?user_id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }

                    // Hiển thị thông tin người dùng
                    document.getElementById('username').value = data.user_info.username;
                    document.getElementById('role').value = getRoleName(data.user_info.role);
                    // Kiểm tra xem có dữ liệu invoices không
                    if (data.invoices && data.invoices.length > 0) {
                        // Hiển thị hóa đơn thành công
                        const invoiceTableBody = document.getElementById('invoiceTableBody');
                        invoiceTableBody.innerHTML = ''; // Xóa nội dung cũ

                        // Lặp qua danh sách invoices
                        data.invoices.forEach(function(invoice) {
                            const row = document.createElement('tr');
                            row.innerHTML = 
                                '<td>' + invoice.id + '</td>' +
                                '<td>' + invoice.package_name + '</td>' +
                                '<td>' + invoice.created_at + '</td>' +
                                '<td>' + invoice.expire_at + '</td>' +
                                '<td>' + invoice.status + '</td>';
                            invoiceTableBody.appendChild(row);
                        });
                    } else {
                        console.log("No invoices found.");
                    }

                    // Cập nhật biểu đồ bot
                    updateBotCharts(data.bots_data, data.total_bots);
                })
                .catch(error => console.error('Error:', error));
        }

        // Hàm để chuyển đổi role từ số sang tên hiển thị
        function getRoleName(role) {
            switch (role) {
                case "1":
                    return 'Free';
                case "2":
                    return 'Standard';
                case "3":
                    return 'Premium';
                case "4":
                    return 'VIP';
                default:
                    return 'Unknown';
            }
        }

        // Hàm để cập nhật biểu đồ bot
        function updateBotCharts(botsData, totalBots) {
            document.getElementById('total-bots').innerText = totalBots;

            const botCounts = botsData.map(function(item) { return item.bot_count; });
            const botDates = botsData.map(function(item) { return item.date; });

            const ctx = document.getElementById('botChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: botDates,
                    datasets: [{
                        label: 'Bots',
                        data: botCounts,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Gọi hàm fetchUserInfo khi trang được tải
        fetchUserInfo();
    });
</script>

