<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

try {
    $sql = 'SELECT * FROM menu';
    $res = $conn->query($sql);
    if ($res) {
        $menu_data = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        die('Error get data from menu');
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
                    <a href="./orders.php" class="p-2 rounded admin__nav-item-link">Order</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./menu.php" class="p-2 rounded admin__nav-item-link admin__nav-item-link--active">Menu</a>
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
                    <h3>Menu</h3>
                    <div class="table">
                        <div class="table-head">
                            <div class="table-row fw-bold text-uppercase">
                                <div class="table-cell">id</div>
                                <div class="table-cell">name</div>
                                <div class="table-cell">action</div>
                            </div>
                        </div>
                        <div class="table-body">
                            <?php
                            $count = 1;
                            foreach ($menu_data as $menu) :
                            ?>
                                <div class="table-row">
                                    <div class="table-cell"><?= $count ?></div>
                                    <div class="table-cell"><?= $menu['name'] ?></div>
                                    <div class="table-cell">
                                        <button class="btn btn-warning edit-menu-btn" data-id="<?= $menu['id'] ?>">Edit</button>
                                    </div>
                                </div>
                            <?php
                            $count++;
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once './layout/footer.php'
?>