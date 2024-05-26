<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_GET['delete'])) {
    $news_id = intval($_GET['delete']);
    try {
        $sql = 'DELETE FROM news WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $news_id);
        $res = $stmt->execute();
        if ($res) {
            header('Location: ./news.php');
        } else {
            $error = 'delete failed category';
        }
    } catch (\Exception $e) {
        die('Delete category failed ' . $e->getMessage());
    }
}
if (isset($_POST['add-news'])) {
    $news_name = sanitize($_POST['name']);
    $news_description = sanitize($_POST['description']);
    $news_image = (isset($_FILES['img'])) ? sanitize($_FILES['img']['name']) : '';
    $news_image_extension = pathinfo($news_image, PATHINFO_EXTENSION);
    $news_image_tmp_name = (isset($_FILES['img'])) ? sanitize($_FILES['img']['tmp_name']) : '';
    $news_url = 'new_detail.php?id=';
    $conn->real_escape_string($news_name);
    $conn->real_escape_string($news_description);
    $conn->real_escape_string($news_image);
    $news_image_folder = '../assets/img/news/' . $news_image;
    if (strlen($news_name) < 5 || strlen($news_name) > 200) {
        $error = 'news name must be more than 5 characters and less than 200 characters';
    } elseif (strlen($news_description) < 5 || strlen($news_description) > 1500) {
        $error = 'new description must be more than 5 characters and less than 1500 characters';
    } elseif ($news_image_extension === '' || !($news_image_extension === 'jpg' || $news_image_extension === 'jpeg' || $news_image_extension === 'png')) {
        $error = 'The news image does not have the correct extension';
    }
    if (!isset($error)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM news WHERE name = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $news_name);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                $sql = 'INSERT INTO news (name, description, image) VALUES (?, ?, ?)';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sss', $news_name, $news_description, $news_image);
                $res = $stmt->execute();
                if ($res) {
                    $news_id_inserted = $conn->insert_id;
                    $sql = 'UPDATE news SET url = ? WHERE id = ?';
                    $news_url .= $news_id_inserted;
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('si', $news_url, $news_id_inserted);
                    $res = $stmt->execute();
                    if ($res) {
                        move_uploaded_file($news_image_tmp_name, $news_image_folder);
                        $conn->commit();
                        header('Location: ./news.php');
                    } else {
                        $error = 'Could not add the news';
                        $conn->rollback();
                    }
                } else {
                    $error = 'Could not add the news';
                    $conn->rollback();
                }
            } else {
                $error = 'news name already exists';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Could not add the new: ' . $e->getMessage());
        }
    }
}
if (isset($_GET['edit'])) {
    $news_id = intval($_GET['edit']);
    try {
        $sql = 'SELECT * FROM news WHERE id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $news_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $news_data = $res->fetch_assoc();
        } else {
            $error = 'id of new does not exist';
        }
    } catch (\Exception $e) {
        die('Get data from table new failed' . $e->getMessage());
    }
}
if (isset($_POST['edit-news'])) {
    $news_id = sanitize($_POST['id']);
    $news_id = intval($news_id);
    $news_name = sanitize($_POST['name']);
    $news_description = sanitize($_POST['description']);
    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $news_image_old = sanitize($_POST['news_img_file_old']);
        $news_image = sanitize($_FILES['img']['name']);
        $news_image_extension = pathinfo($news_image, PATHINFO_EXTENSION);
        $news_image_tmp_name =  sanitize($_FILES['img']['tmp_name']);
        $news_image_folder = '../assets/img/news/' . $news_image;
        $conn->real_escape_string($news_image);
    }
    $conn->real_escape_string($news_name);
    $conn->real_escape_string($news_description);
    if (strlen($news_name) < 5 || strlen($news_name) > 255) {
        $error = 'News name must be more than 5 characters and less than 255 characters';
    } elseif (strlen($news_description) < 5 || strlen($news_description) > 1500) {
        $error = 'new description must be more than 5 characters and less than 1500 characters';
    } elseif ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
        if (!($news_image_extension === 'jpg' || $news_image_extension === 'jpeg' || $news_image_extension === 'png')) {
            $error = 'The new image does not have the correct extension';
        }
    }
    if (empty($error)) {
        try {
            $conn->begin_transaction();
            $sql = 'SELECT * FROM news WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $news_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $sql = 'UPDATE news SET name = ?, description = ?' . (isset($news_image) ? ', image = "' . $news_image . '"' : '') . ' WHERE id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssi', $news_name, $news_description, $news_id);
                $res = $stmt->execute();
                if ($res) {
                    if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
                        $file_del_path = '../assets/img/news/' . $news_image_old;
                        if (file_exists($file_del_path)) {
                            unlink($file_del_path);
                            move_uploaded_file($news_image_tmp_name, $news_image_folder);
                            if (file_exists($news_image_folder)) {
                                $conn->commit();
                                header('location: ./news.php');
                            } else {
                                $conn->rollback();
                                $error = 'Editing new failed';
                            }
                        }
                    } else {
                        $conn->commit();
                        header('location: ./news.php');
                    }
                } else {
                    $conn->rollback();
                    $error = 'Editing new failed';
                }
            } else {
                $error = 'Id of new does not exist';
            }
        } catch (\Exception $e) {
            $conn->rollback();
            die('Editing new failed: ' . $e->getMessage());
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
        ?>
        <form class="p-4 rounded bg-white form" action="./change_news.php" method="post" enctype="multipart/form-data">
            <div class="d-flex align-items-center justify-content-between">
                <h3><?= (isset($news_data)) ? 'Edit news' : 'Add news' ?></h3>
                <a href="./news.php" class="btn btn-danger lh-100">Back</a>
            </div>
            <?php
            if (isset($news_data)) :
            ?>
                <input type="text" name="id" hidden value="<?= $news_data['id'] ?>">
            <?php
            endif;
            ?>
            <label class="form__label" for="news-name">Name</label>
            <input type="text" name="name" id="news-name" class="form-control mb-3 form__inp" placeholder="news name" value="<?= (isset($news_data)) ? $news_data['name'] : '' ?>" minlength="5" maxlength="255" required>
            <label class="form__label" for="news-description">Desciption</label>
            <textarea name="description" rows="3" id="news-description" class="form-control mb-3 form__textarea"><?= (isset($news_data)) ? $news_data['description'] : '' ?></textarea>
            <label class="form__label" for="news_img_file">Image</label>
            <?php
            if (isset($news_data)) :
            ?>
                <img src="../assets/img/news/<?= $news_data['image'] ?>" alt="new" class="my-1 admin__old-image">
                <input type="text" name="news_img_file_old" hidden value="<?= $news_data['image'] ?>">
            <?php
            endif;
            ?>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="img" id="news_img_file" class="form-control mb-3 form__inp">
            <input type="submit" class="btn btn-success w-100 form__btn" name="<?= (isset($news_data)) ? 'edit-news' : 'add-news' ?>" value="<?= (isset($news_data)) ? 'News editing' : 'Add news' ?>">
        </form>
    </div>
</div>
<?php
require_once './layout/footer.php';
?>