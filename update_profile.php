<?php

include 'config.php';

session_start();
$user_id = $_SESSION['user_id'];

require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Initialize the logger
$logger = new Logger('csrf_logger');
$logger->pushHandler(new StreamHandler('csrf.log', Logger::WARNING));



if (!isset($_SESSION['csrf_token'])) {
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set Same-Site cookies for the session
$value = bin2hex(random_bytes(32));
setcookie('session_cookie', $value, [
   'samesite' => 'Strict',  //'lax' or 'none' can also be used
   'secure' => true,    // Recommended for HTTPS
]);


if(isset($_POST['update_profile'])){

   if (!empty($_POST['website'])) {
      $referringUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not Available'; 

      // Log the referring URL using Monolog
      $logger->info('Referring URL: ' . $referringUrl); 

      // Log the attempt or take other actions to handle bots
      $logger->warning('Potential CSRF attack detected: User ID ' . $_SESSION['user_id'] . ' attempted to submit the honeypot field.');

      // Redirect the user to a fake page or take appropriate action
      header("Location: fake_profile.php");
      exit();
   }

   if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      // Capture the referring URL
      $referringUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not Available'; 

      // Log the referring URL using Monolog
      $logger->info('Referring URL: ' . $referringUrl); 

      // Log a warning about the CSRF attack
      $logger->warning('Potential CSRF attack detected: User ID ' . $_SESSION['user_id'] . 
      ' attempted to update profile with unexpected request.');

      // Redirect the user to the fake version of the webpage
      header("Location: fake_profile.php");
      exit(); // Make sure to exit after the header redirection
   }
   else{

      $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
      $fetch = mysqli_fetch_assoc($select);
      if(!empty($_POST['update_name'])){
         $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   
      } else {
         $update_name = $fetch['name'];
      }

      if(!empty($_POST['update_email'])){
         $update_email = mysqli_real_escape_string($conn, $_POST['update_email']);
      
      } else {
         $update_email = $fetch['email'];
      }


      if($update_email != $fetch['email']){
         $message[] = 'email updated successfully!';
      }
      if($update_name != $fetch['name']){
         $message[] = 'username updated successfully!';
      }
      mysqli_query($conn, "UPDATE `user_form` SET name = '$update_name', email = '$update_email' WHERE id = '$user_id'") or die('query failed');

      //md5 is a cryptogrpahic hash function that hashes the passwords in the database
      //$new_pass = mysqli_real_escape_string($conn, md5($_POST['new_pass']));
      //$confirm_pass = mysqli_real_escape_string($conn, md5($_POST['confirm_pass']));


      $new_pass = mysqli_real_escape_string($conn, ($_POST['new_pass']));
      $confirm_pass = mysqli_real_escape_string($conn, ($_POST['confirm_pass']));

      if(!empty($new_pass) || !empty($confirm_pass)){
         if($new_pass != $confirm_pass){
            $message[] = 'confirm password not matched!';
         }else{
            mysqli_query($conn, "UPDATE `user_form` SET password = '$confirm_pass' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'password updated successfully!';
         }
      }
   }
}


//-----------------------------------------------------------------------------------------------


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Check if the Origin header is set and is from your own site
   if (!isset($_SERVER['HTTP_ORIGIN']) || parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST) != $_SERVER['SERVER_NAME']) {
       // Log a warning about the CSRF attack
       $logger->warning('Potential CSRF attack detected: User ID ' 
       . $_SESSION['user_id'] . ' attempted to update profile with unexpected request.');
   }
}


//-------------------------------------------------------------------------------------------


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>

   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<div class="update-profile">

   <?php
      $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
      if(mysqli_num_rows($select) > 0){
         $fetch = mysqli_fetch_assoc($select);
      }
   ?>

   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="honeypot">
         <input type="text" id="website" name="website">
      </div>
      <?php
         if(isset($message)){
            foreach($message as $message){
               echo '<div class="message">'.$message.'</div>';
            }
         }
      ?>
      <div class="flex">
         <div class="inputBox">
            <span>username :</span>
            <input type="text" name="update_name" value="<?php echo $fetch['name']; ?>" class="box">
            <span>your email :</span>
            <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box">
         </div>
         <div class="inputBox">
            <span>new password :</span>
            <input type="password" name="new_pass" placeholder="enter new password" class="box">
            <span>confirm password :</span>
            <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
         </div>
      </div>
      <input type="submit" value="update profile" name="update_profile" class="btn">
      <a href="home.php" class="delete-btn">go back</a>
   </form>

</div>

</body>
</html>