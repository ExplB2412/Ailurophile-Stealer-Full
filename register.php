<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit();
}

$refCode = ''; // Khởi tạo biến refCode

if (isset($_GET['ref'])) {
    $refCode = htmlspecialchars($_GET['ref']); // Gán giá trị mã giới thiệu từ $_GET['ref']
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Register - Ailurophile Stealer</title>

    <!-- Favicon -->
    <link href="ico.ico" rel="icon">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        .login-box {
            width: 360px;
            margin: 7% auto;
        }
    </style>
</head>

<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo">
            <a href="#"><b>Ailurophile</b> Stealer</a>
        </div>
        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Register a new membership</p>

                <form id="register-form">
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="refered" class="form-control" value="<?php echo $refCode; ?>" placeholder="Invite code">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-tag"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="captcha" class="form-control" placeholder="Captcha" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-shield-alt"></span>
                            </div>
                        </div>
                    </div>

                    <img src="captcha/securimage_show.php" id="captcha"/>
                    <img src="images/refresh.gif" class="captcha-refresh" onclick="
                        document.getElementById('captcha').src='captcha/securimage_show.php?sid='+Math.random();"/>
                    <br><br>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                    </div>
                </form>

                <div id="message" class="mt-3"></div>

                <a href="/login" class="text-center">I already have a membership</a>
            </div>
            <!-- /.form-box -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.register-box -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- AJAX for form submission -->
    <script>
        $(document).ready(function(){
            $("#register-form").on("submit", function(event){
                event.preventDefault();

                $.ajax({
                    url: "/api/register",
                    method: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response){
                        if(response.status == "success"){
                            $("#message").html('<div class="alert alert-success">' + response.message + '<br>You will be redirected to the login page in 5 seconds.</div>');
                            setTimeout(function(){
                                window.location.href = "/login";
                            }, 5000);
                        } else {
                            $("#message").html('<div class="alert alert-danger">' + response.message + '</div>');
                            document.getElementById('captcha').src='captcha/securimage_show.php?sid='+Math.random();
                        }
                    },
                    error: function(){
                        $("#message").html('<div class="alert alert-danger">There was an error processing your request. Please try again later.</div>');
                        document.getElementById('captcha').src='captcha/securimage_show.php?sid='+Math.random();
                    }
                });
            });
        });
    </script>
</body>
</html>
