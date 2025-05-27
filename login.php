<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login - Ailurophile Stealer</title>

    <!-- Favicon -->
    <link href="ico.ico" rel="icon">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Custom CSS -->
    <style>
        .login-box {
            width: 360px;
            margin: 7% auto;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Ailurophile</b> Stealer</a>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>
                <form id="login-form">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Captcha" name="captcha" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-shield-alt"></span>
                            </div>
                        </div>
                    </div>
                    <img src="captcha/securimage_show.php" id="captcha"/>
                    <img src="images/refresh.gif" class="captcha-refresh" onclick="
                        document.getElementById('captcha').src='captcha/securimage_show.php?sid='+Math.random();" />
                    <br><br>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                <div id="message" class="mt-3"></div>
                <p class="mb-0">
                    <a href="/register" class="text-center">Register a new membership</a>
                </p>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <!-- AJAX for form submission -->
    <script>
        $(document).ready(function(){
            $("#login-form").on("submit", function(event){
                event.preventDefault();

                $.ajax({
                    url: "/api/login",
                    method: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response){
                        if(response.status == "success"){
                            $("#message").html('<div class="alert alert-success">' + response.message + '<br>You will be redirected to the dashboard in 5 seconds.</div>');
                            setTimeout(function(){
                                window.location.href = "/dashboard";
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
