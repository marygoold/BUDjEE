<?php

use Dom\Text;

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ? AND user_id = ?");
    $delete_order->execute([$delete_id, $user_id]);
    header('location:orders.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | BUDjEE</title>
    <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/orders.css">

</head>

<body>
    <div class="orders-wrapper">
        <?php include 'header.php'; ?>

        <section class="placed-orders">

            <h1 class="title">ORDERS</h1>

            <div class="box-container">

                <?php
                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
                $select_orders->execute([$user_id]);
                if ($select_orders->rowCount() > 0) {
                    while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                ?>

                        <div class="box" data-order-id="<?= $fetch_orders['id']; ?>">
                            <p> Name : <span><?= htmlspecialchars($fetch_orders['name'] ?? 'N/A'); ?></span></p>
                            <p> Placed On: <span>
                                    <?php
                                    echo $fetch_orders['placed_on'] ?? 'Date N/A';
                                    ?>
                                </span> </p>
                            <p> Your Grocery : <br>
                                <span class="grocery-details">
                                    <?php
                                    $products_string = $fetch_orders['total_products'];
                                    $products_array = preg_split('/,\s*(?![^()]*\))/', $products_string);

                                    $item_count = 0;

                                    foreach ($products_array as $product_item) {
                                        if ($item_count > 0) {
                                            echo '<br>';
                                        }

                                        if (preg_match('/^(.*?)\s+\(₱([\d\.,]+)\s+x\s+(\d+)\)$/', trim($product_item), $matches)) {
                                            $product_name = trim($matches[1]);
                                            $price_per_unit = floatval(str_replace(',', '', $matches[2]));
                                            $quantity = intval($matches[3]);

                                            echo htmlspecialchars($quantity) . ' ' . htmlspecialchars($product_name) . ' - ₱' . number_format($price_per_unit, 2);
                                        } elseif (preg_match('/^(.*?)\s*-\s*₱([\d\.,]+)$/', trim($product_item), $matches_alt)) {
                                            $product_name_alt = trim($matches_alt[1]);
                                            $price_alt = floatval(str_replace(',', '', $matches_alt[2]));
                                            echo '1 ' . htmlspecialchars($product_name_alt) . ' - ₱' . number_format($price_alt, 2);
                                        } else {
                                            echo htmlspecialchars(trim($product_item));
                                        }
                                        $item_count++;
                                    }
                                    ?>
                                </span>
                            </p>
                            <?php
                            $cleaned_total_price = str_replace('/-', '', $fetch_orders['total_price']);
                            $numeric_total_price = (float)$cleaned_total_price;
                            ?>
                            <p> Total Price: <span>₱<?= number_format($numeric_total_price, 2); ?></span> </p>

                            <button class="btn" onclick="confirmAction(<?= $fetch_orders['id']; ?>, 'print-order', this);">Print Order</button>
                            <a href="#" class="delete-btn" onclick="event.preventDefault(); confirmAction(<?= $fetch_orders['id']; ?>, 'delete-order');">Delete</a>

                        </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">No orders placed yet!</p>';
                }
                ?>

            </div>

        </section>

        <?php include 'footer.php'; ?>
    </div>
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
    <script>
        document.querySelectorAll('.placed-orders .box-container .box').forEach(box => {
            box.style.height = '10rem';
            box.style.overflow = 'hidden';

            box.addEventListener('click', e => {
                if (e.target.closest('button, a')) return;

                if (box.classList.contains('expanded')) {
                    box.style.overflow = 'hidden';
                    box.style.height = '10rem';
                    box.classList.remove('expanded');
                } else {
                    box.style.height = box.scrollHeight + 'px';
                    box.classList.add('expanded');
                    box.addEventListener('transitionend', function handler(event) {
                        if (event.propertyName === 'height' && box.classList.contains('expanded')) {
                            box.style.overflow = 'visible';
                            box.removeEventListener('transitionend', handler);
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>