<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

$usd_to_php = 55.33;

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$delete_id]);
    header('location:cart.php');
    exit();
}

if (isset($_GET['delete_all'])) {
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    header('location:cart.php');
    exit();
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $p_qty = $_POST['p_qty'];
    $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$p_qty, $cart_id]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | BUDjEE</title>
    <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'header.php'; ?>

    <section class="shopping-cart">

        <h1 class="title">Products Added</h1>

        <div class="box-container">

            <?php
            $grand_total = 0;
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $price_usd_item = $fetch_cart['price'];
                    $price_php_item = $price_usd_item * $usd_to_php;

                    $sub_total_usd = $price_usd_item * $fetch_cart['quantity'];
                    $sub_total_php = $sub_total_usd;
                    $grand_total += $sub_total_php;
            ?>
                    <form action="" method="POST" class="box">
                        <button type="button" class="fas fa-times" onclick="confirmAction(<?= $fetch_cart['id']; ?>, 'delete-cart')"></button>
                        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                        <div class="name"><?= $fetch_cart['name']; ?></div>
                        <div class="price">₱<?= number_format($price_usd_item, 2); ?></div>
                        <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                        <div class="flex-btn">
                            <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
                            <input type="submit" value="update" name="update_qty" class="option-btn">
                        </div>
                        <div class="sub-total">Subtotal : <span>₱<?= number_format($sub_total_php, 2); ?></span></div>
                    </form>
            <?php
                }
            } else {
                echo '<p class="empty">your cart is empty</p>';
            }
            ?>
        </div>

        <div class="cart-total">
            <p>Grand Total : <span>₱<?= number_format($grand_total, 2); ?></span></p>
            <a href="grocery.php" class="option-btn">Continue Shopping</a>
            <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Delete all</a>
            <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Add to Order</a>
        </div>

    </section>

    <?php include 'footer.php'; ?>

    <div id="deleteModal" class="modal">
        <div class="modal-box">
            <p id="modalMessage">Are you sure?</p>
            <div class="modal-actions">
                <a id="confirmActionLink" href="#" class="yes-btn" style="display: none;">Yes</a>
                <button type="button" id="confirmActionButton" class="yes-btn" style="display: none;">Yes</button>
                <button type="button" onclick="closeModal()" class="no-btn">No</button>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>

</body>

</html>