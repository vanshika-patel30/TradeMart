<?php
session_start();
require "db.php";

$login_success = null;
$login_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $redirectRole = "";

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        $login_success = true;
        $login_message = "Login Successful! Redirecting...";

        if ($user['role'] === 'Admin') {
            $redirectRole = 'admin';
        } else {
            $redirectRole = 'user';
        }
    } else {
        $login_success = false;
        $login_message = "Login Unsuccessful! Try Again.";
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradeMart Login</title>
    <link rel="stylesheet" href="login.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="toast" class="login-toast"></div>

    <header>
        <img src="logos/banner_logo.png" alt="TradeMart Logo">
    </header>
    
    <div class="container">
        <div class="welcome-section">
            <h1>WELCOME BACK!</h1>
            <p>Let's pick up where you left off.</p>
            <br>
            <h2>Trade. Shop. Slay.</h2>
            <p>Log in to your TradeMart account.</p>
            <br>
            <i class="bi bi-shop"></i>
        </div>
        <div class="form-section">
            <form id="loginForm" method="POST" action="">
                <div class="form-info">
                    <label for="email" class="label">EMAIL:</label>
                    <div class="input-icon">
                        <span class="icon"><i class="bi bi-envelope-at"></i></span>
                        <input type= "email" name="email" id="email" required>
                    </div>
                    <span class="error" id="emailError">Enter a valid email!</span>
                </div>
                <br>
                <br>
                <br>
                <br>
                <div class="form-info">
                    <label for="password" class="label">PASSWORD:</label>
                    <div class="input-icon" style="position: relative;">
                        <span class="icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="password" required>
                        <span class="toggle-Password"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                    </div>
                    <span class="error" id="passwordError">Incorrect Password!</span>
                </div>
                <br>
                <br>
                <button type="submit">LOGIN</button>
            </form>
        </div>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#ffcccc' : 'lemonchiffon';
            toast.style.color = isError ? '#8a1f1f' : 'black';
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        }


        $(document).ready(function () {
            $('#loginForm').on('submit', function (e) {
                let isValid = true;

                const email = $('#email').val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    $('#emailError').show();
                    isValid = false;
                } else {
                    $('#emailError').hide();
                }

                const password = $('#password').val();
                if (password.length < 6) {
                    $('#passwordError').show();
                    isValid = false;
                } else {
                    $('#passwordError').hide();
                }

                if (!isValid) e.preventDefault();
            });

            $('#togglePassword').on('click', function () {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('bi-eye bi-eye-slash');
            });

            <?php if (isset($login_success)): ?>
                showToast("<?= $login_message ?>", <?= $login_success ? 'false' : 'true' ?>);
                
                <?php if ($login_success && !empty($redirectRole)): ?>
                    setTimeout(() => {
                        <?php if ($redirectRole === 'admin'): ?>
                            window.location.href = 'admin_dashboard.php';
                        <?php else: ?>
                            window.location.href = 'role_select.php';
                        <?php endif; ?>
                    }, 2000);
                <?php endif; ?>
            <?php endif; ?>
        });
   </script>  
</body>
</html>