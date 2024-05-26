<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

try {
    // get slides
    $sql = 'SELECT * FROM options WHERE name = "slides"';
    $res = $conn->query($sql);
    if ($res) {
        $slides_data = $res->fetch_assoc();
        $slides_img_arr = explode(', ', $slides_data['value']);
    }

    // get brands
    $sql = 'SELECT * FROM brands';
    $res = $conn->query($sql);
    if ($res) {
        $brands_data = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        die('Get data from table brands failed');
    }
} catch (Exception $e) {
    die('Retrieving data from database failed: ' . $e->getMessage());
}

if (isset($_POST['send-comments'])) {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);
    $fullname = $conn->real_escape_string($fullname);
    $email = $conn->real_escape_string($email);
    $message = $conn->real_escape_string($message);
    if (strlen($fullname) < 5 || strlen($fullname) > 60) {
        $error = 'Fullname must be greater than 5 characters and less than 60 characters';
    } elseif (!(preg_match('/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/', $email) === 1)) {
        $error = 'Email invalidate';
    } elseif (strlen($message) < 5 || strlen($message) > 500) {
        $error = 'Message must be greater than 5 characters and less than 500 characters';
    }

    if (!isset($error)) {
        try {
            $sql = 'INSERT INTO comments (fullname, email, message) VALUE (?, ?, ?)';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $fullname, $email, $message);
            $res = $stmt->execute();
            if ($res) {
                $successfully = 'Comment successfully';
            } else {
                $error = 'Comment error';
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <section id="content-app">
        <div class="container-fluid px-0">
            <div class="owl-carousel owl-theme mb-4 slider">
                <?php
                foreach ($slides_img_arr as $slide_img):
                ?>
                <div class="item slider__img-box" data-image="<?= $slide_img ?>"></div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
        <div id="list-product">
            <?php
            foreach ($categories_data as $category) :
                try {
                    $sql = 'SELECT products.id, products.name, products.price, products.description, products.image FROM products WHERE category_id = ? AND status = "active" order by rand() LIMIT 4';
                    $stmt = $conn->prepare($sql);
                    $id_cetegory = intval($category['id']);
                    $stmt->bind_param('i', $id_cetegory);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $lst_products = $res->fetch_all(MYSQLI_ASSOC);
                } catch (\Exception $e) {
                    die("Error: " . $e->getMessage());
                }
                if (count($lst_products) > 0) :
            ?>
                    <div class="products">
                        <div class="container">
                            <h4 class="products__category"><?= $category['name'] ?></h4>
                            <ul class="list-unstyled row products__lst">
                                <?php
                                foreach ($lst_products as $product) :
                                ?>
                                    <li class="col-12 col-md-6 col-xxl-3">
                                        <div id="product-<?= $product['id'] ?>" class="products__item" data-id="<?= $product['id'] ?>">
                                            <div class="products__item-img" data-img="<?= $product['image'] ?>"></div>
                                            <div class="products__item-body">
                                                <p class="products__item-name"><?= $product['name'] ?></p>
                                                <?php
                                                if (strlen($product['description']) !== 0) :
                                                ?>
                                                    <p class="text-justify products__item-desc"><?= $product['description'] ?></p>
                                                    <p class="products__item-price">$ <?= $product['price'] ?></p>
                                                <?php
                                                else :
                                                ?>
                                                    <p class="products__item-price">$ <?= $product['price'] ?></p>
                                                    <p class="text-justify products__item-desc"><?= $product['description'] ?></p>
                                                <?php
                                                endif;
                                                ?>
                                                <div class="row btns-primary">
                                                    <div class="col-12 col-md-6">
                                                        <button class="w-100 btn-primary btn-cart <?php
                                                                                                    $prod_added_cart = false;
                                                                                                    if (isset($user_account)) {
                                                                                                        foreach ($cart_data as $cart) {
                                                                                                            if ($cart['id'] == $product['id']) {
                                                                                                                $prod_added_cart = true;
                                                                                                                echo 'active';
                                                                                                            }
                                                                                                        }
                                                                                                    } else {
                                                                                                        $stop_foreach = false;
                                                                                                        foreach ($cart_data as $item) {
                                                                                                            foreach ($item as $key => $value) {
                                                                                                                if ($key === 'id' && $value === $product['id']) {
                                                                                                                    $prod_added_cart = true;
                                                                                                                    $stop_foreach = true;
                                                                                                                    echo 'active';
                                                                                                                    break;
                                                                                                                }
                                                                                                            }
                                                                                                            if ($stop_foreach) {
                                                                                                                break;
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    ?>" data-id="<?= $product['id'] ?>">
                                                            <?= ($prod_added_cart === true) ? 'Remove to cart' : 'Add to cart' ?>
                                                        </button>
                                                    </div>
                                                    <div class="mt-2 mt-md-0 col-12 col-md-6">
                                                        <button class="w-100 btn-primary buy-btn" data-id="<?= $product['id'] ?>">Buy</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php
                                endforeach;
                                ?>
                            </ul>
                            <div class="d-flex justify-content-center">
                                <a href="./products.php?category=<?= $category['id'] ?>" class="btn btn-dark products__link">View all</a>
                            </div>
                        </div>
                    </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
        <div id="brands" class="bg-white py-4 mt-5">
            <div class="container brands-container">
                <h2 class="text-center text-uppercase content__heading">Brands</h2>
                <div class="row justify-content-center brands">
                    <?php
                    foreach ($brands_data as $brand) :
                    ?>
                        <div class="col-xl-2 col-md-3">
                            <div class="h-100 d-flex align-items-center brands__item">
                                <img src="./assets/img/brands/<?= $brand['image'] ?>" alt="<?= $brand['name'] ?>" class="w-100">
                                <div class="brands__item-overlay"></div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
        <div id="about-us__view" class="py-4 bg-light">
            <div class="container content-section">
                <h2 class="text-center text-uppercase content__heading">Contact us</h2>
                <div class="row about-us__view-content">
                    <div class="col-md-6 about-us__view-info">
                        <p class="mb-1"><i class="ti-location-pin about-us__view-icon"></i>New York, US</p>
                        <p class="mb-1"><i class="ti-mobile about-us__view-icon"></i>Phone: <a href="tell:+01 151515">+01 151515</a></p>
                        <p class="mb-1"><i class="ti-email about-us__view-icon"></i>Email: <a href="mailto:tinytots@gmail.com">tinytots@gmail.com</a></p>
                    </div>
                    <div class="col-md-6">
                        <?php
                        if (isset($error)) :
                        ?>
                            <p class="mt-3 alert alert-danger"><?= $error ?></p>
                        <?php
                        endif;

                        if (isset($successfully)) :
                        ?>
                            <p class="mt-3 alert alert-success"><?= $successfully ?></p>
                        <?php
                        endif;
                        ?>
                        <form method="post" action="<?= $_SERVER['PHP_SELF'] . '#about-us__view' ?>">
                            <div class="row about-us__view-form">
                                <div class="col-md-6 px-1">
                                    <input type="text" name="fullname" placeholder="Fullname" minlength="5" maxlength="60" required class="form-control">
                                </div>
                                <div class="col-md-6 px-1">
                                    <input type="email" name="email" placeholder="Email" minlength="5" maxlength="500" required class="form-control">
                                </div>

                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="text" name="message" placeholder="Message" required class="form-control">
                                </div>
                            </div>
                            <button class="mt-3 d-block ms-auto btn btn-dark text-uppercase" name="send-comments">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="map">
            <iframe class="w-100" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d16732.548875903733!2d-74.11248688433929!3d40.566119218506444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2495ae5156655%3A0xbd73fce2fd1acd0d!2sStop%20%26%20Shop!5e0!3m2!1svi!2s!4v1686886117226!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>
    <?php
    require_once './layout/footer.php';
    ?>