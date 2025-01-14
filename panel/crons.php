<?php
// Database connection
include 'config.php'; // Assuming this file contains your DB connection details

// Get current date
$currentDate = date('Y-m-d');

// Query to select all users with expiryDate equal to today's date
$sql = "SELECT web_name FROM websites WHERE expiry_date = '$currentDate'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // Loop through each expired entry
    while ($row = mysqli_fetch_assoc($result)) {
        $webName = $row['web_name'];

        // Delete from the database
        $deleteSql = "DELETE FROM websites WHERE web_name = '$webName'";
        if (mysqli_query($conn, $deleteSql)) {
            echo "Deleted entry for website: $webName from the database.\n";

            // Path to the website's directory
            $webDir = "../websites/$webName";

            // Check if directory exists, then delete it
            if (is_dir($webDir)) {
                deleteDirectory($webDir);
                echo "Deleted directory: $webDir\n";
            } else {
                echo "Directory $webDir does not exist.\n";
            }
        } else {
            echo "Error deleting website $webName from the database: " . mysqli_error($conn) . "\n";
        }
    }
} else {
    echo "No expired websites found.\n";
}

// Close the database connection
mysqli_close($conn);

// Function to delete a directory and its contents
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    $items = array_diff(scandir($dir), array('.', '..'));

    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectory($path); // Recursively delete subdirectories
        } else {
            unlink($path); // Delete file
        }
    }

    // Remove the directory itself
    rmdir($dir);
}
?>