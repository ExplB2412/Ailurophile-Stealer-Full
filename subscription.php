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
    <title>Ailurophile Stealer | Subscription</title><!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="Ailurophile Stealer | Subscription">
    <meta name="description" content="Ailurophile Stealer | Subscription">
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
    <div class="row g-4">
        <div class="col-sm-12 col-md-6 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-4">Standard Package</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>Only one stub may be generated per day.</li>
                        <li>Limit of 50 bots. If there are more than 50 bots, the newest bots will be stored and the oldest bots will be deleted.</li>
                        <li>Stub options:
                            <ul>
                                <li>Stub version 1.x</li>
                                <li>Telegram notify</li>
                            </ul>
                        </li>
                        <li>Pricing
                            <ul>
                                <li>$80/month</li>
                                <li>$400/6 months</li>
                                <li>$1280/Life time</li>
                            </ul>
                        </li>
                        <br>
                        <center>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger" data-package="1">1 Month</button>
                                <button type="button" class="btn btn-warning" data-package="2">6 months</button>
                                <button type="button" class="btn btn-success" data-package="3">Life time</button>
                            </div>
                        </center>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-4">Premium Package</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>05 stubs may be generated per day.</li>
                        <li>Limit of 200 bots. If there are more than 200 bots, the newest bots will be stored and the oldest bots will be deleted.</li>
                        <li>Stub options:
                            <ul>
                                <li>Stub version 1.x</li>
                                <li>Telegram notify</li>
                                <li>Disable Windows Defender</li>
                                <li>Delivery other stealer or RAT</li>
                            </ul>
                        </li>
                        <li>Pricing
                            <ul>
                                <li>$140/month</li>
                                <li>$700/6 months</li>
                                <li>$2240/Life time</li>
                            </ul>
                        </li>
                        <br>
                        <center>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger" data-package="4">1 Month</button>
                                <button type="button" class="btn btn-warning" data-package="5">6 months</button>
                                <button type="button" class="btn btn-success" data-package="6">Life time</button>
                            </div>
                        </center>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-4">VIP Package</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>20 stubs may be generated per day.</li>
                        <li>Unlimited bots.</li>
                        <li>Stub options:
                            <ul>
                                <li>Stub version 1.x</li>
                                <li>Stub version 2.x</li>
                                <li>Assembly information</li>
                                <li>Telegram notify</li>
                                <li>Mix stub</li>
                                <li>Pump file</li>
                                <li>Startup</li>
                                <li>Disable Windows Defender</li>
                                <li>Delivery other stealer or RAT</li>
                            </ul>
                        </li>
                        <li>Pricing
                            <ul>
                                <li>$250/month</li>
                                <li>$1250/6 months</li>
                                <li>$4000/Life time</li>
                            </ul>
                        </li>
                        <br>
                        <center>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger" data-package="7">1 Month</button>
                                <button type="button" class="btn btn-warning" data-package="8">6 months</button>
                                <button type="button" class="btn btn-success" data-package="9">Life time</button>
                            </div>
                        </center>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-4 text-center">Stub version 1.x</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>Stub options:
                            <ul>
                                <li>Bypass 99% AV</li>
                                <li>Disable Windows Defender</li>
                                <li>Delivery other RAT or Stealer</li>
                                <li>Grabber file</li>
                                <li>Stolen password</li>
                                <li>Stolen cookie</li>
                                <li>Stolen history</li>
                                <li>Stolen credit card</li>
                                <li>Telegram notify</li>
                                <li>Stolen all Chromium-based browser data</li>
                                <li>Obfuscated and Encrypted</li>
                            </ul>
                        </li>
                        <br>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-4 text-center">Stub version 2.x</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>Stub options:
                            <ul>
                                <li>Bypass 100% AV</li>
                                <li>Disable Windows Defender</li>
                                <li>Delivery other RAT or Stealer</li>
                                <li>Grabber file</li>
                                <li>Stolen password</li>
                                <li>Stolen cookie</li>
                                <li>Stolen history</li>
                                <li>Stolen credit card</li>
                                <li>Telegram notify</li>
                                <li>Stolen all Chromium-based browser data</li>
                                <li>Stolen all Gecko-based browser data</li>
                                <li>Run silently</li>
                                <li>Startup</li>
                                <li>Obfuscated and Encrypted</li>
                            </ul>
                        </li>
                        <br>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-secondary">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="paymentMessage"></p>
                    <p><strong>Address:</strong> <span id="paymentAddress" class="text-bold"></span></p>
                    <p><strong>Amount:</strong> <span id="paymentAmount" class="text-bold"></span></p>
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
  	     <script>
        $(document).ready(function() {
            $('.btn-group button').on('click', function() {
                var package = $(this).data('package'); // Lấy tên gói từ button data-package
                
                // Hiển thị spinner
                $('#spinner').show();
                
                $.ajax({
                    url: '/api/subscription',
                    method: 'GET',
                    data: { package: package },
                    dataType: 'json',
                    success: function(response) {
                        // Ẩn spinner
                        $('#spinner').hide();
                        
                        if (response.status === 'success') {
                            $('#paymentMessage').text(response.message);
                            $('#paymentAddress').text(response.address);
                            $('#paymentAmount').text(response.amount);
                            $('#paymentModal').modal('show');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Ẩn spinner
                        $('#spinner').hide();
                        alert('AJAX Error: ' + error);
                    }
                });
            });
        });
    </script>

</html>