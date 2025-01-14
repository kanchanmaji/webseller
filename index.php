<?php
// Set HTTP response code to 404
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
        }
        h1 {
            font-size: 48px;
            color: #ff4d4d;
        }
        p {
            font-size: 20px;
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 15px 25px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            color: #fff;
            background-color: #0088cc;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0077aa;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>

    <h1>404 - File Not Found</h1>
    <p>Oops! It looks like the page you're looking for doesn't exist.</p>
    
    <!-- Shiny Telegram Button -->
    <a href="https://t.me/Kanchan_jnv" class="button">Message Us on Telegram</a>

</body>
</html>