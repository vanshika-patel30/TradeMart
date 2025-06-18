<?php
session_start();
require "db.php";

date_default_timezone_set("Africa/Johannesburg");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $phone_number = $_POST["phone_number"];
    $role = "Admin";
    $created_at = date('Y-m-d H:i:s');

    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $admin_success = false;
        $admin_message = "Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone_number, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone_number, $role, $created_at);

        if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_name"] = $name;
                $admin_success = true;
                $admin_message = "Admin Added Successfully!";
        } else  {
            $admin_success = false;
            $admin_message = "Something went wrong! Try Again.";
        }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Another Admin</title>
    <link rel="stylesheet" href="add_admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div id="toast" class="admin-add-toast"></div>

     <header>
        <img src="logos/banner_logo.png" alt="TradeMart Logo">
        <a href="admin_dashboard.php" class="back-dashboard"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
    </header>

    <div class="container">
        <div class="welcome-section">
            <h1>HI ADMIN!</h1>
            <p>Expand your admin crew.</p>
            <br>
            <h2>Give access. Share Responsibility</h2>
            <p>Add admin with their details.</p>
            <br>
            <i class="bi bi-person-add"></i>
        </div>
        <div class="form-section">
            <form id="add-admin-Form" method="POST" action="">
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
                <button type="submit">ADD ADMIN</button>
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

            $('#add-admin-Form').on('submit', function(e) {
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
                        window.location.href = 'admin_dashboard.php';
                    }, 2000);
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>

    <?php if (!empty($admin_success)) : ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const msg = document.createElement('div');
                msg.textContent = 'Added Admin Successfully!';
                msg.style.position = 'fixed';
                msg.style.top = '20px';
                msg.style.left = '50%';
                msg.style.transform = 'translateX(-50%)';
                msg.style.backgroundColor = 'lemonchiffon';
                msg.style.color = 'black';
                msg.style.padding = '15px 25px';
                msg.style.borderRadius = '8px';
                msg.style.fontWeight = 'bold';
                msg.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
                msg.style.zIndex = '9999';
                document.body.appendChild(msg);

                setTimeout(function () {
                    window.location.href = 'admin_dashboard.php';
                }, 2000);
            });
        </script>
    <?php endif; ?>
</body>
</html>