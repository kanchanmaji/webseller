<?php
include 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $webName = mysqli_real_escape_string($conn, $_POST['webName']);
    
    // Define the directory path in the ../websites directory
    $webDir = "../websites/$webName";

    // Delete the folder and its contents
    if (is_dir($webDir)) {
        deleteDirectory($webDir); // Use the helper function to delete the directory
        echo "Deleted directory: $webDir\n";
    } else {
        echo "Directory not found: $webDir\n";
    }

    // Delete the web entry from both the websites and webs tables
    $deleteSql1 = "DELETE FROM websites WHERE web_name = ?";
    $deleteSql2 = "DELETE FROM webs WHERE web_name = ?";
    
    $stmt1 = mysqli_prepare($conn, $deleteSql1);
    mysqli_stmt_bind_param($stmt1, 's', $webName);
    mysqli_stmt_execute($stmt1);
    
    $stmt2 = mysqli_prepare($conn, $deleteSql2);
    mysqli_stmt_bind_param($stmt2, 's', $webName);
    mysqli_stmt_execute($stmt2);
    
    if (mysqli_stmt_affected_rows($stmt1) > 0 || mysqli_stmt_affected_rows($stmt2) > 0) {
        echo "Deleted web: $webName from database.\n";
    } else {
        echo "Web not found in database.\n";
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

// Redirect back to the manage webs page
header('Location: manage_webs.php');
exit();
?>