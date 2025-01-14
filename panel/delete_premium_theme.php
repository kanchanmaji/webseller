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

// Fetch themes for dropdown from `premium_themes` table
$query = 'SELECT theme_name FROM premium_themes';
$result = mysqli_query($conn, $query);
$themes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $themes[] = $row['theme_name'];
}

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Handle file deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $themeToDelete = $_POST['deleteFile'];

    if (!empty($themeToDelete)) {
        // Prepare and execute the SQL statement to delete the theme from the database
        $query = 'DELETE FROM premium_themes WHERE theme_name = ?';
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $themeToDelete);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // Remove the file from the server
            $filePath = '../scripts/' . $themeToDelete . '.*'; // use wildcard for any extension
            $files = glob($filePath); // Get all files matching the theme name
            if (count($files) > 0) {
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                $successMessage = "Theme deleted successfully!";
            } else {
                $errorMessage = "Theme file not found on the server.";
            }
        } else {
            $errorMessage = "Failed to delete theme from the database.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = "No theme selected.";
    }

    mysqli_close($conn);
}
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
            <div class="heado"><i class="bi bi-trash"></i> Delete Theme</div>
            
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

            <!-- Delete Theme Form -->
            <form action="delete_premium_theme.php" method="post" enctype="multipart/form-data" class="form-group">
                <div class="mb-3">
                    <label for="themeToDelete" class="form-label">Select a theme to delete:</label>
                    <select name="deleteFile" id="themeToDelete" class="form-control" required>
                        <option value="">--Select a theme--</option>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?php echo htmlspecialchars($theme); ?>"><?php echo htmlspecialchars($theme); ?></option>
                       
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Theme</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>