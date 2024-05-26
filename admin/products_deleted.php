<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

try {
    $sql = 'SELECT COUNT(id) as qty_product FROM products WHERE status = "deleted"';
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        $number_of_result = $row['qty_product'];
        $results_per_page = 5;
        $number_of_page = ceil($number_of_result / $results_per_page);
        $page = 1;
        if (isset($_GET['page'])) {
            $page = intval(sanitize($_GET['page']));
        }
        $page_first_result = ($page - 1) * $results_per_page;
        // get categories deleted
        $sql = 'SELECT products.id, products.name, products.price, products.description, products.image, categories.name as category FROM products INNER JOIN categories ON categories.id = products.category_id where products.status = "deleted" ORDER BY products.id LIMIT ?, ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $page_first_result, $results_per_page);
        $stmt->execute();
        $res = $stmt->get_result();
        $lst_product_deleted = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    die('Retrieving data from database failed: ' . $e->getMessage());
}

require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <div class="p-4 rounded bg-white overflow-hidden">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Deleted products</h4>
                <a href="./products.php" class="btn btn-success">Products</a>
            </div>
            <div class="table">
                <div class="table-head">
                    <div class="table-row fw-bold text-uppercase">
                        <div class="table-cell">id</div>
                        <div class="table-cell">name</div>
                        <div class="table-cell">category</div>
                        <div class="table-cell">price</div>
                        <div class="table-cell">description</div>
                        <div class="table-cell">image</div>
                        <div class="table-cell">action</div>
                    </div>
                </div>
                <div class="table-body">
                    <?php
                    foreach ($lst_product_deleted as $product) :
                    ?>
                        <div class="table-row">
                            <div class="table-cell"><?= $product['id'] ?></div>
                            <div class="table-cell"><?= $product['name'] ?></div>
                            <div class="table-cell"><?= $product['category'] ?></div>
                            <div class="table-cell"><?= $product['price'] ?></div>
                            <div class="table-cell">
                                <p class="table-desc"><?= $product['description'] ?></p>
                            </div>
                            <div class="table-cell">
                                <img class="table-img" src="../assets/img/products/<?= $product['image'] ?>" alt="product">
                            </div>
                            <div class="table-cell">
                                <div class="d-flex">
                                    <button class="btn btn-success restore-product-btn" data-id="<?= $product['id'] ?>">Restore</button>
                                </div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
            </div>
            <?php
            if ($number_of_page > 1) :
            ?>
                <nav aria-label="Page navigation example" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($page - 1) < 1 ? $number_of_page : $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php
                        for ($i = 0; $i < $number_of_page; $i++) :
                        ?>
                            <li class="page-item"><a class="page-link<?= (($i + 1) == $page) ? ' border border-light text-white bg-danger' : '' ?>" href="?page=<?= $i + 1 ?>"><?= $i + 1 ?></a></li>
                        <?php
                        endfor;
                        ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($page + 1) > $number_of_page ? 1 : $page + 1 ?>" aria-label="Next">
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
</div>
<?php
require_once './layout/footer.php';
?>