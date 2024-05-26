<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_GET['edit'])) {
    $menu_id = intval($_GET['edit']);
    try {
        $sql = 'SELECT * FROM menu WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $menu_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $menu_data = $res->fetch_assoc();
        } else {
            header('location: ./menu.php');
        }
    } catch (\Exception $e) {
        die('Get data from table menu failed' . $e->getMessage());
    }
}
if (isset($_POST['menu_edit'])) {
    $menu_id = sanitize($_POST['id']);
    $menu_id = intval($menu_id);
    $menu_name = sanitize($_POST['name']);
    $menu_name = strtolower($menu_name);
    $menu_id = $conn->real_escape_string($menu_id);
    $menu_name = $conn->real_escape_string($menu_name);
    if (strlen($menu_name) < 3 || strlen($menu_name) > 100) {
        $error = 'Category must be greater than 3 characters and less than 100 characters';
    }
    if (empty($errors)) {
        try {
            $sql = 'UPDATE menu SET name = ? WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $menu_name, $menu_id);
            $res = $stmt->execute();
            if ($res) {
                header('location: ./menu.php');
            }
        } catch (\Exception $e) {
            die('Editing category failed ' . $e->getMessage());
        }
    }
}

if (!(isset($_POST['edit-cate']) || isset($_GET['edit']))) {
    header('Locahost: ./menu.php');
}

require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <?php
        if (isset($error)):
        ?>
        <p class="alert alert-danger mb-3">$error</p>
        <?php
        endif;
        ?>
        <div class="p-4 rounded bg-white overflow-hidden">
            <form class="admin__form" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
                <div class="d-flex align-items-center justify-content-between">
                    <h3>Edit menu</h3>
                    <a href="./menu.php" class="btn btn-danger">Back</a>
                </div>
                <input type="text" name="id" hidden value="<?= $menu_data['id'] ?>">
                <label class="form__label" for="menu-name">Name</label>
                <input type="text" name="name" id="menu-name" class="form-control mb-3" placeholder="Menu name" value="<?= $menu_data['name'] ?>" minlength="3" maxlength="100" require>
                <input type="submit" class="btn btn-success w-100 form__btn" name="menu_edit" value="Menu edit">
            </form>
        </div>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>