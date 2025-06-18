<?php
session_start();
require "db.php";

date_default_timezone_set("Africa/Johannesburg");

$signup_success = null;
$signup_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone_number = $_POST["phone_number"];
    $role = "Buyer";
    $created_at = date('Y-m-d H:i:s');

    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $signup_success = false;
        $signup_message = "Email already exists! Try logging in.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone_number, $role, $created_at);

        if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_name"] = $name;
                $signup_success = true;
                $signup_message = "Sign Up Successful! Redirecting...";
            } else {
                $signup_success = false;
                $signup_message = "Sign Up Unsuccessful! Try Again.";
            }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradeMart Sign Up</title>
    <link rel="stylesheet" href="signup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div id="toast" class="signup-toast"></div>

     <header>
        <img src="logos/banner_logo.png" alt="TradeMart Logo">
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>HELLO & WELCOME!</h1>
            <p>We're glad you're here!</p>
            <br>
            <h2>Sign Up. Style Up. Sell Smart.</h2>
            <p>Sign up to join local sellers and buyers.</p>
            <br>
            <i class="bi bi-bag-check-fill"></i>
        </div>
        <div class="form-section">
            <form id="signupForm" method="POST" action="">
                <div class="form-info">
                    <label for="name" class="label">FULL NAME:</label>
                    <div class="input-icon">
                        <span class="icon"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <span class="error" id="nameError">Please enter a valid name with letters!</span>
                </div>
                <div class="form-info">
                    <label for="password" class="label">PASSWORD:</label>
                    <div class="input-icon">
                        <span class="icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="password" required>
                        <span class="toggle-password"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                    </div>
                    <span class="error" id="passwordError">Password must at least be 6 characters with letters, numbers and underscore!</span>
                </div>
                <div class="form-info">
                    <label for="email" class="label">EMAIL:</label>
                    <div class="input-icon">
                        <span class="icon"><i class="bi bi-envelope-at"></i></span>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <span class="error" id="emailError">Please enter a valid email!</span>
                </div>
                 <div class="form-info">
                    <label for="phone_number" class="label">PHONE NUMBER:</label>
                    <div class="input-icon">
                        <span class="icon"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" name="phone_number" id="phone_number" required>
                    </div>
                    <span class="error" id="phoneError">Please enter a valid phone number!</span>
                </div>
                <button type="submit">SIGN UP</button>
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

        $(document).ready(function() {
            $('#togglePassword').on('click', function () {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('bi-eye bi-eye-slash');
            });

            $('#signupForm').on('submit', function(e) {
                let isValid = true;

                const name = $('#name').val().trim();
                const nameRegex = /^[A-Za-z\s'-]+$/;
                if (!nameRegex.test(name)) {
                    $('#nameError').show(); 
                    isValid = false;
                } else {
                    $('#nameError').hide();
                }

                const password = $('#password').val();
                const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d_]{6,}$/;
                if (!passwordRegex.test(password)) {
                    $('#passwordError').show();
                    isValid = false;
                } else {
                    $('#passwordError').hide();
                }

                const email = $('#email').val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    $('#emailError').show();
                    isValid = false;
                } else {
                    $('#emailError').hide();
                }

                const phone_number = $('#phone_number').val();
                const phoneRegex = /^(\+27|0)[6-8][0-9]{8}$/;
                if (!phoneRegex.test(phone_number)) {
                    $('#phoneError').show();
                    isValid = false;
                } else {
                    $('#phoneError').hide();
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            <?php if (isset($signup_success)): ?>
                showToast("<?= $signup_message ?>", <?= $signup_success ? 'false' : 'true' ?>);
                
                <?php if ($signup_success): ?>
                    setTimeout(() => {
                        window.location.href = 'role_select.php';
                    }, 2000);
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>