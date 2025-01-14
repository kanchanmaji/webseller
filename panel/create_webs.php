<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION["username"])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION["username"];
$query = 'SELECT `role`, `web_limit` FROM users WHERE `username` = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
$role = $row['role'];
$web_limit = $row['web_limit'];

if ($role !== "seller") {
    header('Location: dashboard.php');
    exit();
}

// Fetch themes for dropdown from `themes` and `premium_theme` tables
$query = 'SELECT theme_name FROM themes';
$result = mysqli_query($conn, $query);
$themes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $themes[] = $row['theme_name'];
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check seller's current web count
    $seller_username = mysqli_real_escape_string($conn, $_POST['username']);
    $query = 'SELECT COUNT(*) FROM websites WHERE username = ?';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $seller_username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    $current_web_count = $row[0];

    // Check if the user has reached their web limit
    if ($current_web_count >= $web_limit) {
        $errorMessage = 'This seller has reached the web limit!';
    } else {
        // Get the form data
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password for security
        $expiryDate = mysqli_real_escape_string($conn, $_POST['expiryDate']);
        $themeName = mysqli_real_escape_string($conn, $_POST['selectTheme']);
        $webName = mysqli_real_escape_string($conn, $_POST['webname']);

        // Insert data into websites table
        $sql = "INSERT INTO websites (username, password, expiry_date, theme_name, web_name) 
                VALUES ('$username', '$password', '$expiryDate', '$themeName', '$webName')";
        
        if (mysqli_query($conn, $sql)) {
            $successMessage = 'Website created successfully!';
            
            // Extract theme and mail panel zip files
            $webDir = "../websites/$webName"; // Main directory where new folder is created

            if (!file_exists($webDir)) {
                mkdir($webDir, 0777, true); // Create the folder for the new website
            }

            // Define paths for the zip files
            $themeZipPath = "../scripts/$themeName.zip";
            $mailPanelZipPath = "../scripts/mail_panel.zip";

            // Extract theme zip file
            $zip = new ZipArchive;
            if ($zip->open($themeZipPath) === TRUE) {
                $zip->extractTo($webDir); // Extract theme to the created folder
                $zip->close();
                $successMessage .= ' Theme extracted successfully!';
            } else {
                $errorMessage .= ' Failed to extract theme!';
            }

            // Extract mail_panel.zip file
            if ($zip->open($mailPanelZipPath) === TRUE) {
                $zip->extractTo($webDir); // Extract mail panel into the same folder
                $zip->close();
                $successMessage .= ' Mail panel extracted successfully!';
            } else {
                $errorMessage .= ' Failed to extract mail panel!';
            }

        } else {
            $errorMessage = 'Error: ' . mysqli_error($conn);
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
    <title>Create Web</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../panel/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
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
            <h3><i class="bi bi-file-earmark-plus"></i> Create Web</h3>
        </div>

        <!-- Success and Error Messages -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert" style="color: black;">
                <?php echo $errorMessage; ?>
            </div>
        <?php elseif ($successMessage): ?>
            <div class="alert alert-success" role="alert" style="color: black;">
                <?php echo $successMessage; ?>
            </div>
            
            <!-- Display Admin and User Links -->
            <div class="alert alert-info" role="alert" style="color: black;">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($_POST['username']); ?></p>
                <p><strong>Password:</strong> <?php echo htmlspecialchars($_POST['password']); ?></p>
                <p><strong>Admin Link:</strong> <span id="adminLink"><?php echo "$weblink + websites/$webName/login.php"; ?></span> 
                    <button class="btn btn-secondary btn-sm" onclick="copyText('adminLink')">Copy</button>
                </p>
                <p><strong>User Link:</strong> <span id="userLink"><?php echo "$weblink + websites/$webName/"; ?></span> 
                    <button class="btn btn-secondary btn-sm" onclick="copyText('userLink')">Copy</button>
                </p>
            </div>
        <?php endif; ?>

        <!-- Create Web Form -->
        <form action="create_webs.php" method="post" class="form-group">

            <!-- Username Input -->
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person"></i> Username
                </label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
            </div>

            <!-- Password Input -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-key"></i> Password
                </label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>

            <!-- Expiry Date Input -->
            <div class="mb-3">
                <label for="expiryDate" class="form-label">
                    <i class="bi bi-calendar"></i> Expiry Date
                </label>
                <input type="date" class="form-control" id="expiryDate" name="expiryDate" required>
            </div>

            <!-- Theme Selection -->
            <div class="mb-3">
                <label for="selectTheme" class="form-label">
                    <i class="bi bi-palette"></i> Select Theme
                </label>
                <select class="form-control" id="selectTheme" name="selectTheme" required>
                    <option value="">--Select a theme--</option>
                    <?php foreach ($themes as $theme): ?>
                        <option value="<?php echo htmlspecialchars($theme); ?>"><?php echo htmlspecialchars($theme); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Web Name Input -->
            <div class="mb-3">
                <label for="webname" class="form-label">
                    <i class="bi bi-files"></i> Web Name
                </label>
                <input type="text" class="form-control" id="webname" name="webname" placeholder="Enter web name" required>
            </div>

            <!-- Create Web Button -->
            <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle"></i> Create Web
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Copy Text Script -->
<script>
    function copyText(elementId) {
        var textToCopy = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(textToCopy).then(function() {
            alert('Copied to clipboard');
        }).catch(function(error) {
            alert('Failed to copy text: ' + error);
        });
    }
</script>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>