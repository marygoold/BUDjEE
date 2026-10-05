<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'config.php';
session_start();

$name_error = false;
$email_error = false;
$pass_error = false;
$message = [];

if (isset($_POST['submit'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass = md5($_POST['pass']);
   $cpass = md5($_POST['cpass']);
   $image = $_FILES['image']['name'];
   $image_tmp = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
      $name_error = true;
      $message[] = 'Name can only contain letters and spaces!';
   } else {
      $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
      $check->execute([$email]);

      if ($check->rowCount() > 0) {
         $email_error = true;
      } elseif ($pass != $cpass) {
         $pass_error = true;
      } else {
         $code = rand(100000, 999999);
         $insert = $conn->prepare("INSERT INTO users (name, email, password, image, verify_code, verified) VALUES (?, ?, ?, ?, ?, 0)");
         $insert->execute([$name, $email, $pass, $image, $code]);
         move_uploaded_file($image_tmp, $image_folder);

         $_SESSION['verify_email'] = $email;
         unset($_SESSION['email_sent']);
         header('Location: verify_email.php');
         exit;
      }
   }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>Register | BUDjEE</title>
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
   <link rel="stylesheet" href="css/components.css">


   <link rel="stylesheet" href="css/components.css">

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

</head>

<body class="login_bg">

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo "<div class='message'><span>$msg</span></div>";
      }
   }
   ?>

   <section class="form-container">
      <form action="" method="POST" enctype="multipart/form-data">
         <h3>Register Now</h3>

         <?php if (isset($email) && !$email_error && !$pass_error && !$name_error): ?>
            <p>Verified Email: <strong><?= htmlspecialchars($email) ?></strong></p>
         <?php endif; ?>

         <div class="input-wrapper">
            <input
               type="text"
               name="name"
               class="box <?= $name_error ? 'error' : '' ?>"
               placeholder="Enter your name"
               pattern="[A-Za-z\s]+"
               title="Name can only contain letters and spaces"
               required>
            <?php if ($name_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>

         <div class="input-wrapper">
            <input type="email" name="email" class="box <?= $email_error ? 'error' : '' ?>" placeholder="Enter your email" required>
            <?php if ($email_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>

         <div class="input-wrapper">
            <input type="password" id="pass" name="pass" class="box <?= $pass_error ? 'error' : '' ?>" placeholder="Enter your password" required>
            <i class="fas fa-eye toggle-password" toggle="#pass"></i>
            <?php if ($pass_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>

         <div class="input-wrapper">
            <input type="password" id="cpass" name="cpass" class="box <?= $pass_error ? 'error' : '' ?>" placeholder="Confirm your password" required>
            <i class="fas fa-eye toggle-password" toggle="#cpass"></i>
            <?php if ($pass_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>


         <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">

         <input type="submit" name="submit" class="btn" value="Register Now">
         <p>Already have an account? <a href="login.php">Login now</a></p>
      </form>
   </section>

   <script>
      document.querySelectorAll('.toggle-password').forEach(icon => {
         icon.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('toggle'));
            const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
            target.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
         });
      });
   </script>

</body>

</html>