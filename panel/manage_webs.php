<?php
session_start();
include 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["username"])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION["username"];
$query = 'SELECT role FROM users WHERE username = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
$role = $row[0];

if ($role !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Fetch webs for dropdown from `webs` and `websites` tables
$query = 'SELECT web_name FROM webs UNION SELECT web_name FROM websites';
$result = mysqli_query($conn, $query);
$webs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $webs[] = $row['web_name'];
}

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Handle web deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $webToDelete = $_POST['deleteFile'];

    if (!empty($webToDelete)) {
        // Prepare and execute the SQL statements to delete the web from the `webs` table
        $query1 = 'DELETE FROM webs WHERE web_name = ?';
        $stmt = mysqli_prepare($conn, $query1);
        mysqli_stmt_bind_param($stmt, 's', $webToDelete);
        mysqli_stmt_execute($stmt);

        // Delete from `websites` table if necessary
        $query2 = 'DELETE FROM websites WHERE web_name = ?';
        $stmt = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt, 's', $webToDelete);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // Remove the directory and its contents from the server
            $dirPath = '../websites/' . $webToDelete;

            if (is_dir($dirPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($dirPath);
                $successMessage = "Web deleted successfully!";
            } else {
                $errorMessage = "Directory not found on the server.";
            }
        } else {
            $errorMessage = "Failed to delete web from the database.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "No web selected.";
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanchan Web Seller</title>
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
            <div class="heado"><i class="bi bi-trash"></i> Manage Web</div>

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

            <!-- Delete Web Form -->
            <form action="manage_webs.php" method="post" class="form-group">
                <div class="mb-3">
                    <label for="webToDelete" class="form-label">Select a Web to delete:</label>
                    <select name="deleteFile" id="webToDelete" class="form-control" required>
                        <option value="">--Select a Web--</option>
                        <?php foreach ($webs as $web): ?>
                            <option value="<?php echo htmlspecialchars($web); ?>">
                                <?php echo htmlspecialchars($web); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Web</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>