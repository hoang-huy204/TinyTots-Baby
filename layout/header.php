<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
    if (isset($_COOKIE['user'])) {
        $user_account = json_decode($_COOKIE['user'], true);
    } else {
        $user_account = $_SESSION['user'];
    }
}

// echo $user_account['address'];
// die();

if (isset($user_account)) {
    $cart_id = intval($_SESSION['cart_id']);
    try {
        $sql = 'SELECT products.id, products.name, products.price, products.description, products.image, carts_detail.quantity FROM carts_detail JOIN products ON products.id = carts_detail.product_id WHERE cart_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $cart_id);
        $res = $stmt->execute();
        if ($res) {
            $res = $stmt->get_result();
            $cart_data = $res->fetch_all(MYSQLI_ASSOC);
        }
    } catch (\Exception $e) {
        die($e->getMessage());
    }
} else {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $cart_data = $_SESSION['cart'];
}

// dd($cart_data);
// die();

try {
    // get logo
    $sql = 'SELECT * FROM options WHERE name = "logo"';
    $res = $conn->query($sql);
    if ($res) {
        $logo_data = $res->fetch_assoc();
    } else {
        die('Get logo failed');
    }

    // get categories active
    $sql = 'SELECT * FROM categories where status = "active"';
    $res  = $conn->query($sql);
    if ($res) {
        $categories_data = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        die('Get data from table categories failed');
    }

    // get menu
    $sql = 'SELECT * FROM menu';
    $res  = $conn->query($sql);
    if ($res) {
        $menu_data = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        die('Get data from table menu failed');
    }
} catch (Exception $e) {
    die('Retrieving data from database failed: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TinyTots Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" integrity="sha512-NhSC1YmyruXifcj/KFRWoC561YpHpc5Jtzgvbuzx5VozKpWvQ+4nXhPdFgmx8xqexRcpAglTj9sIBWINXa8x5w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/fonts/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" integrity="sha512-t4GWSVZO1eC8BM339Xd7Uphw5s17a86tIZIj8qRxhnKub6WoyhnrxeCIMeAqBPgdZGlCcG2PrZjMc+Wr78+5Xg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/css/base.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/responsive.css">
</head>

<body>
    <header id="header" class="shadow-sm position-fixed top-0 start-0 end-0">
        <div class="container">
            <div class="row d-flex align-items-center justify-content-between header__main">
                <div class="col-sm-6 col-md-3">
                    <a href="./index.php" class="d-flex align-items-center h-100 logo">
                        <img src="./assets/img/<?= $logo_data['value'] ?>" alt="logo" class="py-1 logo-img">
                        <span class="text-dark logo-title">TinyTots</span>
                    </a>
                </div>

                <div class="d-none d-lg-block col-lg-5">
                    <div class="d-flex shadow-sm header__search">
                        <input type="text" name="search" placeholder="Search products" id="header-search" class="ms-3 my-1 p-0 header__search-inp">
                        <div class="px-3 py-2 header__search-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="py-2 px-3 header__btn-icon header__help">
                            <i class="fa-regular fa-circle-question header__help-icon"></i>
                        </div>
                        <a href="./cart.php" class="py-2 px-3 header__btn-icon position-relative header__cart">
                            <i class="ti-shopping-cart header__cart-icon"></i>
                            <span class="position-absolute header__cart-quantity"><?= count($cart_data) ?></span>
                        </a>
                        <?php
                        if (isset($user_account)) :
                        ?>
                            <div class="small ms-2 me-3 position-relative header__account">
                                <p class="mb-0">Hello <strong><?= $user_account['name'] ?></strong></p>
                                <div class="shadow-sm rounded overflow-hidden header__sub-menu">
                                    <a href="./logout.php">Logout</a>
                                </div>
                            </div>
                        <?php
                        else :
                        ?>
                            <div class="small me-3">
                                <a href="./login.php" class="text-black-50">Login</a>
                                <span class="text-black-50">/</span>
                                <a href="./registration.php" class="text-black-50">Register</a>
                            </div>
                        <?php
                        endif;
                        ?>


                    </div>
                </div>
            </div>
            <nav class="d-none d-md-block header__navbar">
                <ul class="list-unstyled mb-0 d-flex align-items-center justify-content-center header__navbar-lst">
                    <?php
                    foreach ($menu_data as $menu) :
                    ?>
                        <li class="position-relative header__navbar-item header__navbar-item--title">
                            <a href="./<?= $menu['url'] ?>" class="d-block text-uppercase text-dark header__navbar-link"><?= $menu['name'] ?></a>
                            <?php
                            if ($menu['name'] === 'Products') :
                            ?>
                                <ul class="list-unstyled position-absolute start-0 shadow-sm bg-white header__navbar-sub">
                                    <?php
                                    foreach ($categories_data as $category) :
                                    ?>
                                        <li class="header__navbar-item header__navbar-item--sub">
                                            <a href="./products.php?category=<?= $category['id'] ?>" class="d-block text-uppercase text-dark header__navbar-link"><?= $category['name'] ?></a>
                                        </li>
                                    <?php
                                    endforeach;
                                    ?>
                                </ul>
                            <?php
                            endif;
                            ?>
                        </li>
                    <?php
                    endforeach;
                    ?>
                </ul>
            </nav>
        </div>
    </header>