<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

try {
    // get product categories
    $sql = 'SELECT * FROM categories';
    $res  = $conn->query($sql);
    if ($res) {
        $category_arr = $res->fetch_all(MYSQLI_ASSOC);
    } else {
        die('Get data from table categories failed');
    }
} catch (\Exception $e) {
    die('Retrieving data from database failed: ' . $e->getMessage());
};
if (isset($_GET['delete'])) {
    $prod_id = intval($_GET['delete']);
    try {
        $sql = 'UPDATE products SET status = "deleted" WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $prod_id);
        $res = $stmt->execute();
        if ($res) {
            header('Location: ./products.php');
        } else {
            $errors[] = 'delete failed category';
        }
    } catch (\Exception $e) {
        die('Delete category failed ' . $e->getMessage());
    }
}
if (isset($_GET['restore'])) {
    $prod_id = intval($_GET['restore']);
    try {
        $sql = 'UPDATE products SET status = "active" WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $prod_id);
        $res = $stmt->execute();
        if ($res) {
            header('Location: ./products.php');
        } else {
            $errors[] = 'Restore category failed';
        }
    } catch (\Exception $e) {
        die('Restore category failed ' . $e->getMessage());
    }
}
if (isset($_POST['add-prod'])) {
    $prod_name = sanitize($_POST['name']);
    $prod_cate = sanitize($_POST['cate']);
    $prod_cate = (int)$prod_cate;
    $prod_price = sanitize($_POST['price']);
    $prod_price = floatval($prod_price);
    $prod_description = sanitize($_POST['description']);
    $prod_image = (isset($_FILES['img'])) ? sanitize($_FILES['img']['name']) : '';
    $prod_image_extension = pathinfo($prod_image, PATHINFO_EXTENSION);
    $prod_image_tmp_name = (isset($_FILES['img'])) ? sanitize($_FILES['img']['tmp_name']) : '';
    $conn->real_escape_string($prod_name);
    $conn->real_escape_string($prod_cate);
    $conn->real_escape_string($prod_price);
    $conn->real_escape_string($prod_description);
    $conn->real_escape_string($prod_image);
    $prod_image_folder = '../assets/img/products/' . $prod_image;
    if (strlen($prod_name) < 5 || strlen($prod_name) > 200) {
        $errors[] = 'Product name must be more than 5 characters and less than 200 characters';
    } elseif (empty($prod_price)) {
        $errors[] = 'Product price is not in the correct format';
    } elseif (!empty($prod_description)) {
        if (strlen($prod_description) < 5 || strlen($prod_description) > 800) {
            $errors[] = 'Product description must be more than 5 characters and less than 800 characters';
        }
    } elseif ($prod_image_extension === '' || !($prod_image_extension === 'jpg' || $prod_image_extension === 'jpeg' || $prod_image_extension === 'png')) {
        $errors[] = 'The product image does not have the correct extension';
    }
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM products WHERE name = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $prod_name);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $sql = 'INSERT INTO products(name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sdssi', $prod_name, $prod_price, $prod_description, $prod_image, $prod_cate);
                $res = $stmt->execute();
                if ($res) {
                    move_uploaded_file($prod_image_tmp_name, $prod_image_folder);
                    $successfully = 'New product added successfully';
                    $conn->commit();
                } else {
                    $errors[] = 'Could not add the product';
                    $conn->rollback();
                }
            } else {
                $errors[] = 'Product name already exists';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Could not add the product: ' . $e->getMessage());
        }
    }
}
if (isset($_GET['edit'])) {
    $id_product = intval($_GET['edit']);
    try {
        $sql = 'SELECT * FROM products WHERE id = ? AND status = "active"';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_product);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $data_product = $res->fetch_assoc();
        } else {
            $errors[] = 'id of product does not exist';
        }
    } catch (\Exception $e) {
        die('Get data from table product failed' . $e->getMessage());
    }
}
if (isset($_POST['edit-prod'])) {
    $prod_id = sanitize($_POST['id']);
    $prod_id = intval($prod_id);
    $prod_name = sanitize($_POST['name']);
    $prod_cate = sanitize($_POST['cate']);
    $prod_cate = intval($prod_cate);
    $prod_price = sanitize($_POST['price']);
    $prod_price = floatval($prod_price);
    $prod_description = sanitize($_POST['description']);
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $prod_image_old = sanitize($_POST['img-old']);
        $prod_image = sanitize($_FILES['img']['name']);
        $prod_image_extension = pathinfo($prod_image, PATHINFO_EXTENSION);
        $prod_image_tmp_name =  sanitize($_FILES['img']['tmp_name']);
        $prod_image_folder = '../assets/img/products/' . $prod_image;
        $conn->real_escape_string($prod_image);
    }
    $conn->real_escape_string($prod_name);
    $conn->real_escape_string($prod_cate);
    $conn->real_escape_string($prod_price);
    $conn->real_escape_string($prod_description);
    if (strlen($prod_name) < 5 || strlen($prod_name) > 200) {
        $errors[] = 'Category name must be more than 5 characters and less than 200 characters';
    } elseif (empty($prod_price)) {
        $errors[] = 'Product price is not in the correct format';
    } elseif (!empty($prod_description)) {
        if (strlen($prod_description) < 5 || strlen($prod_description) > 800) {
            $errors[] = 'Product description must be more than 5 characters and less than 800 characters';
        }
    } elseif ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        if (!($prod_image_extension === 'jpg' || $prod_image_extension === 'jpeg' || $prod_image_extension === 'png')) {
            $errors[] = 'The product image does not have the correct extension';
        }
    }
    if (empty($errors)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM products WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $prod_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $sql = 'UPDATE products SET name = ?, price = ?, description = ?, category_id = ?' . (isset($prod_image) ? ', image = "' . $prod_image . '"' : '') . ' WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sdsii', $prod_name, $prod_price, $prod_description, $prod_cate, $prod_id);
                $res = $stmt->execute();
                if ($res) {
                    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
                        $file_del_path = '../assets/img/products/' . $prod_image_old;
                        if (file_exists($file_del_path)) {
                            unlink($file_del_path);
                            move_uploaded_file($prod_image_tmp_name, $prod_image_folder);
                            if (file_exists($prod_image_folder)) {
                                $conn->commit();
                                header('location: ./products.php');
                            } else {
                                $conn->rollback();
                                $errors[] = 'Editing product failed';
                            }
                        }
                    } else {
                        $conn->commit();
                        header('location: ./products.php');
                    }
                } else {
                    $conn->rollback();
                    $errors[] = 'Editing product failed';
                }
            } else {
                $errors[] = 'Id of product does not exist';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Editing product failed: ' . $e->getMessage());
        }
    }
}
require_once './layout/header.php';
?>
<div class="bg-light admin__main">
    <div class="container">
        <?php
        if (!empty($errors)) {
            echo '<ul class="list-unstyled alert alert-danger px-4 fw-500">';
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
        <form class="p-4 rounded bg-white form" action="./change_product.php" method="post" enctype="multipart/form-data">
            <div class="d-flex align-items-center justify-content-between">
                <h3><?= (isset($data_product)) ? 'Edit product' : 'Add a new product' ?></h3>
                <a href="./products.php" class="btn btn-danger lh-100">Back</a>
            </div>
            <?php
            if (isset($data_product)) :
            ?>
                <input type="text" name="id" hidden value="<?= $data_product['id'] ?>">
            <?php
            endif;
            ?>
            <label class="form__label" for="product-name">Name</label>
            <input type="text" name="name" id="product-name" class="form-control mb-3 form__inp" placeholder="product name" value="<?= (isset($data_product)) ? $data_product['name'] : '' ?>" minlength="5" maxlength="200" required>
            <label class="form__label" for="product-category">Category</label>
            <a href="./change_category.php" class="ms-3 mb-2 py-1 btn btn-success">New category</a>
            <select name="cate" id="product-category" class="form-control mb-3 form__inp">
                <option value="" disabled>Select category</option>
                <?php
                foreach ($category_arr as $cate) :
                ?>
                    <option value="<?= $cate['id'] ?>" <?= $selected = (isset($data_product)) ? ($data_product['category_id'] == $cate['id'] ? 'selected' : '') : '' ?>><?= ucwords($cate['name']) ?></option>
                <?php
                endforeach;
                ?>
            </select>
            <label class="form__label" for="product-price">Price</label>
            <input type="number" step="0.01" name="price" id="product-price" class="form-control mb-3 form__inp" placeholder="product price" value="<?= (isset($data_product)) ? $data_product['price'] : '' ?>">
            <label class="form__label" for="product-description">Desciption</label>
            <textarea name="description" rows="3" id="product-description" class="form-control mb-3 form__textarea"><?= (isset($data_product)) ? $data_product['description'] : '' ?></textarea>
            <label class="form__label" for="file-prod-img">Image</label>
            <?php
            if (isset($data_product)) :
            ?>
                <img src="../assets/img/products/<?= $data_product['image'] ?>" alt="product" class="my-1 admin__old-image">
                <input type="text" name="img-old" hidden value="<?= $data_product['image'] ?>">
            <?php
            endif;
            ?>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="img" id="file-prod-img" class="form-control mb-3 form__inp">
            <input type="submit" class="btn btn-success w-100 form__btn" name="<?= (isset($data_product)) ? 'edit-prod' : 'add-prod' ?>" value="<?= (isset($data_product)) ? 'Edit product' : 'Add product' ?>">
        </form>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>