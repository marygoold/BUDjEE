<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
};

if (isset($_POST['update_profile'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $update_profile = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
   $update_profile->execute([$name, $user_id]);


   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;
   $old_image = $_POST['old_image'];

   if (!empty($image)) {
      if ($image_size > 2000000) {
         $image_error = 'Image size is too large!';
      } else {
         $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);
         if ($update_image) {
            move_uploaded_file($image_tmp_name, $image_folder);
            if (file_exists('uploaded_img/' . $old_image)) {
               unlink('uploaded_img/' . $old_image);
            }
         };
      };
   };
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Profile | BUDjEE</title>
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>

<body class="user_bg">

   <?php include 'header.php'; ?>

   <section class="update-profile">

      <h1 class="title">update profile</h1>

      <form action="" method="POST" enctype="multipart/form-data">
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <div class="flex">
            <div class="inputBox">
               <span>Username :</span>
               <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="update username" required class="box">

               <span>Email :</span>
               <?php
               $email = $fetch_profile['email'] ?? '';
               $masked_email = substr($email, 0, 3) . '*****@gmail.com';
               ?>
               <input type="text" value="<?= $masked_email; ?>" class="box" disabled>

               <span>Update Picture :</span>
               <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
               <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">

               <?php if (!empty($image_error)) : ?>
                  <div style="color: red; margin-top: 5px; font-size: 17px;"><?= $image_error; ?></div>

               <?php endif; ?>

            </div>
         </div>

         <div class="flex-btn">
            <input type="submit" class="btn" value="update" name="update_profile">
            <a href="home.php" class="option-btn" style="background-color: #938abc; color: white;">go back</a>

         </div>
      </form>


   </section>
   <script src="js/script.js"></script>

</body>

</html>