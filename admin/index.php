<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

try {
    // get order quantity
    $sql = 'SELECT COUNT(id) as order_qty FROM orders';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $order_qty = $row['order_qty'];
 
    // get menu quantity
    $sql = 'SELECT COUNT(id) as menu_qty FROM menu';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $menu_qty = $row['menu_qty'];

    // get comment quantity
    $sql = 'SELECT COUNT(id) as comment_qty FROM comments';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $comment_qty = $row['comment_qty'];

    // get franchising quantity
    $sql = 'SELECT COUNT(id) as franchising_qty FROM franchising';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $franchising_qty = $row['franchising_qty'];

    // get category quantity
    $sql = 'SELECT COUNT(id) as category_qty FROM categories WHERE status = "active"';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $category_qty = $row['category_qty'];

    // get product quantity
    $sql = 'SELECT COUNT(id) as product_qty FROM products WHERE status = "active"';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $product_qty = $row['product_qty'];

    // get brand quantity
    $sql = 'SELECT COUNT(id) as brand_qty FROM brands';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $brand_qty = $row['brand_qty'];

    // get new quantity
    $sql = 'SELECT COUNT(id) as new_qty FROM news';
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $new_qty = $row['new_qty'];
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
                    <a href="./index.php" class="p-2 rounded admin__nav-item-link admin__nav-item-link--active">Home</a>
                </li>
                <li class="mb-1 admin__nav-item">
                    <a href="./orders.php" class="p-2 rounded admin__nav-item-link">Order</a>
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
                        <h3>Home</h3>
                    </div>
                    <div class="mt-2 row">
                        <div class="col-3 mt-1">
                            <a href="./orders.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $order_qty ?></span>
                                <b class="d-block text-center">Total order</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./menu.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $menu_qty ?></span>
                                <b class="d-block text-center">Total menu</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./comments.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $comment_qty ?></span>
                                <b class="d-block text-center">Total comment</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./franchising.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $franchising_qty ?></span>
                                <b class="d-block text-center">Total franchising</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./categories.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $category_qty ?></span>
                                <b class="d-block text-center">Total category</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./products.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $product_qty ?></span>
                                <b class="d-block text-center">Total product</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./brands.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $brand_qty ?></span>
                                <b class="d-block text-center">Total brand</b>
                            </a>                            
                        </div>
                        <div class="col-3 mt-1">
                            <a href="./news.php" class="p-5 d-block lh-base btn-primary">
                                <span class="d-block text-center"><?= $new_qty ?></span>
                                <b class="d-block text-center">Total new</b>
                            </a>                            
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