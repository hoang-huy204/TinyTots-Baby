<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_GET['id'])) {
    $prod_id = sanitize($_GET['id']);
    $prod_id = intval($prod_id);
    $prod_id = $conn->real_escape_string($prod_id);
    try {
        $sql = 'SELECT * FROM products WHERE id = ? AND status = "active"';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $prod_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows == 0) {
            $error = 'Product id does not exist';
        } else {
            $data = $res->fetch_assoc();
        }
    } catch (\Exception $e) {
        die('Data fetch failed: ' . $e->getMessage());
    }
} else {
    $error = 'Product id does not exist';
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <div id="content-app">
        <div class="container px-0 py-5">

            <?php
            if (!isset($error)) :
            ?>
                <div class="bg-white rounded row mx-0 product-detail">
                    <div class="col-md-4 ps-0">
                        <div class="product-detail__img" data-img="<?= $data['image'] ?>"></div>
                    </div>
                    <div class="col-md-8 pe-0">
                        <div class="product-detail__information">
                            <div class="row align-items-center justify-content-between">
                                <h2 class="col-10 mb-0 product-detail__heading"><?= $data['name'] ?></h2>
                                <div class="col-1">
                                    <a href="./index.php#product-<?= $data['id'] ?>" class="d-block text-center bg-primary-1 btn-primary">Back</a>
                                </div>

                            </div>
                            <p class="col-3 mb-1 product-detail__price">$ <?= $data['price'] ?></p>
                            <div class="product-detail__desc">
                                <div class="d-flex mb-2 product-detail__reviews">
                                    <div class="product__star">
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <span class="ms-2 product-detail__reviews-quantity">(<?= $data['reviewers'] ?> reviews)</span>
                                </div>
                                <p class="product-detail__reviews-text"><?= $data['description'] ?></p>
                                <div class="buttons">
                                    <div class="d-flex align-items-center mb-4 product-detail__quantity">
                                        <button class="btn btn-dark me-2 product__qty-btn product__qty-btn--decrease">-</button>
                                        <input type="number" class="px-3 product__qty-input" value="1" step="1" min="1" onkeydown="return false;">
                                        <button class="btn btn-dark ms-2 product__qty-btn
                                product__qty-btn--increase">+</button>
                                    </div>
                                    <div class="btns-primary">
                                        <button class="px-3 me-3 btn-primary btn-cart <?php
                                                                                        $prod_added_cart = false;
                                                                                        if (isset($user_account)) {
                                                                                            foreach ($cart_data as $cart) {
                                                                                                if ($cart['id'] == $data['id']) {
                                                                                                    $prod_added_cart = true;
                                                                                                    echo 'active';
                                                                                                }
                                                                                            }
                                                                                        } else {
                                                                                            $stop_foreach = false;
                                                                                            foreach ($cart_data as $item) {
                                                                                                foreach ($item as $key => $value) {
                                                                                                    if ($key === 'id' && $value === $data['id']) {
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
                                                                                        ?>" data-id="<?= $data['id'] ?>">
                                            <?= ($prod_added_cart === true) ? 'Remove to cart' : 'Add to cart' ?>
                                        </button>
                                        <button class="px-3 btn-primary buy-btn"  data-id="<?= $data['id'] ?>">Buy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 px-0 mt-4">
                        <h2 class="mb-3 product-detail__heading">Detailed description</h2>
                        <p class="mb-0 text-body"><?= $data['description'] ?></p>
                    </div>
                </div>
            <?php
            else :
            ?>
                <div class="alert alert-danger">
                    <h4 class="mb-0"><?= $error ?></h4>
                </div>
            <?php
            endif;
            ?>
        </div>
    </div>
    <?php
    require_once './layout/footer.php';
    ?>