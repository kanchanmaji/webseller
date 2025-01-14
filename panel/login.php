<?php
session_start();
include('config.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // SweetAlert success
            echo "
            <script>
                setTimeout(function() {
                    swal({
                        title: 'Login Successful!',
                        text: 'You will be redirected to the dashboard.',
                        icon: 'success',
                        timer: 2000,
                        buttons: false,
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                }, 500);
            </script>
            ";
        } else {
            // SweetAlert error for incorrect password
            echo "
            <script>
                setTimeout(function() {
                    swal({
                        title: 'Error!',
                        text: 'Incorrect password.',
                        icon: 'error',
                        timer: 2000,
                        buttons: false,
                    });
                }, 500);
            </script>
            ";
        }
    } else {
        // SweetAlert error for user not found
        echo "
        <script>
            setTimeout(function() {
                swal({
                    title: 'Error!',
                    text: 'User not found.',
                    icon: 'error',
                    timer: 2000,
                    buttons: false,
                });
            }, 500);
        </script>
        ";
    }
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
    <!-- SweetAlert CSS and JS -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../panel/assets/css/style.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>

    <!-- Main Content Area -->

    <div class="options-container">
      <div class="heado"><i class="bi bi-person-circle"></i> Login Account</div>
    
      <!-- Login Form -->
      <form action="login.php" method="post" class="form-group">
        <div class="mb-3">
          <label for="username" class="form-label">
            <i class="bi bi-person"></i> Username
          </label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">
            <i class="bi bi-lock"></i> Password
          </label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-2" style="height: 50px; border-radius: 10px 0 0 10px;">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
        
      </form>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>