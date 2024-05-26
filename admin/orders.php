<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

try {
    $sql = 'SELECT COUNT(id) as qty_order FROM orders';
    $res = $conn->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        $number_of_result = $row['qty_order'];
        $results_per_page = 5;
        $number_of_page = ceil($number_of_result / $results_per_page);
        $page = 1;
        if (isset($_GET['page'])) {
            $page = intval(sanitize($_GET['page']));
        }
        $count = ($page - 1) * $results_per_page + 1;
        $page_first_result = ($page - 1) * $results_per_page;
        // get categories active
        $sql = 'SELECT orders.id, users.name as cust_name, orders.total_bill, status FROM orders JOIN users ON users.id = orders.user_id LIMIT ?, ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $page_first_result, $results_per_page);
        $stmt->execute();
        $res = $stmt->get_result();
        $orders_data = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    die('Retrieving data from database failed: ' . $e->getMessage());
}
require_once './layout/header.php'
?>
<div class="position-fixed top-0 start-0 admin__menu-btn">
    <i class="fa-solid fa-bars menu-btn__icon"></i>
</div>
<div class="container-fluid px-0">
    <div class="bg-white admin-controls">
        <div id="admin__nav" class="">
            <div class="d-flex align-items-center mb-3 position-relative admin__nav-info">
                <i class="fa-solid fa-user border border-secondary rounded-circle admin__nav-info-icon"></i>
                <h4 class="ms-2 mb-0 text-capitalize admin__nav-info-name"><?= $admin['name'] ?></h4>
                <div class="shadow-sm rounded overflow-hidden admin__sub-menu">
                    <a href="./registration.php">Registration</a>
                    <a href="./logout.php">Logout</a>
                </div>
            </div>
            <ul class="list-unstyled text-capitalize">
                <li class="mb-1 admin__nav-item">
                    <a href="./index.php" class="p-2 rounded admin__nav-item-link">Home</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./orders.php" class="p-2 rounded admin__nav-item-link admin__nav-item-link--active">Order</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./menu.php" class="p-2 rounded admin__nav-item-link">Menu</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./comments.php" class="p-2 rounded admin__nav-item-link">Comments</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./franchising.php" class="p-2 rounded admin__nav-item-link">Franchising</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./categories.php" class="p-2 rounded admin__nav-item-link">Categories</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./products.php" class="p-2 rounded admin__nav-item-link">Products</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./brands.php" class="p-2 rounded admin__nav-item-link">Brands</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./news.php" class="p-2 rounded admin__nav-item-link">News</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="bg-light admin__views">
        <div class="row admin__view-page">
            <div class="col-md-12 mt-4">
                <div class="p-3 bg-white rounded">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3>Order</h3>
                    </div>

                    <div class="table">
                        <div class="table-head">
                            <div class="table-row fw-bold text-uppercase">
                                <div class="table-cell">id</div>
                                <div class="table-cell">Customer name</div>
                                <div class="table-cell">total order amount</div>
                                <div class="table-cell">status</div>
                                <div class="table-cell">action</div>
                            </div>
                        </div>
                        <div class="table-body">
                            <?php
                            foreach ($orders_data as $order) :
                            ?>
                                <div class="table-row">
                                    <div class="table-cell"><?= $count ?></div>
                                    <div class="table-cell"><?= $order['cust_name'] ?></div>
                                    <div class="table-cell">$ <?= $order['total_bill'] ?></div>
                                    <div class="table-cell"><?= ($order['status'] === 1) ? 'complete' : 'unfinished' ?></div>
                                    <div class="table-cell">
                                        <div class="d-flex">
                                            <a href="./order_view.php?id=<?= $order['id'] ?>" class="btn btn-success">Order View</a>
                                            <button class="ms-2 btn btn-danger del-order-btn" data-id="<?= $order['id'] ?>">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            $count++;
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
    </div>
</div>
<?php
require_once './layout/footer.php'
?>