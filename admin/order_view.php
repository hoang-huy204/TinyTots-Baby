<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_GET['remove'])) {
    $order_id = intval($_GET['remove']);
    try {
        $sql = 'DELETE FROM orders_detail WHERE order_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $res = $stmt->execute();
        if ($res) {
            $sql = 'DELETE FROM orders WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $order_id);
            $res = $stmt->execute();
            if ($res) {
                header('Location: ./orders.php');
            }
        }
    } catch (\Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

if (isset($_GET['id'])) {
    try {
        $order_id = intval($_GET['id']);
        if (isset($_GET['check'])) {
            $order_status = intval($_GET['check']);
            if ($order_status === 0) {
                $sql = 'UPDATE orders SET status = 1 WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $order_id);
                $stmt->execute();
            } else {
                $sql = 'UPDATE orders SET status = 0 WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $order_id);
                $stmt->execute();
            }
        }
        $sql = 'SELECT orders.id, users.fullname, users.address, users.phone, orders.total_bill, orders.status  FROM orders JOIN users ON users.id = orders.user_id where orders.id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $order_id);
        $res = $stmt->execute();
        if ($res) {
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $ordering_info_data = $res->fetch_assoc();
                $sql = 'SELECT products.name, orders_detail.price, orders_detail.quantity FROM orders_detail JOIN products ON products.id = orders_detail.product_id WHERE orders_detail.order_id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $order_id);
                $res = $stmt->execute();
                if ($res) {
                    $res = $stmt->get_result();
                    $order_products_data = $res->fetch_all(MYSQLI_ASSOC);
                }
            } else {
                header('Location: ./orders.php');
            }
        }
    } catch (\Exception $e) {
        die('Error: ' . $e->getMessage());
    }
} else {
    header('Location: ./orders.php');
}

require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <div class="p-3 bg-white rounded">
            <div class="d-flex align-items-center justify-content-between">
                <h2>Order detail</h2>
                <a href="./orders.php" class="btn btn-danger">Back</a>
            </div>
            <div class="ms-2">
                <p><span class="me-1 fw-500">Customer name:</span><?= $ordering_info_data['fullname'] ?></p>
                <p><span class="me-1 fw-500">Customer phone:</span><?= $ordering_info_data['phone'] ?></p>
                <p><span class="me-1 fw-500">Customer address:</span><?= $ordering_info_data['address'] ?></p>
                <h6>List product</h6>
                <div class="table">
                    <div class="table-head">
                        <div class="table-row fw-bold text-uppercase">
                            <div class="table-cell">id</div>
                            <div class="table-cell">name</div>
                            <div class="table-cell">price</div>
                            <div class="table-cell">quantity</div>
                        </div>
                    </div>
                    <div class="table-body">
                        <?php
                        $count = 1;
                        foreach ($order_products_data as $product) :
                        ?>
                            <div class="table-row">
                                <div class="table-cell"><?= $count ?></div>
                                <div class="table-cell"><?= $product['name'] ?></div>
                                <div class="table-cell">$ <?= $product['price'] ?></div>
                                <div class="table-cell"><?= $product['quantity'] ?></div>
                            </div>
                        <?php
                            $count++;
                        endforeach;
                        ?>
                    </div>
                </div>
                <p><span class="me-1 fw-500">Total order amount:</span>$ <?= $ordering_info_data['total_bill'] ?></p>
                <p><span class="me-1 fw-500">Status:</span><?= ($ordering_info_data['status'] === 1) ? 'complete' : 'unfinished' ?></p>
                <h6>Action</h6>
                <div>
                    <a href="<?= $_SERVER['PHP_SELF'] . '?id=' . $ordering_info_data['id'] . '&check=' . $ordering_info_data['status'] ?>" class="btn btn-success"><?= ($ordering_info_data['status'] === 1) ? 'Checked' : 'Check' ?></a>
                    <a href="<?= $_SERVER['PHP_SELF'] . '?remove=' . $ordering_info_data['id'] ?>" class="btn btn-danger">Remove</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>