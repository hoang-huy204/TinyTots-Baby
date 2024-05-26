<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_GET['id'])) {
    $new_id = sanitize($_GET['id']);
    $new_id = intval($new_id);
    $new_id = $conn->real_escape_string($new_id);
    try {
        $sql = 'SELECT * FROM news WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $new_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows == 0) {
            $error = 'New id does not exist';
        } else {
            $data = $res->fetch_assoc();
        }
    } catch (\Exception $e) {
        die('Data fetch failed: ' . $e->getMessage());
    }
} else {
    $error = 'New id does not exist';
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <div id="content-app">
        <div class="container px-0 py-5">
        <?php
            if (!isset($error)) :
            ?>
            <div class="rounded bg-white news">
                <div class="news__item-img news__item-img--details" data-img="<?= $data['image'] ?>"></div>
                <div class="p-4 news__item-body">
                    <h4 class="news__item-heading-details"><?= $data['name'] ?></h4>
                    <p class="news__item-desc-detail"><?= $data['description'] ?></p>
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