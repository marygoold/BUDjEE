<?php
@include 'config.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$email_error = false;

if (isset($_POST['submit'])) {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    $reset_code = rand(100000, 999999);
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_code'] = $reset_code;

    $name = htmlspecialchars($user['name']);

    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'budjee.website@gmail.com';
      $mail->Password = 'jdrn jyid ulki ymgm';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      $mail->setFrom('budjee.website@gmail.com', 'BUDjEE');
      $mail->addAddress($email, $name);

      $mail->isHTML(true);
      $mail->Subject = 'Your Password Reset Code';
      $mail->Body = "
      <html>
        <head>
          <style>
            body {
              background-color: #ffe6f0;
              font-family: 'Segoe UI', sans-serif;
              padding: 20px;
              color: #333;
            }
            .email-wrapper {
              background-color: #fff0f5;
              border: 3px solid #ffb6c1;
              padding: 20px;
              border-radius: 10px;
              max-width: 500px;
              margin: auto;
            }
            .code-box {
              font-size: 24px;
              font-weight: bold;
              color: #d63384;
              background-color: #fff;
              padding: 10px 20px;
              border-radius: 5px;
              border: 1px dashed #d63384;
              display: inline-block;
              margin-top: 10px;
              letter-spacing: 6px;
            }
            h2 {
              color: #d63384;
            }
            p {
              margin-top: 10px;
            }
          </style>
        </head>
        <body>
          <div class='email-wrapper'>
            <h2>Hi $name, from BUDjEE 💗</h2>
            <p>We received a request to reset your PASSWORD. Use the code below to continue:</p>
            <div class='code-box'>$reset_code</div>
            <p>If you didn’t request this, you can ignore this email.</p>
            <p style='margin-top:20px;'>– BUDjEE Team</p>
          </div>
        </body>
      </html>
      ";

      $mail->send();

      header('Location: reset_password.php');
      exit();
    } catch (Exception $e) {
      error_log("Mailer Error: " . $mail->ErrorInfo);
      $email_error = true;
    }
  } else {
    $email_error = true;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Forgot Password | BUDjEE</title>
  <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
  <link rel="stylesheet" href="css/components.css">
</head>

<body class="login_bg">
  <section class="form-container">
    <form action="" method="POST">
      <h3>Forgot Password</h3>
      <div class="input-wrapper">
        <input type="email" name="email" class="box <?= $email_error ? 'error' : '' ?>" placeholder="Enter your email" required>
        <?php if ($email_error): ?>
          <span class="placeholder-icon">!</span>
        <?php endif; ?>
      </div>
      <input type="submit" name="submit" class="btn" value="Send Code">
      <a href="login.php" class="btn">Back to Login</a>
    </form>
  </section>
</body>

</html>