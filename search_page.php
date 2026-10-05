<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
};


if (isset($_POST['add_to_wishlist'])) {

   $pid = $_POST['pid'] ?? '0';
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if ($check_wishlist_numbers->rowCount() > 0) {
      $message[] = 'Already added to wishlist!';
   } elseif ($check_cart_numbers->rowCount() > 0) {
      $message[] = 'Already added to cart!';
   } else {
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

if (isset($_POST['add_to_cart'])) {

   $pid = $_POST['pid'] ?? '0';
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if ($check_cart_numbers->rowCount() > 0) {
      $message[] = 'Already added to cart!';
   } else {

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if ($check_wishlist_numbers->rowCount() > 0) {
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}

$query = '';
$show_products = false;

if (isset($_POST['search_btn'])) {
   $query = trim($_POST['search_box'] ?? '');
   $show_products = true;
} elseif (isset($_POST['add_to_cart']) || isset($_POST['add_to_wishlist'])) {
   $query = trim($_POST['search_box'] ?? '');
   if (!empty($query)) {
      $show_products = true;
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page | BUDjEE</title>
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'header.php'; ?>

   <section class="search-form">
      <form action="" method="POST">

         <input type="text" class="box" name="search_box" placeholder="Search products..." required value="<?= htmlspecialchars($query) ?>">
         <input type="submit" name="search_btn" value="search" class="btn">
      </form>
   </section>

   <section class="products" style="padding-top: 0; min-height:100vh;">
      <div class="box-container">
         <?php
         if ($show_products) {
            $query = filter_var($query, FILTER_SANITIZE_STRING);
            $data = fetchFromSerpApiWithFallback($query, $serpapi_keys);

            if ($data !== false) {
               if (!empty($data['shopping_results'])) {
                  foreach ($data['shopping_results'] as $item) {
                     $title = $item['title'];
                     $price = $item['price'];
                     $thumbnail = $item['thumbnail'];
                     $pid = '0';

                     $price_number = floatval(preg_replace('/[^\d.]/', '', $price));
                     $price_php = $price_number * 55.33;
                     $price_php_formatted = '₱' . number_format($price_php, 2);
         ?>
                     <div class="box">
                        <img src="<?= htmlspecialchars($thumbnail); ?>" alt="<?= htmlspecialchars($title); ?>" />
                        <div class="name"><?= htmlspecialchars($title); ?></div>
                        <div class="price"><?= $price_php_formatted; ?></div>

                        <form method="POST" class="add-to-cart">
                           <input type="hidden" name="pid" value="<?= $pid; ?>">
                           <input type="hidden" name="p_name" value="<?= htmlspecialchars($title); ?>">
                           <input type="hidden" name="p_price" value="<?= $price_number; ?>">
                           <input type="hidden" name="p_image" value="<?= htmlspecialchars($thumbnail); ?>">
                           <input type="number" name="p_qty" value="1" min="1" />
                        </form>

                        <form method="POST" class="add-to-wishlist">
                           <input type="hidden" name="pid" value="<?= $pid; ?>">
                           <input type="hidden" name="p_name" value="<?= htmlspecialchars($title); ?>">
                           <input type="hidden" name="p_price" value="<?= $price_number; ?>">
                           <input type="hidden" name="p_image" value="<?= htmlspecialchars($thumbnail); ?>">
                           <button type="submit" name="add_to_wishlist">♡ Wishlist</button>
                        </form>

                        <div class="add-cart-bottom">
                           <form method="POST">
                              <input type="hidden" name="pid" value="<?= $pid; ?>">
                              <input type="hidden" name="p_name" value="<?= htmlspecialchars($title); ?>">
                              <input type="hidden" name="p_price" value="<?= $price_number; ?>">
                              <input type="hidden" name="p_image" value="<?= htmlspecialchars($thumbnail); ?>">
                              <input type="hidden" name="p_qty" value="1" />
                              <button type="submit" name="add_to_cart">🛒 Add to Cart</button>
                           </form>
                        </div>
                     </div>
         <?php
                  }
               } else {
                  echo '<p class="empty">No result found!</p>';
               }
            } else {
               echo '<p class="empty">Search error. Please try again.</p>';
            }
         }
         ?>
      </div>

   </section>

   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>