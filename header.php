<?php

if (isset($message)) {
   foreach ($message as $message) {
      echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header">

   <div class="flex">

      <a href="home.php" class="logo">BUDjEE<span>.</span></a>

      <a href="search_page.php" class="search-box"><i class="fas fa-search"></i> SEARCH</a>

      <nav class="navbar">
         <a href="home.php">HOME</a>
         <a href="grocery.php">PRODUCT</a>
         <a href="list.php">ORDER</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>

         <?php
         $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $count_cart_items->execute([$user_id]);
         $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
         $count_wishlist_items->execute([$user_id]);
         ?>
         <a href="wishlist.php">
            <img src="images/heart.png" alt="Heart" style="width:30px; height:30px; vertical-align: top;">
            <span>(<?= $count_wishlist_items->rowCount(); ?>)</span>
         </a>

         <a href="cart.php">
            <img src="images/CART.jpg" alt="Cart" style="width:30px; height:30px; vertical-align: top;">
            <span>(<?= $count_cart_items->rowCount(); ?>)</span></a>
      </div>

      <div class="profile">
         <?php
         $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
         $select_profile->execute([$user_id]);
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="user_profile_update.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>

      </div>

   </div>

</header>