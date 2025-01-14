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

// Fetch themes for dropdown from `themes` table
$query = 'SELECT theme_name FROM premium_themes';
$themeResult = mysqli_query($conn, $query);
$themes = [];
while ($row = mysqli_fetch_assoc($themeResult)) {
    $themes[] = $row['theme_name'];
}

// Fetch sellers for dropdown from `users` table
$sellerQuery = 'SELECT username FROM users WHERE role = "seller"';
$sellerResult = mysqli_query($conn, $sellerQuery);
$sellers = [];
while ($row = mysqli_fetch_assoc($sellerResult)) {
    $sellers[] = $row['username'];
}

$error = ''; // Variable to store error message
$success = ''; // Variable to store success message
$usernameToShow = ''; // Variable to store username for modal
$passwordToShow = ''; // Variable to store password for modal

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Hash password for security
    $expiryDate = mysqli_real_escape_string($conn, $_POST['expiryDate']);
    $themeName = mysqli_real_escape_string($conn, $_POST['selectTheme']);
    $webName = mysqli_real_escape_string($conn, $_POST['webname']);
    $seller = mysqli_real_escape_string($conn, $_POST['selectSeller']);

    // Check if the username or web name already exists
    $userQuery = 'SELECT COUNT(*) FROM webs WHERE username = ?';
    $stmt = mysqli_prepare($conn, $userQuery);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $webQuery = 'SELECT COUNT(*) FROM webs WHERE web_name = ?';
    $stmt = mysqli_prepare($conn, $webQuery);
    mysqli_stmt_bind_param($stmt, 's', $webName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $webCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($userCount > 0) {
        $error = "Username already exists.";
    } elseif ($webCount > 0) {
        $error = "Web name already exists.";
    } else {
        // Insert data into webs table
        $sql = "INSERT INTO webs (username, password, expiry_date, theme_name, web_name, seller) 
                VALUES ('$username', '$hashedPassword', '$expiryDate', '$themeName', '$webName', '$seller')";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Website created successfully!";
            $usernameToShow = htmlspecialchars($username);
            $passwordToShow = htmlspecialchars($password);

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
                $success .= " Theme extracted successfully!";
            } else {
                $error .= " Failed to extract theme!";
            }

            // Extract mail_panel.zip file
            if ($zip->open($mailPanelZipPath) === TRUE) {
                $zip->extractTo($webDir); // Extract mail panel into the same folder
                $zip->close();
                $success .= " Mail panel extracted successfully!";
            } else {
                $error .= " Failed to extract mail panel!";
            }

        } else {
            $error = "Error: " . mysqli_error($conn);
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
    <title>Create Premium Web</title>
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
                            <a class="nav-link" href="create_webs.php"><i class="bi bi-file-earmark-plus"></i> Create Web</a>
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
                <h3><i class="bi bi-file-earmark-plus"></i> Create Premium Web</h3>
            </div>
            
            <!-- Create Web Form -->
            <form id="createWebForm" action="create_premium_web.php" method="post" class="form-group">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <script>
                        // Show modal if the creation is successful
                        var myModal = new bootstrap.Modal(document.getElementById('infoModal'));
                        myModal.show();
                        document.getElementById('modalUsername').innerText = '<?php echo $usernameToShow; ?>';
                        document.getElementById('modalPassword').innerText = '<?php echo $passwordToShow; ?>';
                    </script>
                <?php endif; ?>

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

                <!-- Seller Selection -->
                <div class="mb-3">
                    <label for="selectSeller" class="form-label">
                        <i class="bi bi-person"></i> Select Seller
                    </label>
                    <select class="form-control" id="selectSeller" name="selectSeller" required>
                        <option value="">--Select a seller--</option>
                        <?php foreach ($sellers as $seller): ?>
                            <option value="<?php echo htmlspecialchars($seller); ?>"><?php echo htmlspecialchars($seller); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Create Web Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Create Premium Web
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Website Created Successfully</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Username:</strong> <span id="modalUsername"></span></p>
                    <p><strong>Password:</strong> <span id="modalPassword"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>