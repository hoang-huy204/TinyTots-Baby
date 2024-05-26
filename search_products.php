<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_GET['value'])) {
    $prod_name = sanitize($_GET['value']);
    $prod_name = '%' . $prod_name . '%';
    $prod_name = $conn->real_escape_string($prod_name);
    try {
        $sql = 'SELECT products.id, products.name, categories.name AS category, products.price, products.description, products.image FROM products INNER JOIN categories ON categories.id = products.category_id WHERE products.name like ? AND products.status = "active"';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $prod_name);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows == 0) {
            $error = 'Product does not exist';
        } else {
            $products_data = $res->fetch_all(MYSQLI_ASSOC);
        }
    } catch (\Exception $e) {
        die('Data fetch failed: ' . $e->getMessage());
    }
} else {
    $error = 'Product does not exist';
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <div id="content-app">
        <div class="container px-0 py-5">
            <?php
            if (isset($error)) :
            ?>
                <div class="alert alert-danger">
                    <h4 class="mb-0"><?= $error ?></h4>
                </div>
            <?php
            else :
            ?>
                <ul class="list-unstyled row mb-3 products__lst">
                    <?php
                    foreach ($products_data as $product) :
                    ?>
                        <li class="col-12 col-md-6 col-xxl-3">
                            <div id="product-<?= $product['id'] ?>" class="products__item" data-id="<?= $product['id'] ?>">
                                <div class="products__item-img" data-img="<?= $product['image'] ?>"></div>
                                <div class="products__item-body">
                                    <p class="products__item-name"><?= $product['name'] ?></p>
                                    <p class="mb-0">Category: <strong><?= $product['category'] ?></strong></p>
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
                                            <button class="w-100 btn-primary buy-btn"  data-id="<?= $product['id'] ?>">Buy</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php
                    endforeach;
                    ?>
                </ul>
            <?php
            endif;
            ?>
        </div>
    </div>
    <?php
    require_once './layout/footer.php';
    ?>