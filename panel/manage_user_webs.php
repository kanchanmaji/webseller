<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in and is a seller
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
$role = $row['role'];

if ($role !== "seller") {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$messageType = '';

// Handle delete request
if (isset($_POST['delete_web'])) {
    $webName = mysqli_real_escape_string($conn, $_POST['web_name']);

    // Delete the web directory
    $webDir = "../websites/$webName";
    if (is_dir($webDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($webDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($webDir); // Finally, remove the root directory
    }

    // Delete the web entry from the database
    $sql = "DELETE FROM webs WHERE web_name = ? AND seller = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $webName, $username);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Website deleted successfully!";
        $messageType = 'success';
    } else {
        $message = "Error deleting website from database.";
        $messageType = 'danger';
    }
}

// Fetch websites created by the logged-in seller
$query = 'SELECT web_name, theme_name, expiry_date FROM webs WHERE seller = ?';
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$websites = [];
while ($row = mysqli_fetch_assoc($result)) {
    $websites[] = $row;
}

// Close the database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Websites</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    	    <link rel="stylesheet" href="../panel/assets/css/style.css">
    	<!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #fff;
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
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
    <div class="container table-container">
        <h2 class="text-center mb-4">Your Created Websites</h2>

        <?php if (!empty($websites)): ?>
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Website Name</th>
                        <th>Theme Name</th>
                        <th>Expiry Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($websites as $web): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($web['web_name']); ?></td>
                            <td><?php echo htmlspecialchars($web['theme_name']); ?></td>
                            <td><?php echo htmlspecialchars($web['expiry_date']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this website?');">
                                    <input type="hidden" name="web_name" value="<?php echo htmlspecialchars($web['web_name']); ?>">
                                    <button type="submit" name="delete_web" class="btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No websites found.</p>
        <?php endif; ?>
    </div>

    <!-- Success/Error Popup Modal -->
    <?php if ($message != ''): ?>
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel"><?php echo ($messageType == 'success') ? 'Success' : 'Error'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-<?php echo $messageType; ?>" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <?php if ($message != ''): ?>
        <script>
            var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
            messageModal.show();
        </script>
    <?php endif; ?>
</body>
</html>