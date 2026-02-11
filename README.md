Web Seller Panel Setup Guide

This guide provides instructions to set up the Web Seller Panel on a cPanel hosting environment. Follow the steps below carefully to ensure the script runs smoothly.


---

Features

User-friendly interface to manage products and sales.

Secure database connection for storing and retrieving data.

Configurable settings for customization.

cPanel-based deployment for easy hosting.



---

Requirements

1. PHP 7.4 or later.


2. MySQL 5.7 or later.


3. cPanel hosting account.


4. Access to the cPanel File Manager or FTP client.




---

Setup Instructions

Step 1: Upload the Files

1. Log in to your cPanel account.


2. Go to File Manager and navigate to the root directory where you want to install the script (e.g., public_html/webseller).


3. Upload the script files to this directory.


4. Extract the uploaded .zip file (if applicable).



Step 2: Create a Database

1. In cPanel, go to MySQL Databases.


2. Create a new database (e.g., webseller_db).


3. Create a database user (e.g., webseller_user) and set a secure password.


4. Assign the user to the database with All Privileges.



Step 3: Configure the Script

1. Open the config.php file located in the script directory.


2. Update the following fields with your database details:

<?php
// Database configuration
$db_host = 'localhost';           // Hostname (usually localhost)
$db_name = 'your_database_name';  // Replace with your database name
$db_user = 'your_database_user';  // Replace with your database username
$db_pass = 'your_database_pass';  // Replace with your database password

// Additional configurations
$site_url = 'http://yourdomain.com/webseller'; // Replace with your domain URL
?>


3. Save the changes.



Step 4: Import the Database

1. In cPanel, go to phpMyAdmin.


2. Select the database you created in Step 2.


3. Click on the Import tab.


4. Choose the .sql file provided with the script (usually in the database folder).


5. Click Go to import the database structure and data.



Step 5: Test the Application

1. Open your web browser and navigate to your site (e.g., http://yourdomain.com/webseller).


2. Log in with the default admin credentials provided in the documentation or as specified in the database.


3. Check if the site is functioning as expected.




---

Customization

1. Update the site name, logo, and other settings in the admin panel (if available).


2. Modify styles and themes in the assets or css directory.




---

Troubleshooting

Database Connection Error: Ensure the database credentials in config.php are correct.

File Not Found: Verify that all files were uploaded to the correct directory.

Permission Issues: Ensure necessary files and folders have write permissions (e.g., chmod 755).



---

Support

If you encounter any issues or need further assistance, please contact the developer support team at support@codewithkanchan.com.
