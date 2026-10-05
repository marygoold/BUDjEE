<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

$usd_to_php_exchange_rate = 55.33;

if (isset($_POST['order'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    date_default_timezone_set('Asia/Manila');
    $placed_on = date('Y-m-d H:i:s');


    $cart_total_in_php = 0;
    $cart_products_array = [];

    $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $cart_query->execute([$user_id]);
    if ($cart_query->rowCount() > 0) {
        while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
            $item_price_from_db = (float)$cart_item['price'];
            $item_price_for_calculation = $item_price_from_db * $usd_to_php_exchange_rate;

            $sub_total_php = ($item_price_for_calculation * $cart_item['quantity']);
            $cart_total_in_php += $sub_total_php;

            $cart_products_array[] = $cart_item['name'] . ' (₱' . number_format($item_price_for_calculation, 2) . ' x ' . $cart_item['quantity'] . ')';
        };
    };

    $total_products = !empty($cart_products_array) ? implode(', ', $cart_products_array) : '';


    $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND total_products = ? AND total_price = ?");
    $order_query->execute([$name, $total_products, $cart_total_in_php]);

    if ($cart_total_in_php == 0) {
        $message[] = 'Your cart is empty';
    } elseif ($order_query->rowCount() > 0) {
        $message[] = 'Order placed already!';
    } else {
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, total_products, total_price, placed_on) VALUES(?,?,?,?,?)");
        $insert_order->execute([$user_id, $name, $total_products, $cart_total_in_php, $placed_on]);
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);
        $message[] = 'Order placed successfully!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | BUDjEE</title>
    <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <?php include 'header.php'; ?>

    <section class="display-orders">

        <?php
        $cart_grand_total_for_display_php = 0;
        $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $select_cart_items->execute([$user_id]);
        if ($select_cart_items->rowCount() > 0) {
            while ($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
                $item_price_from_db = (float)$fetch_cart_items['price'];
                $item_price_php_for_display = $item_price_from_db;

                $cart_total_price_for_item_php = ($item_price_php_for_display * $fetch_cart_items['quantity']);
                $cart_grand_total_for_display_php += $cart_total_price_for_item_php;
        ?>
                <p>
                    <?= htmlspecialchars($fetch_cart_items['name']); ?>
                    <span>
                        (₱<?= number_format($item_price_php_for_display, 2); ?> x <?= $fetch_cart_items['quantity']; ?>)
                    </span>
                </p>
        <?php
            }
        } else {
            echo '<p class="empty">Your cart is empty!</p>';
        }
        ?>
        <div class="grand-total">
            Total : <span>₱<?= number_format($cart_grand_total_for_display_php, 2); ?></span>
        </div>
    </section>

    <section class="checkout-orders">

        <form action="" method="POST">

            <h3>place your order</h3>

            <div class="flex">
                <div class="inputBox">
                    <span>Name :</span>
                    <input type="text" name="name" placeholder="Enter your name" class="box" required>
                </div>
            </div>

            <input type="submit" name="order" class="btn <?= ($cart_grand_total_for_display_php > 0) ? '' : 'disabled'; ?>" value="place order">
            <a href="grocery.php" class="btn">Continue Shopping</a>

        </form>

    </section>

    <?php include 'footer.php'; ?>

    <script src="js/script.js"></script>

</body>

</html>