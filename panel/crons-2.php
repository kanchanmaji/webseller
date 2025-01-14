<?php
include 'config.php'; // Database connection

// Get the current date
$currentDate = date('Y-m-d');

// Query to select webs where expiry date matches the current date
$sql = "SELECT web_name FROM webs WHERE expiry_date = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $currentDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Process each web that has expired
while ($row = mysqli_fetch_assoc($result)) {
    $webName = $row['web_name'];

    // Define the directory path in the ../websites directory
    $webDir = "../websites/$webName";

    // Delete the folder and its contents
    if (is_dir($webDir)) {
        deleteDirectory($webDir); // Use the helper function to delete the directory
        echo "Deleted directory: $webDir\n";
    } else {
        echo "Directory not found: $webDir\n";
    }

    // Delete the web entry from the database
    $deleteSql = "DELETE FROM webs WHERE web_name = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, 's', $webName);
    if (mysqli_stmt_execute($deleteStmt)) {
        echo "Deleted web: $webName from database.\n";
    } else {
        echo "Error deleting web: " . mysqli_error($conn) . "\n";
    }
}

// Function to recursively delete a directory and its contents
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $filePath = "$dir/$file";
        is_dir($filePath) ? deleteDirectory($filePath) : unlink($filePath);
    }

    return rmdir($dir);
}

// Close the database connection
mysqli_close($conn);
?>