<?php
session_start();
include 'config.php'; // Database connection

// Initialize message variables
$message = '';
$messageType = '';

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

    // Check if the theme name already exists
    $query = 'SELECT COUNT(*) FROM themes WHERE theme_name = ?';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $themeName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);

    if ($count > 0) {
        // Theme name already exists
        $message = "The theme name '$themeName' already exists. Please choose a different name.";
        $messageType = "error";
    } else {
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

            // Move the uploaded file to the designated directory with the new name
            if (move_uploaded_file($themeFile['tmp_name'], $filePath)) {
                // Save the theme name into the database
                $query = 'INSERT INTO themes (theme_name) VALUES (?)';
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 's', $themeName);
                mysqli_stmt_execute($stmt);

                // Check if the query was successful
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = "Theme uploaded and saved successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to save theme details in the database.";
                    $messageType = "error";
                }

                mysqli_stmt_close($stmt);
            } else {
                $message = "Failed to upload the theme file.";
                $messageType = "error";
            }
        } else {
            $message = "No file uploaded or there was an error uploading the file.";
            $messageType = "error";
        }
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
    <title>Theme Uploader</title>
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
            <div class="heado mb-4">
                <h3><i class="bi bi-palette"></i> Theme Uploader</h3>
            </div>

            <!-- Display Message -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Theme Upload Form -->
            <form action="upload_theme.php" method="post" enctype="multipart/form-data" class="form-group">
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