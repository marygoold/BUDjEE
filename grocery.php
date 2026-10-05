<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit();
}

$category = $_GET['category'] ?? 'grocery';
$category = htmlspecialchars($category);

$sort = $_GET['sort'] ?? 'name_asc';

$query = $category === 'grocery' ? 'grocery products' : $category;

$data = fetchFromSerpApiWithFallback($query, $serpapi_keys);

if ($data === false) {
   $results = [];
   $message[] = 'All API keys exhausted or error fetching data.';
} else {
   $results = $data['shopping_results'] ?? [];
}


usort($results, function ($a, $b) use ($sort) {
   $nameA = $a['title'] ?? '';
   $nameB = $b['title'] ?? '';
   $priceA = $a['extracted_price'] ?? 0;
   $priceB = $b['extracted_price'] ?? 0;

   switch ($sort) {
      case 'price-asc':
         return $priceA <=> $priceB;
      case 'price-desc':
         return $priceB <=> $priceA;
      case 'name-asc':
         return strcasecmp($nameA, $nameB);
      case 'name-desc':
         return strcasecmp($nameB, $nameA);
      default:
         return 0;
   }
});


if (isset($_POST['add_to_wishlist'])) {

   $pid = $_POST['pid'];
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

   $pid = $_POST['pid'];
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <title><?= ucfirst($category) ?> | BUDjEE</title>
   <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
   <link rel="stylesheet" href="css/style.css" />


</head>

<body>

   <?php include 'header.php'; ?>

   <section class="products">
      <h1 class="title"><?= ucfirst($category) ?> Products</h1>

      <form method="GET" class="sorting-form" id="sortForm">
         <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
         <label for="sort-by">Sort by:</label>
         <select id="sort-by" name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
            <option value="default" <?= $sort == 'default' ? 'selected' : '' ?>>Default</option>
            <option value="price-asc" <?= $sort == 'price-asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price-desc" <?= $sort == 'price-desc' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="name-asc" <?= $sort == 'name-asc' ? 'selected' : '' ?>>Name: A to Z</option>
            <option value="name-desc" <?= $sort == 'name-desc' ? 'selected' : '' ?>>Name: Z to A</option>
         </select>

      </form>


      <div class="box-container">
         <?php if (!empty($results)): ?>
            <?php foreach ($results as $product): ?>
               <div class="box">
                  <img src="<?= htmlspecialchars($product['thumbnail'] ?? ''); ?>" alt="<?= htmlspecialchars($product['title'] ?? 'Product'); ?>" />
                  <div class="name"><?= htmlspecialchars($product['title'] ?? 'No Title'); ?></div>
                  <div class="price">
                     <?php
                     $priceUsd = $product['extracted_price'] ?? null;
                     if ($priceUsd) {
                        $exchangeRate = 55.33;
                        $pricePhp = $priceUsd * $exchangeRate;
                        echo '₱' . number_format($pricePhp, 2);
                     } else {
                        echo 'No price';
                     }
                     ?>
                  </div>


                  <form method="POST" class="add-to-cart">
                     <input type="hidden" name="pid" value="<?= htmlspecialchars($product['product_id'] ?? ''); ?>">
                     <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['title'] ?? ''); ?>">
                     <input type="hidden" name="p_price" value="<?= htmlspecialchars($product['extracted_price'] ?? ''); ?>">
                     <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['thumbnail'] ?? ''); ?>">
                     <input type="number" name="p_qty" value="1" min="1" />
                  </form>

                  <form method="POST" class="add-to-wishlist">
                     <input type="hidden" name="pid" value="<?= htmlspecialchars($product['product_id'] ?? ''); ?>">
                     <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['title'] ?? ''); ?>">
                     <input type="hidden" name="p_price" value="<?= htmlspecialchars($product['extracted_price'] ?? ''); ?>">
                     <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['thumbnail'] ?? ''); ?>">
                     <button type="submit" name="add_to_wishlist">♡ Wishlist</button>

                  </form>


                  <div class="add-cart-bottom">
                     <form method="POST">
                        <input type="hidden" name="pid" value="<?= htmlspecialchars($product['product_id'] ?? ''); ?>">
                        <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['title'] ?? ''); ?>">
                        <input type="hidden" name="p_price" value="<?= htmlspecialchars($product['extracted_price'] ?? ''); ?>">
                        <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['thumbnail'] ?? ''); ?>">
                        <input type="hidden" name="p_qty" value="1" />
                        <button type="submit" name="add_to_cart">🛒 Add to Cart</button>
                     </form>
                  </div>
               </div>

            <?php endforeach; ?>
         <?php else: ?>
            <p class="empty">No products found in this category.</p>
         <?php endif; ?>
      </div>
   </section>

   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>
</body>

</html>