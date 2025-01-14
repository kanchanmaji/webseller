<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION["username"])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION["username"];
$query = 'SELECT `role` FROM users WHERE `username` = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
$role = $row[0];

if ($role !== "admin") {
    header('Location: dashboard.php');
    exit();
}

$error = ''; // Variable to store error message
$success = ''; // Variable to store success message

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sellerUsername = $_POST['sellerUsername'];
    $sellerPassword = password_hash($_POST['sellerPassword'], PASSWORD_DEFAULT); // Hash the password
    $sellerWebLimit = $_POST['sellerWebLimit'];
    $sellerRole = $_POST['sellerRole'];

    // Check if the username already exists
    $query = 'SELECT COUNT(*) FROM users WHERE username = ?';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $sellerUsername);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        $error = "Username already exists.";
    } else {
        // Insert the new seller into the database
        $query = 'INSERT INTO users (username, password, web_limit, role) VALUES (?, ?, ?, ?)';
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssis', $sellerUsername, $sellerPassword, $sellerWebLimit, $sellerRole);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Seller created successfully!";
        } else {
            $error = "Failed to create seller: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Seller</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../panel/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .copy-btn {
            cursor: pointer;
            background: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 5px;
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

    <!-- Main Content Area -->
    <div class="container mt-5">
        <div class="options-container">
            <div class="heado"><i class="bi bi-person-plus"></i> Create Seller</div>
            
            <!-- Create Seller Form -->
            <form action="create_seller.php" method="post" class="form-group">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="sellerUsername" class="form-label">
                        <i class="bi bi-person"></i> Seller Username
                    </label>
                    <input type="text" class="form-control" id="sellerUsername" name="sellerUsername" placeholder="Enter seller username" required>
                </div>
                <div class="mb-3">
                    <label for="sellerPassword" class="form-label">
                        <i class="bi bi-key"></i> Seller Password
                    </label>
                    <input type="password" class="form-control" id="sellerPassword" name="sellerPassword" placeholder="Enter seller password" required>
                </div>
                <div class="mb-3">
                    <label for="sellerWebLimit" class="form-label">
                        <i class="bi bi-cloud-upload"></i> Seller Web Limit
                    </label>
                    <input type="number" class="form-control" id="sellerWebLimit" name="sellerWebLimit" placeholder="Enter web limit" required>
                </div>
                <div class="mb-3">
                    <label for="sellerRole" class="form-label">
                        <i class="bi bi-people"></i> Role
                    </label>
                    <select class="form-control" id="sellerRole" name="sellerRole" required>
                        <option value="">--Select a role--</option>
                        <option value="seller">Seller</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-person-plus"></i> Create Seller</button>
            </form>
            
            <?php if (!empty($success)): ?>
                <div class="mt-4">
                    <h5>New Seller Details:</h5>
                    <div class="mb-2">
                        <strong>Username:</strong> <?php echo htmlspecialchars($sellerUsername); ?>
                        <button class="copy-btn" onclick="copyToClipboard('username')">Copy</button>
                    </div>
                    <div>
                        <strong>Password:</strong> <span id="password-text"><?php echo htmlspecialchars($_POST['sellerPassword']); ?></span>
                        <button class="copy-btn" onclick="copyPassword()">Copy</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('Username copied to clipboard');
            }, function(err) {
                alert('Failed to copy text: ' + err);
            });
        }

        function copyPassword() {
            const passwordText = document.getElementById('password-text').innerText;
            navigator.clipboard.writeText(passwordText).then(function() {
                alert('Password copied to clipboard');
            }, function(err) {
                alert('Failed to copy text: ' + err);
            });
        }
    </script>
</body>
</html>