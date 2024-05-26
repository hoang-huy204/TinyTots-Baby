<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

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
        }
    } catch (\Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

require_once './layout/header.php';
$user_id = intval($user_account['id']);
try {
    $sql = 'SELECT * FROM orders WHERE user_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $res = $stmt->execute();
    if ($res) {
        $res = $stmt->get_result();
        $orders_data = $res->fetch_all(MYSQLI_ASSOC);
    }
} catch (\Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<div id="contents-with-footer">
    <section id="content-app" class="py-5">
        <div class="container bg-white rounded">
            <a href="./cart.php" class="px-3 my-2 btn-primary">Back</a>
            <div class="table">
                <div class="table-head">
                    <div class="table-row fw-bold text-uppercase">
                        <div class="table-cell">id</div>
                        <div class="table-cell">total bill</div>
                        <div class="table-cell">status</div>
                        <div class="table-cell">action</div>
                    </div>
                </div>
                <div class="table-body">
                    <?php
                    $count = 1;
                    foreach ($orders_data as $order) :
                    ?>
                        <div class="table-row">
                            <div class="table-cell"><?= $count ?></div>
                            <div class="table-cell">$ <?= $order['total_bill'] ?></div>
                            <div class="table-cell">
                                <p class="table-desc"><?= ($order['status'] === 1) ? 'complete' : 'unfinished' ?></p>
                            </div>
                            <div class="table-cell">
                                <?php
                                if ($order['status'] === 0):
                                ?>
                                <a href="<?= $_SERVER['PHP_SELF'] . '?remove=' . $order['id'] ?>" class="text-danger">
                                    <i class="fa-regular fa-circle-xmark"></i>
                                </a>
                                <?php
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php
                        $count++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once './layout/footer.php';
    ?>