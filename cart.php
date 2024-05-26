<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_POST['order'])) {
    $name = sanitize($_POST['name']);
    $fullname = sanitize($_POST['fullname']);
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $name = $conn->real_escape_string($name);
    $fullname = $conn->real_escape_string($fullname);
    $address = $conn->real_escape_string($address);
    $phone = $conn->real_escape_string($phone);

    if (strlen($name) < 3 || strlen($name) > 30) {
        $error = 'Name must be between 3 and 30 characters';
    } elseif (strlen($fullname) < 5 || strlen($fullname) > 100) {
        $error = 'Fullnname must be between 5 and 100 characters';
    } elseif (strlen($address) < 3 || strlen($address) > 200) {
        $error = 'Address must be between 3 or be between 200 characters';
    } elseif (!preg_match('/^(0|\+84)\d{9,10}$/', $phone)) {
        $error = 'Phone invalidate';
    }
    if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
        $cart_id = intval($_SESSION['cart_id']);
        $sql = 'SELECT products.id, products.price, carts_detail.quantity FROM carts_detail JOIN products ON products.id = carts_detail.product_id WHERE carts_detail.cart_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $cart_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $error = 'Shopping cart no products';
        } else {
            $cart_current_data = $res->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        if (count($_SESSION['cart']) === 0) {
            $error = 'Shopping cart no products';
        }
    }

    if (empty($error)) {
        try {
            $conn->begin_transaction();
            if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
                if (isset($_COOKIE['user'])) {
                    $user_account = json_decode($_COOKIE['user'], true);
                } else {
                    $user_account = $_SESSION['user'];
                }
                $user_id = intval($user_account['id']);
                $sql = 'UPDATE users SET address = ?, phone = ? where id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssi', $address, $phone, $user_id);
                $res = $stmt->execute();
                if ($res) {
                    $sql = 'INSERT INTO orders (user_id) VALUES (?)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $user_id);
                    $res = $stmt->execute();
                    if ($res) {
                        $order_id = $conn->insert_id;
                        $total_bill = 0;
                        foreach ($cart_current_data as $cart) {
                            $product_id = intval($cart['id']);
                            $product_price = floatval($cart['price']);
                            $product_qty = intval($cart['quantity']);

                            $sql = 'INSERT INTO orders_detail (order_id, product_id, price, quantity) VALUE (?, ?, ?, ?)';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('iidi', $order_id, $product_id, $product_price, $product_qty);
                            $res = $stmt->execute();
                            if ($res) {
                                $total_bill += $product_price * $product_qty;
                            }
                        }
                        if ($total_bill > 0) {
                            $total_bill = number_format($total_bill, 2, '.', '');
                            $total_bill = floatval($total_bill);
                            $sql = 'UPDATE orders SET total_bill = ? WHERE id = ?';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('di', $total_bill, $order_id);
                            $res = $stmt->execute();
                            if ($res) {
                                $sql = 'DELETE FROM carts_detail WHERE cart_id = ?';
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $cart_id);
                                $res = $stmt->execute();
                                if ($res) {
                                    $successfully = 'Thanks for ordering. We will contact you as soon as possible.';
                                    $conn->commit();
                                }
                            }
                        }
                    }
                }
            } else {
                $sql = 'INSERT INTO users (fullname, name, address, phone, role) VALUE (?, ?, ?, ?, "customer")';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $fullname, $name, $address, $phone);
                $res = $stmt->execute();
                if ($res) {
                    $user_id = $conn->insert_id;
                    $sql  = "INSERT INTO orders (user_id) VALUES ($user_id)";
                    $res = $conn->query($sql);
                    if ($res) {
                        $order_id = $conn->insert_id;
                        $total_bill = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $product_id = intval($item['id']);
                            $product_qty = intval($item['quantity']);
                            $sql = 'SELECT price FROM products WHERE id = ?';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('i', $product_id);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $row = $res->fetch_assoc();
                            $product_price = $row['price'];
                            $product_price = floatval($product_price);

                            $sql = 'INSERT INTO orders_detail (order_id, product_id, price, quantity) VALUE (?, ?, ?, ?)';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('iidi', $order_id, $product_id, $product_price, $product_qty);
                            $res = $stmt->execute();
                            if ($res) {
                                $total_bill += $product_price * $product_qty;
                            }
                        }
                        if ($total_bill > 0) {
                            $total_bill = number_format($total_bill, 2, '.', '');
                            $total_bill = floatval($total_bill);
                            $sql = 'UPDATE orders SET total_bill = ? WHERE id = ?';
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('di', $total_bill, $order_id);
                            $res = $stmt->execute();
                            if ($res) {
                                $successfully = 'Thanks for ordering. We will contact you as soon as possible.';
                                $_SESSION['cart'] = [];
                                $conn->commit();
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die($e->getMessage());
        }
    }
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <section id="content-app" class="py-5">
        <h2 class="text-center text-uppercase mb-3">cart</h2>
        <div class="container">
            <table id="cart" class="mb-0 table table-hover table-condensed">
                <thead>
                    <th width="60%">Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th class="text-center">Into money</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cart_data as $cart) :
                        if (isset($_SESSION['user']) || isset($_COOKIE['user'])) :
                            $product_data = $cart;
                    ?>
                        <?php
                        else :
                            $product_id = intval($cart['id']);
                            try {
                                $sql = 'SELECT * FROM products WHERE id = ?';
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param('i', $product_id);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                if ($res->num_rows === 1) {
                                    $product_data = $res->fetch_assoc();
                                } else {
                                    die('error');
                                }
                            } catch (\Exception $e) {
                                die($e->getMessage());
                            }
                        endif;
                        ?>
                        <tr class="cart__item">
                            <td>
                                <div class="row">
                                    <div class="col-sm-2 hidden-xs"><img src="./assets/img/products/<?= $product_data['image'] ?>" alt="<?= $product_data['image'] ?>" class="w-100 img-responsive">
                                    </div>
                                    <div class="col-sm-10">
                                        <h4 class=""><?= $product_data['name'] ?></h4>
                                        <p class="products__item-desc"><?= $product_data['description'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="cart__product-price">$ <?= $product_data['price'] ?></td>
                            <td>
                                <div class="d-flex">
                                    <!-- <button class="btn btn-dark me-2 product__qty-btn product__qty-btn--decrease">-</button> -->
                                    <input type="number" class="px-3 cart__product-qty-input" value="<?= $cart['quantity'] ?>" step="1" min="1" onkeydown="return false;" data-id="<?= $product_data['id'] ?>">
                                    <!-- <button class="btn btn-dark ms-2 product__qty-btn
                                product__qty-btn--increase">+</button> -->
                                </div>
                            </td>
                            <td class="text-center cart__money"></td>
                            <td class="actions">
                                <div class="cart_close_btn" data-id="<?= $product_data['id'] ?>">
                                    <i class="fa-regular fa-circle-xmark"></i>
                                </div>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><a href="./index.php#list-product" class=""><i class="me-1 fa fa-angle-left"></i>Continue shopping</a>
                        </td>
                        <td colspan="2" class="hidden-xs text-center total-amount-payable"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-4 p-3 bg-white rounded customer-form">
                <div class="d-flex align-items-center justify-content-between">
                    <h3>Customer Information</h3>
                    <?php
                    if (isset($user_account)) :
                    ?>
                    <a href="./purchase_order.php" class="px-3 btn-primary">Purchase order</a>
                    <?php
                    endif;
                    ?>
                </div>
                <?php
                if (isset($error)) :
                ?>
                    <p class="mb-2 alert alert-danger"><?= $error ?></p>
                <?php
                elseif (isset($successfully)) :
                ?>
                    <p class="mb-2 alert alert-success"><?= $successfully ?></p>
                <?php
                endif;
                ?>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
                    <div class="form-group">
                        <label for="name" class="mt-1">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" minlength="3" maxlength="30" placeholder="Enter your name" <?= (isset($user_account)) ? " value=\"{$user_account['name']}\" readonly" : '' ?> required>
                    </div>
                    <div class="mt-2 form-group">
                        <label for="fullname" class="mt-1">Fullname:</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" minlength="5" maxlength="100" placeholder="Enter your fullname" <?= (isset($user_account)) ? " value=\"{$user_account['fullname']}\" readonly" : '' ?> required>
                    </div>
                    <div class="mt-2 form-group">
                        <label for="address" class="mt-1">Address:</label>
                        <input type="text" class="form-control" id="address" name="address" minlength="3" maxlength="200" placeholder="Enter your address" <?= (isset($user_account)) && !is_null($user_account['address']) ? " value=\"{$user_account['address']}\"" : '' ?> required>
                    </div>
                    <div class="mt-2 form-group">
                        <label for="phone" class="mt-1">Phone Number:</label>
                        <input type="tel" class="form-control" id="phone" name="phone" minlength="10" maxlength="14" placeholder="Enter your phone number" <?= (isset($user_account)) ? " value=\"{$user_account['phone']}\"" : '' ?> required>
                    </div>
                    <div class="form-group">
                        <button class="mt-2 btn-primary" name="order">Order</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php
    require_once './layout/footer.php';
    ?>