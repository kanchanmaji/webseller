<?php
session_start();
include 'config.php';

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $query = 'SELECT * FROM users WHERE `username` = ?';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['username'] = null;
        session_destroy();
        header('Location:login.php');
        exit();
    }

    $s = "SELECT `role` FROM users WHERE username = '$username'";
    $r = mysqli_query($conn, $s);
    $row = mysqli_fetch_array($r);
    $role = $row[0];

    if ($role == "admin") {
        // Initialize messages
        $successMessage = '';
        $errorMessage = '';

        // Check if a new limit is being submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seller_id']) && isset($_POST['new_limit'])) {
            $seller_id = $_POST['seller_id'];
            $new_limit = $_POST['new_limit'];

            // Update the web limit in the database
            $update_query = "UPDATE users SET web_limit = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ii', $new_limit, $seller_id);
            mysqli_stmt_execute($update_stmt);

            if (mysqli_stmt_affected_rows($update_stmt) > 0) {
                $successMessage = "Limit updated successfully!";
            } else {
                $errorMessage = "Failed to update the limit.";
            }
        }

        // Check if a delete request is made
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_seller_id'])) {
            $delete_seller_id = $_POST['delete_seller_id'];

            // Delete the seller from the database
            $delete_query = "DELETE FROM users WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, 'i', $delete_seller_id);
            mysqli_stmt_execute($delete_stmt);

            if (mysqli_stmt_affected_rows($delete_stmt) > 0) {
                $successMessage = "Seller deleted successfully!";
            } else {
                $errorMessage = "Failed to delete the seller.";
            }
        }

        // Get all sellers from the database
        $sellers_query = "SELECT id, username, web_limit FROM users WHERE role = 'seller'";
        $sellers_result = mysqli_query($conn, $sellers_query);
    }
}
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Seller</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../panel/assets/css/style.css">
 <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
   <style>
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .table-container {
            margin-top: 50px;
        }
        .table {
            background-color: #ff0000;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .table th {
            background-color: #ff0000;
            color: white;
        }
        .btn-primary {
            background-color: #ff0000;
            border-color: #ff0000;
        }
        .btn-primary:hover {
            background-color: #e60000;
            border-color: #e60000;
        }
        .btn-warning {
            background-color: #ffcc00;
            border-color: #ffcc00;
        }
        .btn-warning:hover {
            background-color: #e5b800;
            border-color: #e5b800;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>
<body>
	<!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-red">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-house-door"></i> Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if ($role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_seller.php"><i class="bi bi-person-plus"></i> Add Seller</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_web1.php"><i class="bi bi-file-earmark-plus"></i> Create Web</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_premium_web.php"><i class="bi bi-star"></i> Create Premium Web</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="upload_theme.php"><i class="bi bi-palette"></i> Add Theme</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="upload_premium_theme.php"><i class="bi bi-palette-fill"></i> Add Premium Theme</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="delete_theme.php"><i class="bi bi-trash"></i> Delete Theme</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="edt_seller.php"><i class="bi bi-people"></i> Manage Sellers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="send_message.php"><i class="bi bi-envelope"></i> Send Message</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_webs.php"><i class="bi bi-files"></i> Manage All Webs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="delete_premium_theme.php"><i class="bi bi-star-fill"></i> Manage Premium Themes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="change_pass.php"><i class="bi bi-key"></i> Change Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="create_webs.php"><i class="bi bi-file-earmark-plus"></i> Create Web</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_user_webs.php"><i class="bi bi-files"></i> Manage All Webs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="change_pass.php"><i class="bi bi-key"></i> Change Password</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="buy_limit.php"><i class="bi bi-cart-plus"></i> Add Buy Limit</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container mt-4">
        <!-- Display success and error messages -->
        <?php if ($successMessage): ?>
            <div class="message success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="message error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="container table-container">
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Current Limit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($seller = mysqli_fetch_assoc($sellers_result)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($seller['username']); ?></td>
                            <td>
                                <span id="limit_<?php echo $seller['id']; ?>">
                                    <?php echo htmlspecialchars($seller['web_limit']); ?>
                                </span>
                                <form id="form_<?php echo $seller['id']; ?>" style="display:none;" method="POST">
                                    <input type="hidden" name="seller_id" value="<?php echo $seller['id']; ?>">
                                    <input type="number" name="new_limit" value="<?php echo $seller['web_limit']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="showEditForm(<?php echo $seller['id']; ?>)">Edit</button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_seller_id" value="<?php echo $seller['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function showEditForm(sellerId) {
            var limitSpan = document.getElementById('limit_' + sellerId);
            var form = document.getElementById('form_' + sellerId);

            limitSpan.style.display = 'none';
            form.style.display = 'block';
        }
    </script>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>