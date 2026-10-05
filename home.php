<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit();
}

if (isset($_POST['add_to_wishlist'])) {
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if ($check_wishlist->rowCount() > 0) {
      $message[] = 'Already added to wishlist!';
   } elseif ($check_cart->rowCount() > 0) {
      $message[] = 'Already added to cart!';
   } else {
      $add_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
      $add_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

if (isset($_POST['add_to_cart'])) {
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if ($check_cart->rowCount() > 0) {
      $message[] = 'Already added to cart!';
   } else {
      $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist->execute([$p_name, $user_id]);

      if ($check_wishlist->rowCount() > 0) {
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $add_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      $add_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <title>Home | BUDjEE</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">
</head>

<body>

   <?php include 'header.php'; ?>

   <div class="home-bg">
      <section class="home">
         <div class="content">
            <span>Grocery List Budgeting</span>
            <h3>Welcome to BUDjEE</h3>
            <a href="#category-section" class="scroll-btn">Browse Categories</a>
         </div>
      </section>
   </div>

   <section class="home-category" id="category-section">
      <h1 class="title">List of Categories</h1>
      <div class="box-container">

         <?php
         $categories = [
            ['meat', 'meat.gif'],
            ['fruits', 'fruit1.gif'],
            ['vegetables', 'vege.gif'],
            ['seafood', 'fish.gif'],
            ['spices', 'spice.gif'],
            ['dairy', 'dairy.gif'],
            ['condiments', 'orange.gif'],
            ['processed', 'burger.gif'],
            ['snacks', 'donut.gif'],
            ['beverages', 'water.gif'],
            ['alcohol', 'beer.gif'],
            ['hygiene', 'toilet.gif']
         ];

         foreach ($categories as $category) {
            echo '
            <a href="grocery.php?category=' . urlencode($category[0]) . '" class="box">
               <img src="images/' . $category[1] . '" alt="' . htmlspecialchars($category[0]) . '">
               <h3>' . ucfirst(htmlspecialchars($category[0])) . '</h3>
            </a>';
         }
         ?>

      </div>
   </section>

   <br><br><br><br>

   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>
</body>

</html>