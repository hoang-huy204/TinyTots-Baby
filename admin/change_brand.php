<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_GET['delete'])) {
    $brand_id = intval($_GET['delete']);
    try {
        $sql = 'DELETE FROM brands WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $brand_id);
        $res = $stmt->execute();
        if ($res) {
            header('Location: ./brands.php');
        } else {
            $error = 'delete failed category';
        }
    } catch (\Exception $e) {
        die('Delete category failed ' . $e->getMessage());
    }
}
if (isset($_POST['add-brand'])) {
    $brand_name = sanitize($_POST['name']);
    $brand_image = (isset($_FILES['img'])) ? sanitize($_FILES['img']['name']) : '';
    $brand_image_extension = pathinfo($brand_image, PATHINFO_EXTENSION);
    $brand_image_tmp_name = (isset($_FILES['img'])) ? sanitize($_FILES['img']['tmp_name']) : '';
    $conn->real_escape_string($brand_name);
    $conn->real_escape_string($brand_image);
    $brand_image_folder = '../assets/img/brands/' . $brand_image;
    if (strlen($brand_name) < 4 || strlen($brand_name) > 100) {
        $error = 'Brand name must be more than 4 characters and less than 100 characters';
    } elseif ($brand_image_extension === '' || !($brand_image_extension === 'jpg' || $brand_image_extension === 'jpeg' || $brand_image_extension === 'png')) {
        $error = 'The brand image does not have the correct extension';
    }
    if (!isset($error)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM brands WHERE name = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $brand_name);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $sql = 'INSERT INTO brands(name, image) VALUES (?, ?)';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $brand_name, $brand_image);
                $res = $stmt->execute();
                if ($res) {
                    move_uploaded_file($brand_image_tmp_name, $brand_image_folder);
                    $successfully = 'New brand added successfully';
                    $conn->commit();
                } else {
                    $error = 'Could not add the brand';
                    $conn->rollback();
                }
            } else {
                $error = 'Brand name already exists';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Could not add the brand: ' . $e->getMessage());
        }
    }
}
if (isset($_GET['edit'])) {
    $brand_id = intval($_GET['edit']);
    try {
        $sql = 'SELECT * FROM brands WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $brand_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $brand_data = $res->fetch_assoc();
        } else {
            $error = 'id of brand does not exist';
        }
    } catch (\Exception $e) {
        die('Get data from table brand failed' . $e->getMessage());
    }
}
if (isset($_POST['edit-brand'])) {
    $brand_id = sanitize($_POST['id']);
    $brand_id = intval($brand_id);
    $brand_name = sanitize($_POST['name']);
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $brand_image_old = sanitize($_POST['img-old']);
        $brand_image = sanitize($_FILES['img']['name']);
        $brand_image_extension = pathinfo($brand_image, PATHINFO_EXTENSION);
        $brand_image_tmp_name =  sanitize($_FILES['img']['tmp_name']);
        $brand_image_folder = '../assets/img/brands/' . $brand_image;
        $conn->real_escape_string($brand_image);
    }
    $conn->real_escape_string($brand_name);
    if (strlen($brand_name) < 4 || strlen($brand_name) > 200) {
        $error = 'Category name must be more than 4 characters and less than 200 characters';
    } elseif (isset($brand_image)) {
        if (!($brand_image_extension === 'jpg' || $brand_image_extension === 'jpeg' || $brand_image_extension === 'png')) {
            $error = 'The brand image does not have the correct extension';
        }
    }
    if (empty($error)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM brands WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $brand_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $sql = 'UPDATE brands SET name = ?' . (isset($brand_image) ? ', image = "' . $brand_image . '"' : '') . ' WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $brand_name, $brand_id);
                $res = $stmt->execute();
                if ($res) {
                    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
                        $file_del_path = '../assets/img/brands/' . $brand_image_old;
                        if (file_exists($file_del_path)) {
                            unlink($file_del_path);
                            move_uploaded_file($brand_image_tmp_name, $brand_image_folder);
                            if (file_exists($brand_image_folder)) {
                                $conn->commit();
                                header('location: ./brands.php');
                            } else {
                                $conn->rollback();
                                $error = 'Editing brand failed';
                            }
                        }
                    } else {
                        $conn->commit();
                        header('location: ./brands.php');
                    }
                } else {
                    $conn->rollback();
                    $error = 'Editing brand failed';
                }
            } else {
                $error = 'Id of brand does not exist';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Editing brand failed: ' . $e->getMessage());
        }
    }
}
require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <?php
        if (isset($error)) :
        ?>
            <p class="alert alert-danger fw-500"><?= $error ?></p>
        <?php
        endif;
        if (isset($successfully)) :
        ?>
            <p class="alert alert-success fw-500"><?= $successfully ?></p>
        <?php
        endif;
        ?>
        <form class="p-4 rounded bg-white form" action="./change_brand.php" method="post" enctype="multipart/form-data">
            <div class="d-flex align-items-center justify-content-between">
                <h3><?= (isset($brand_data)) ? 'Edit brand' : 'Add a new brand' ?></h3>
                <a href="./brands.php" class="btn btn-danger lh-100">Back</a>
            </div>
            <?php
            if (isset($brand_data)) :
            ?>
                <input type="text" name="id" hidden value="<?= $brand_data['id'] ?>">
            <?php
            endif;
            ?>
            <label class="form__label" for="brand-name">Name</label>
            <input type="text" name="name" id="brand-name" class="form-control mb-3 form__inp" placeholder="brand name" value="<?= (isset($brand_data)) ? $brand_data['name'] : '' ?>" minlength="4" maxlength="100" required>
            <label class="form__label" for="file-brand-img">Image</label>
            <?php
            if (isset($brand_data)) :
            ?>
                <img src="../assets/img/brands/<?= $brand_data['image'] ?>" alt="brand" class="my-1 admin__old-image">
                <input type="text" name="img-old" hidden value="<?= $brand_data['image'] ?>">
            <?php
            endif;
            ?>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="img" id="file-brand-img" class="form-control mb-3 form__inp">
            <input type="submit" class="btn btn-success w-100 form__btn" name="<?= (isset($brand_data)) ? 'edit-brand' : 'add-brand' ?>" value="<?= (isset($brand_data)) ? 'Edit brand' : 'Add brand' ?>">
        </form>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>