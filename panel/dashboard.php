<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the user role
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toggle Navbar with Animation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../panel/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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

    <!-- Main Content Area -->
    <div class="options-container">
        <div class="heado"><i class="bi bi-house-door"></i> Dashboard</div>
        
        <?php if ($role == 'admin'): ?>
            <div class="option"><i class="bi bi-house-door"></i> <a href="dashboard.php">Dashboard</a></div>
            <div class="option"><i class="bi bi-person-plus"></i> <a href="create_seller.php">Add Seller</a></div>
            <div class="option"><i class="bi bi-file-earmark-plus"></i> <a href="create_web1.php">Create Web</a></div>
            <div class="option"><i class="bi bi-star"></i> <a href="create_premium_web.php">Create Premium Web</a></div>
            <div class="option"><i class="bi bi-palette"></i> <a href="upload_theme.php">Add Theme</a></div>
            <div class="option"><i class="bi bi-palette-fill"></i> <a href="upload_premium_theme.php">Add Premium Theme</a></div>
            <div class="option"><i class="bi bi-trash"></i> <a href="delete_theme.php">Delete Theme</a></div>
            <div class="option"><i class="bi bi-people"></i> <a href="edt_seller.php">Manage Sellers</a></div>
            <div class="option"><i class="bi bi-envelope"></i> <a href="send_message.php">Send Message</a></div>
            <div class="option"><i class="bi bi-files"></i> <a href="manage_webs.php">Manage All Webs</a></div>
            <div class="option"><i class="bi bi-star-fill"></i> <a href="delete_premium_theme.php">Manage Premium Themes</a></div>
            <div class="option"><i class="bi bi-key"></i> <a href="change_pass.php">Change Password</a></div>
            <div class="option"><i class="bi bi-box-arrow-right"></i> <a href="logout.php">Logout</a></div>
        <?php else: ?>
            <div class="option"><i class="bi bi-file-earmark-plus"></i> <a href="create_webs.php">Create Web</a></div>
            <div class="option"><i class="bi bi-files"></i> <a href="manage_user_webs.php">Manage All Webs</a></div>
            <div class="option"><i class="bi bi-key"></i> <a href="change_pass.php">Change Password</a></div>
            <div class="option"><i class="bi bi-cart-plus"></i> <a href="buy_limit.php">Add Buy Limit</a></div>
            <div class="option"><i class="bi bi-box-arrow-right"></i> <a href="logout.php">Logout</a></div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
