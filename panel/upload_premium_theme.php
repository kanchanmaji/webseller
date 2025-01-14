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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the theme name from the form input
    $themeName = $_POST['themeName'];

    // Check if a file was uploaded without errors
    if (isset($_FILES['themeFile']) && $_FILES['themeFile']['error'] == 0) {
        $themeFile = $_FILES['themeFile'];

        // Define the directory to store uploaded files (goes one directory up, then into 'scripts')
        $uploadDirectory = '../scripts/';

        // Make sure the upload directory exists
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        // Extract the original file extension
        $fileExtension = pathinfo($themeFile['name'], PATHINFO_EXTENSION);
        
        // Set the new file name as the theme name with the original file extension
        $newFileName = $themeName . '.' . $fileExtension;
        $filePath = $uploadDirectory . $newFileName;

        // First, check if the theme name already exists
        $checkQuery = 'SELECT COUNT(*) FROM premium_themes WHERE theme_name = ?';
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, 's', $themeName);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_bind_result($checkStmt, $count);
        mysqli_stmt_fetch($checkStmt);
        mysqli_stmt_close($checkStmt);

        if ($count > 0) {
            $errorMessage = "Theme name already exists.";
        } else {
            // Move the uploaded file to the designated directory with the new name
            if (move_uploaded_file($themeFile['tmp_name'], $filePath)) {
                // Save the theme name into the database
                $query = 'INSERT INTO premium_themes(theme_name) VALUES (?)';
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 's', $themeName);
                mysqli_stmt_execute($stmt);

                // Check if the query was successful
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $successMessage = "Theme uploaded and saved successfully!";
                } else {
                    $errorMessage = "Failed to save theme details in the database.";
                }

                mysqli_stmt_close($stmt);
            } else {
                $errorMessage = "Failed to upload the theme file.";
            }
        }
    } else {
        $errorMessage = "No file uploaded or there was an error uploading the file.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
	<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Theme Uploader</title>
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
    <div class="container mt-5">
        <div class="options-container">
            <div class="heado mb-4"><i class="bi bi-palette"></i> Premium Theme Uploader</div>
            
            <!-- Display Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php elseif ($errorMessage): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <!-- Theme Upload Form -->
            <form action="upload_premium_theme.php" method="post" enctype="multipart/form-data" class="form-group">
                <div class="mb-3">
                    <label for="themeName" class="form-label">Theme Name</label>
                    <input type="text" class="form-control" id="themeName" name="themeName" placeholder="Enter theme name" required>
                </div>
                <div class="mb-3">
                    <label for="themeFile" class="form-label">Upload Theme File</label>
                    <input type="file" class="form-control" id="themeFile" name="themeFile" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload Theme</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>