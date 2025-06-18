<?php
session_start();

require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$edit_success = null;
$edit_message = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['role'] === 'Buyer') {
    $redirect_to = "buyer_dashboard.php";
} elseif ($user['role'] === 'Seller') {
    $redirect_to = "seller_dashboard.php";
} elseif ($user['role'] === 'Admin') {
    $redirect_to = "admin_dashboard.php";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name=?, password=?, email=?, phone_number=? WHERE user_id=?");
        $update->bind_param("ssssi", $name, $password, $email, $phone_number, $user_id);
    } else {
        $update = $conn->prepare("UPDATE users SET name=?, email=?, phone_number=? WHERE user_id=?");
        $update->bind_param("sssi", $name, $email, $phone_number, $user_id);
    }
    
    if ($update->execute()) {
        $edit_success = true;
        $edit_message = "Profile updated successfully!";
    } else {
        $edit_success = false;
        $edit_message = "Failed to update profile!";
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit_profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Edit Profile</title>
</head>
<body>
    <div id="toast" class="edit-profile-toast"></div>

    <div class="profile-modal" id="profile-modal">
        <span class="close-modal" id="closeModal">&times;</span>
        <h1>EDIT PROFILE</h1>
        <form id="edit-form" method="POST" action="">
            <div class="form-info">
                <label for="name" class="label">Name:</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                <span class="error" id="nameError">Please enter a valid name with letters!</span>
            </div>

            <div class="form-info">
                <label for="password" class="label">Password:</label>
                <div class="password-icon">
                    <input type="password" name="password" id="password" placeholder="Enter new password (optional)">
                    <span class="toggle-password"><i class="bi bi-eye-slash" id="togglePassword"></i></span>
                </div>
                <span class="error" id="passwordError">Password must at least be 6 characters with letters, numbers and underscore!</span>
            </div>

            <div class="form-info">
                <label for="email" class="label">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <span class="error" id="emailError">Please enter a valid email!</span>
            </div>

            <div class="form-info">
                <label for="text" class="label">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" required>
                <span class="error" id="phoneError">Please enter a valid phone number!</span>
            </div>

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.backgroundColor = isError ? '#ffcccc' : 'a3d8af';
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

            $('#edit-form').on('submit', function(e) {
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
                if (password.length > 0 && !passwordRegex.test(password)) {
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

            <?php if (!empty($edit_message)): ?>
                    showToast("<?= $edit_message ?>", <?= $edit_success ? 'false' : 'true' ?>);
            <?php endif; ?>
        });

        document.addEventListener("DOMContentLoaded", function () {
            const closeBtn = document.getElementById("closeModal");
            if (closeBtn) {
                closeBtn.addEventListener("click", function () {
                    window.location.href = "<?php echo $redirect_to; ?>";
                });
            }
        });
    </script>
</body>
</html>