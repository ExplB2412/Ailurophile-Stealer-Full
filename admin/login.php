<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: users.php"); // Chuyển hướng tới dashboard nếu đã đăng nhập
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script>
        function login() {
            const password = document.getElementById('password').value;

            // Sử dụng Fetch API để gửi yêu cầu POST đến API login
            fetch('/admin/apiadmin/login_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'dashboard.php'; // Chuyển hướng đến dashboard nếu thành công
                } else {
                    document.getElementById('error').innerText = data.message; // Hiển thị lỗi nếu đăng nhập thất bại
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <h2>Admin Login</h2>
    <p style="color: red;" id="error"></p> <!-- Nơi hiển thị lỗi nếu có -->
    <form onsubmit="event.preventDefault(); login();">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
