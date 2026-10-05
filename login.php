<?php
@include 'config.php';
session_start();

if (isset($_SESSION['flash_message'])) {
   echo '<div class="success-message">' . $_SESSION['flash_message'] . '</div>';
   unset($_SESSION['flash_message']);
}

$login_error = false;

if (isset($_POST['submit'])) {
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $pass = filter_var(md5($_POST['pass']), FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($row) {
      if ($row['user_type'] === 'admin') {
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');
         exit();
      } elseif ($row['user_type'] === 'user') {
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
         exit();
      }
   } else {
      $login_error = true;
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login | BUDjEE</title>
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/components.css">
   <style>
      .toggle-password {
         position: absolute;
         right: 15px;
         top: 50%;
         transform: translateY(-50%);
         cursor: pointer;
         color: #888;
      }

      .input-wrapper {
         position: relative;
      }
   </style>
</head>

<body class="login_bg">

   <?php if (isset($message)) {
      foreach ($message as $message) {
         echo "<div class='message'><span>$message</span><i class='fas fa-times' onclick='this.parentElement.remove();'></i></div>";
      }
   } ?>

   <section class="form-container">
      <form action="" method="POST">
         <h3>Login Now</h3>

         <div class="input-wrapper">
            <input
               type="email"
               name="email"
               class="box <?= $login_error ? 'error' : '' ?>"
               placeholder="Enter your email"
               required>
            <?php if ($login_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>

         <div class="input-wrapper">
            <input
               type="password"
               name="pass"
               id="password"
               class="box <?= $login_error ? 'error' : '' ?>"
               placeholder="Enter your password"
               required>
            <i class="fas fa-eye fa-lg toggle-password" id="togglePassword"></i>

            <?php if ($login_error): ?>
               <span class="placeholder-icon">!</span>
            <?php endif; ?>
         </div>

         <input type="submit" value="Login" class="btn" name="submit">
         <p><a href="forgot_password.php">Forgot Password?</a></p>
         <p>Don't have an account? &nbsp; <a href="register.php">Register now</a></p>
      </form>
   </section>

   <script>
      const togglePassword = document.querySelector('#togglePassword');
      const password = document.querySelector('#password');

      togglePassword.addEventListener('click', function() {
         const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
         password.setAttribute('type', type);
         this.classList.toggle('fa-eye');
         this.classList.toggle('fa-eye-slash');
      });
   </script>

</body>

</html>