<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION["user_id"];
$toast_message = '';
$toast_success = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user_id"])) {
    $delete_id = $_POST["delete_user_id"];

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role IN ('Buyer', 'Seller')");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $toast_message = "User deleted successfully.";
        $toast_success = true;
    } else {
        $toast_message = "Error deleting user.";
        $toast_success = false;
    }
}

$stmt = "SELECT user_id, name, email, phone_number, role FROM users WHERE role IN ('Buyer', 'Seller')";
$result = $conn->query($stmt);

$users = array();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="view_users.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200..700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>Registered Users</h2>
            <a href="admin_dashboard.php" class="back-btn"><i class="bi bi-arrow-bar-left"></i>Back to Dashboard</a>
        </header>

        <div id="toast" class="admin-users-toast"></div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Role</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-label="ID"><?= $user['user_id'] ?></td>
                        <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                        <td data-label="Phone"><?= htmlspecialchars($user['phone_number']) ?></td>
                        <td data-label="Role"><?= ucfirst($user['role']) ?></td>
                        <td>
                           <button class="delete-btn" onclick="openModal(<?= $user['user_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="admin-modal" class="modal">
            <div class="modal-content">
                <p>Are you sure you want to delete this user's profile?</p>
                <form method="POST" id="delete-form">
                    <input type="hidden" name="delete_user_id" id="delete_user_id">
                    <button type="submit" class="confirm-btn">Yes</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">No</button>
                </form>
            </div>
        </div>
    </div>

   <script>
        const deleteForm = document.getElementById("delete-form");
        const deleteUserIdInput = document.getElementById("delete_user_id");
        const modal = document.getElementById("admin-modal");

        function openModal(userId) {
            deleteUserIdInput.value = userId;
            modal.style.display = "flex";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.openModal = openModal;
        window.closeModal = closeModal;

        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        <?php if (!empty($toast_message)): ?>
            const toast = document.getElementById("toast");
            toast.textContent = "<?= $toast_message ?>";
            toast.style.backgroundColor = <?= $toast_success ? "'lemonchiffon'" : "'#ffcccc'" ?>;
            toast.style.color = <?= $toast_success ? "'black'" : "'#8a1f1f'" ?>;
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>