<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_GET['category'])) {
    $category_id = sanitize($_GET['category']);
    $category_id = $conn->real_escape_string($category_id);
    try {
        $sql = 'SELECT * FROM categories WHERE id = ? AND status = "active"';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $category_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $category_data = $res->fetch_assoc();

            $sql = 'SELECT COUNT(id) as qty_products FROM products WHERE category_id = ? AND status = "active"';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $category_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $number_of_result = $row['qty_products'];
            $results_per_page = 8;
            $number_of_page = ceil($number_of_result / $results_per_page);
            $page = 1;
            if (isset($_GET['page'])) {
                $page = intval(sanitize($_GET['page']));
            }
            $page_first_result = ($page - 1) * $results_per_page;
            $sql = 'SELECT products.id, products.name, products.price, products.description, products.image FROM products WHERE category_id = ? AND status = "active" LIMIT ?, ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iii', $category_id, $page_first_result, $results_per_page);
            $stmt->execute();
            $res = $stmt->get_result();
            $products_data = $res->fetch_all(MYSQLI_ASSOC);
            if (count($products_data) === 0) {
                $error = 'No products';
            }
        }
    } catch (\Exception $e) {
        die('Error getting data from database: ' . $e->getMessage());
    }
} else {
    $error = 'No products';
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <?php
    if (!isset($error)) :
    ?>
        <section id="content-app" class="py-4">
            <div class="products">
                <div class="container">
                    <h4 class="products__category"><?= $category_data['name'] ?></h4>
                    <ul class="list-unstyled row mb-3 products__lst">
                        <?php
                        foreach ($products_data as $product) :
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
                    if ($number_of_page > 1) :
                    ?>
                        <nav aria-label="Page navigation example" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                    <a class="page-link" href="?category=<?= $category_data['id'] ?>&page=<?= ($page - 1) < 1 ? $number_of_page : $page - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php
                                for ($i = 0; $i < $number_of_page; $i++) :
                                ?>
                                    <li class="page-item"><a class="page-link<?= (($i + 1) == $page) ? ' active' : '' ?>" href="?category=<?= $category_data['id'] ?>&page=<?= $i + 1 ?>"><?= $i + 1 ?></a></li>
                                <?php
                                endfor;
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="?category=<?= $category_data['id'] ?>&page=<?= ($page + 1) > $number_of_page ? 1 : $page + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php
                    endif;
                    ?>
                </div>
            </div>
        </section>
    <?php
    else :
    ?>
        <div class="container py-4">
            <div class="alert alert-danger mb-0"><?= $error ?></div>
        </div>
    <?php
    endif;
    ?>
    <?php
    require_once './layout/footer.php';
    ?>