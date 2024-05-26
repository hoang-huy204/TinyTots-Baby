<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_GET['delete'])) {
    $id_cate = intval($_GET['delete']);
    try {
        $conn->begin_transaction();
        $sql = 'SELECT * FROM categories WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cate);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $sql = 'UPDATE products SET status = "deleted" WHERE category_id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_cate);
            $res = $stmt->execute();
            if ($res) {
                $sql = 'UPDATE categories SET status = "deleted" WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $id_cate);
                $res = $stmt->execute();
                if ($res) {
                    $conn->commit();
                    header('Location: ./categories.php');
                } else {
                    $conn->rollback();
                    $errors[] = 'delete failed category';
                }
            } else {
                $conn->rollback();
                $errors[] = 'delete failed product';
            }
        } else {
            $errors[] = 'category delete failed because the category id doesn\'t exist';
        }
    } catch (\Exception $e) {
        $conn->rollback();
        die('Delete category failed ' . $e->getMessage());
    }
}
if (isset($_GET['restore'])) {
    $id_cate = intval($_GET['restore']);
    try {
        $sql = 'UPDATE categories SET status = "active" WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cate);
        $res = $stmt->execute();
        if ($res) {
            header('Location: ./categories.php');
        } else {
            $errors[] = 'Restore category failed';
        }
    } catch (\Exception $e) {
        die('Restore category failed ' . $e->getMessage());
    }
}
if (isset($_POST['add-cate'])) {
    $name_cate = sanitize($_POST['name']);
    $name_cate = strtolower($name_cate);
    $name_cate = $conn->real_escape_string($name_cate);
    if (strlen($name_cate) < 3 || strlen($name_cate) > 50) {
        $errors[] = 'Category must be greater than 3 characters and less than 50 characters';
    }
    if (empty($errors)) {
        try {
            $sql = 'SELECT * FROM category WHERE name = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $name_cate);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $sql = 'INSERT INTO categories (name) VALUE (?)';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $name_cate);
                $res = $stmt->execute();
                if ($res) {
                    $successfully = 'Added category successfully';
                } else {
                    $errors[] = 'adding category failed';
                }
            } else {
                $errors[] = 'Category name already exists';
            }
        } catch (\Exception $e) {
            die('adding category failed ' . $e->getMessage());
        }
    }
}
if (isset($_GET['edit'])) {
    $id_cate = intval($_GET['edit']);
    try {
        $sql = 'SELECT * FROM categories WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_cate);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $data_cate = $res->fetch_assoc();
        } else {
            $errors[] = 'id of category does not exist';
        }
    } catch (\Exception $e) {
        die('Get data from table categories failed' . $e->getMessage());
    }
}
if (isset($_POST['edit-cate'])) {
    $id_cate = sanitize($_POST['id']);
    $id_cate = intval($id_cate);
    $name_cate = sanitize($_POST['name']);
    $name_cate = strtolower($name_cate);
    $id_cate = $conn->real_escape_string($id_cate);
    $name_cate = $conn->real_escape_string($name_cate);
    if (strlen($name_cate) < 3 || strlen($name_cate) > 50) {
        $errors[] = 'Category must be greater than 3 characters and less than 50 characters';
    }
    if (empty($errors)) {
        try {
            $sql = 'SELECT * FROM categories WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_cate);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $sql = 'SELECT * FROM categories WHERE name = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $name_cate);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows === 0) {
                    $sql = 'UPDATE categories SET name = ? WHERE id = ?';
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('si', $name_cate, $id_cate);
                    $res = $stmt->execute();
                    if ($res) {
                        header('location: ./categories.php');
                    } else {
                        $errors[] = 'Editing category failed';
                    }
                } else {
                    $errors[] = 'Category name already exists';
                }
            } else {
                $errors[] = 'Id of category does not exist';
            }
        } catch (\Exception $e) {
            die('Editing category failed ' . $e->getMessage());
        }
    }
}
require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <?php
        if (!empty($errors)) {
            echo '<ul class="list-unstyled alert alert-danger px-2 fw-500">';
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo '</ul>';
        }
        if (!empty($successfully)) :
        ?>
            <p class="alert alert-success fw-500"><?= $successfully ?></p>
        <?php
        endif;
        ?>
        <div class="p-4 rounded bg-white overflow-hidden">
            <form class="admin__form" action="./change_category.php" method="post">
                <div class="d-flex justify-content-between">
                    <h3><?= (!empty($data_cate)) ? 'Edit categorty' : 'Add a new category' ?></h3>
                    <div>
                        <a href="./categories.php" class="btn btn-danger lh-100">Categories view</a>
                        <a href="./change_product.php" class="btn btn-success lh-100">Add product</a>
                    </div>
                </div>
                <?php
                if (!empty($data_cate)) :
                ?>
                    <input type="text" name="id" hidden value="<?= $data_cate['id'] ?>">
                <?php
                endif;
                ?>
                <label class="form__label" for="cate-name">Name</label>
                <input type="text" name="name" id="cate-name" class="form-control mb-3" placeholder="category name" value="<?= (!empty($data_cate)) ? $data_cate['name'] : '' ?>" minlength="3" maxlength="50" require>
                <input type="submit" class="btn btn-success w-100 form__btn" name="<?= (!empty($data_cate)) ? 'edit-cate' : 'add-cate' ?>" value="<?= (!empty($data_cate)) ? 'Edit category' : 'Add category' ?>">
            </form>
        </div>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>