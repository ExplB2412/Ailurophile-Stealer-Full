<?php
session_start();
include __DIR__ . "/apiadmin/config.php";

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Ailurophile Stealer Dashboard</title>
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

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
	<style>
    /* Màu sắc cho các role */
    .role-standard {
        color: blue; /* Màu xanh cho role Standard */
        font-weight: bold;
    }

    .role-premium {
        color: green; /* Màu xanh lá cho role Premium */
        font-weight: bold;
    }

    .role-vip {
        color: red; /* Màu đỏ cho role VIP */
        font-weight: bold;
    }
</style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

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

        <!-- Content Start -->
        <div class="content">
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
                            <span class="d-none d-lg-inline-flex"><?php echo "admin";?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Blank Start -->
      <div class="container-fluid pt-4 px-4">
    <div class="bg-secondary text-center rounded p-4">
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-white">
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Total log</th>
                        <th scope="col">Edit user</th>
                    </tr>
                </thead>
                <tbody id="botTableBody">
                    <!-- Nội dung bảng sẽ được cập nhật bởi AJAX -->
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <button class="btn btn-primary mx-1" onclick="loadBots(1)">1</button>
            <button class="btn btn-primary mx-1" onclick="loadBots(2)">2</button>
            <button class="btn btn-primary mx-1" onclick="loadBots(3)">3</button>
            <button class="btn btn-primary mx-1" onclick="loadBots(4)">4</button>
            <button class="btn btn-primary mx-1" onclick="loadBots(5)">5</button>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Bulk Download</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Nội dung modal sẽ được cập nhật từ JavaScript -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">Ailurophile Stealer</a>, All Right Reserved. Telegram support: @Ailurophilevn
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

 
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm gọi API và cập nhật bảng người dùng
        function fetchUsers() {
            fetch('/admin/apiadmin/user_api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }

                    // Lấy tbody để chèn nội dung
                    const botTableBody = document.getElementById('botTableBody');
                    botTableBody.innerHTML = ''; // Xóa sạch nội dung trước đó

                    // Lặp qua danh sách người dùng và chèn vào bảng
                    data.forEach(function(user) {
                        const row = document.createElement('tr');

                        row.innerHTML = 
                            '<td>' + user.id + '</td>' +
                            '<td>' + user.username + '</td>' +
                            '<td class="' + getRoleClass(user.role) + '">' + getRoleName(user.role) + '</td>' +
                            '<td>' + user.created_at + '</td>' +
                            '<td>' + user.total_bots + '</td>' +
                            '<td>' +
                                '<a href="edit_user.php?id=' + user.id + '" class="btn btn-primary btn-sm">Edit</a>' +
                            '</td>';

                        botTableBody.appendChild(row);
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        // Hàm để chuyển đổi role từ số sang tên hiển thị và thêm màu sắc cho role
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

        // Hàm để trả về lớp CSS tương ứng với role
        function getRoleClass(role) {
            switch (role) {
                case "2":
                    return 'role-standard'; // Màu sắc cho role Standard
                case "3":
                    return 'role-premium'; // Màu sắc cho role Premium
                case "4":
                    return 'role-vip'; // Màu sắc cho role VIP
                default:
                    return ''; // Không có màu cho role Free hoặc Unknown
            }
        }

        // Gọi hàm fetchUsers khi trang được tải
        fetchUsers();
    });
</script>
