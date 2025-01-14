<?php
$merchantId = '47657418';
$token = '51364b5715f547b1a2e37e93dff0f3a8';
$utrFile = 'utr.json'; // File to store verified UTRs
$givenAmount = 250; // Amount that user must pay to increase web limit
$error = ''; // Initialize error variable
$success = false; // Initialize success variable
$key = ''; // Initialize key variable
$webLimitIncrease = 25; // Number of websites added after payment

session_start();
include 'config.php'; // Database connection

if (isset($_POST['submit'])) {
    $utr = $_POST['utr'];
    $chosenUsername = $_POST['username']; // Get the chosen username from the input

    // Basic UTR validation
    if (!preg_match('/^[A-Za-z0-9]+$/', $utr) || strlen($utr) !== 12) {
        $error = 'Invalid UTR format or length';
    } elseif (empty($chosenUsername)) {
        $error = 'Username cannot be empty';
    } else {
        // Check if UTR already exists in the file
        if (file_exists($utrFile)) {
            $utrData = json_decode(file_get_contents($utrFile), true);
            if (!is_array($utrData)) {
                $utrData = [];
            }
            if (in_array($utr, $utrData)) {
                $error = 'UTR has already been used';
            }
        } else {
            $utrData = []; // Initialize as an empty array if the file doesn't exist
        }

        if (!$error) {
            // Create the API URL
            $url = 'https://payments-tesseract.bharatpe.in/api/v1/merchant/transactions?module=PAYMENT_QR&merchantId=' . $merchantId . '&token=' . $token . '&utr=' . $utr;

            // Initialize cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $error_code = curl_errno($ch);
            curl_close($ch);

            if ($error_code) {
                $error = 'cURL error: ' . curl_error($ch);
            } else {
                $data = json_decode($response, true);

                // Check if decoding was successful
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $error = 'JSON Decode Error: ' . json_last_error_msg();
                } else {
                    // Check if the API response contains the required data
                    if (isset($data['data']['transactions'][0])) {
                        $transaction = $data['data']['transactions'][0];

                        // Match the bankReferenceNo with UTR
                        if ($transaction['bankReferenceNo'] === $utr) {
                            // Check the amount
                            if ($transaction['amount'] != $givenAmount) {
                                $error = 'Amount must be ₹' . $givenAmount . '. Received: ₹' . $transaction['amount'];
                            } elseif ($transaction['status'] === 'SUCCESS') {
                                // Save the verified UTR to the file
                                $utrData[] = $utr;
                                file_put_contents($utrFile, json_encode($utrData));

                                // Update the web limit for the user
                                $sql = "UPDATE users SET web_limit = web_limit + ? WHERE username = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('is', $webLimitIncrease, $chosenUsername);

                                if ($stmt->execute()) {
                                    // Set success flag and prepare message
                                    $success = true;
                                    $message = "UTR verified successfully. Web limit increased by $webLimitIncrease.";
                                } else {
                                    $error = 'Error: ' . $stmt->error;
                                }

                                $stmt->close(); // Close statement
                            } else {
                                $error = 'Transaction not successful. Status: ' . $transaction['status'];
                            }
                        } else {
                            $error = 'UTR does not match the bankReferenceNo.';
                        }
                    } else {
                        $error = 'Invalid UTR or no matching transaction found.';
                    }
                }
            }
        }
    }
}

$conn->close(); // Close database connection
?>
<?php
// PHP logic remains the same as in the previous script
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Payment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../panel/assets/css/style.css">

</head>
<body>
	    <style>
        /* General body styles */
        
        /* QR Section Styles */
        .qr-section {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        /* QR code image */
        .qr-image {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Form input styles */
        .qr-section input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 2px solid #ff0000;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Input placeholder text */
        .qr-section input[type="text"]::placeholder {
            color: #ff0000;
        }

        /* Button styles */
        .qr-section button {
            width: 100%;
            padding: 12px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Button hover effect */
        .qr-section button:hover {
            background-color: #c0392b;
        }
        .swal2-popup {
    font-family: Arial, sans-serif;
    font-size: 16px;
}

.swal2-title {
    color: #ff0000; /* Red title in SweetAlert */
}

.swal2-confirm {
    background-color: #ff0000 !important;
    border: none !important;
    box-shadow: none !important;
}

.swal2-confirm:hover {
    background-color: #ff0000 !important;
}
    </style>
    
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



    <!-- QR Code Payment Section -->
    <section class="qr-section mt-5">
        <div class="qr-image text-center">
            <img src="../panel/assets/img/qr_code.png" alt="QR Code for Payment" width="200px">
        </div>
        <form id="utrform" method="post">
            <input type="text" name="utr" class="form-control" placeholder="Enter 12-digit UTR number" maxlength="12" required>
            <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            <button class="btn btn-primary" name="submit">Submit</button>
        </form>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Error/Success Alerts -->
    <?php if ($error): ?>
    <script>
        Swal.fire({
            title: 'Error!',
            text: <?php echo json_encode($error); ?>,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
    <?php elseif ($success): ?>
    <script>
        Swal.fire({
            title: 'Success!',
            text: <?php echo json_encode($message); ?>,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
    <?php endif; ?>
</body>
</html>