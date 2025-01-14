<?php
$merchantId = '47657418';
$token = '51364b5715f547b1a2e37e93dff0f3a8';
$utrFile = 'utr.json'; // file to store verified UTRs
$givenAmount = 1; // amount given by admin
$error = ''; // Initialize error variable
$success = false; // Initialize success variable
$key = ''; // Initialize key variable

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
                                $error = 'Amount must be the same as given by admin (₹' . $givenAmount . '). Received: ₹' . $transaction['amount'];
                            } elseif ($transaction['status'] === 'SUCCESS') {
                                // Save the verified UTR to the file
                                $utrData[] = $utr;
                                file_put_contents($utrFile, json_encode($utrData));

                                // Generate a random password for the seller
                                $password = bin2hex(random_bytes(4)); // Random password

                                // Insert the seller into the database
                                $sql = "INSERT INTO users (username, password, web_limit, role) VALUES (?, ?, ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Hash password for security
                                $web_limit = 25;
                                $role = 'seller';

                                $stmt->bind_param('ssis', $chosenUsername, $hashedPassword, $web_limit, $role);

                                if ($stmt->execute()) {
                                    // Set success flag and prepare message
                                    $success = true;
                                    $message = "Seller account created successfully. Username: $chosenUsername, Password: $password";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Verification</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                            <a class="nav-link active" href="login.php"><i class="bi bi-house-door"></i> Login In</a>
                        </li>
                        
                    <?php else: ?>
      <li class="nav-item">
                            <a class="nav-link active" href="login.php"><i class="bi bi-house-door"></i> Login In</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


		
		
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

/* Form input styles */
.qr-section input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 2px solid #ff0000; /* Red border */
    border-radius: 5px;
    font-size: 16px;
    margin-top: 20px;
}

/* Input placeholder text */
.qr-section input[type="text"]::placeholder {
    color: #ff0000; /* Red placeholder text */
}

/* Button styles */
.qr-section button {
    width: 100%;
    padding: 12px;
    background-color: #ff0000; /* Red button */
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

/* Button hover effect */
.qr-section button:hover {
    background-color: #c0392b; /* Darker red on hover */
}

/* SweetAlert styling (optional: modifies default SweetAlert styles to fit the red theme) */
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
    <!-- QR Code Section -->
    <section class="qr-section mt-5">
    	 <div class="qr-image text-center">
            <img src="../panel/assets/img/qr_code.png" alt="QR Code for Payment" width="200px" >
        </div>
        <form id="utrform" method="post">
            <input type="text" name="utr" class="form-control" placeholder="Enter 12-digit UTR number" maxlength="12" required>
            <input type="text" name="username" class="form-control" placeholder="Choose your username" required>
            <button class="btn btn-primary" name="submit">Submit</button>
        </form>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

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
            html: 'UTR verified successfully.<br>Username: <strong><?php echo htmlspecialchars($chosenUsername); ?></strong><br>Password: <strong><?php echo htmlspecialchars($password); ?></strong>',
            icon: 'success',
            confirmButtonText: 'OK',
            didOpen: () => {
                const username = '<?php echo htmlspecialchars($chosenUsername); ?>';
                const password = '<?php echo htmlspecialchars($password); ?>';
                const copyButton = document.createElement('button');
                copyButton.innerText = 'Copy Username & Password';
                copyButton.classList.add('btn', 'btn-success');
                Swal.getHtmlContainer().appendChild(copyButton);

                copyButton.addEventListener('click', () => {
                    navigator.clipboard.writeText(`Username: ${username}\nPassword: ${password}`);
                    Swal.fire('Copied!', 'Username and Password copied to clipboard.', 'success');
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
</html>