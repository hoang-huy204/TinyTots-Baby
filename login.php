<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
    header('Location: ./index.php');
}

if (isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);

    $regex_email = '/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/';
    if (!(preg_match($regex_email, $email) === 1)) {
        $error = 'Email invalidate';
    } else {
        if (strlen($password) < 5 || strlen($password) > 40) {
            $error = 'Password must be greater than 5 characters and less than 40 characters';
        }
    }

    if (!isset($error)) {
        try {
            $sql = 'SELECT id, name, fullname, email, address, phone FROM users WHERE role = "user" AND email = ? AND password = sha1(?)';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $password);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 1) {
                $user = $res->fetch_assoc();
                if (isset($_POST['remember'])) {
                    setcookie('user', json_encode($user), time() + 86400);
                } else {
                    $_SESSION['user'] = $user;
                }
                $sql = 'SELECT * FROM carts where user_id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $user['id']);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows === 0) {
                    $sql = 'INSERT INTO carts (user_id) VALUE (?)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('i', $user['id']);
                    $stmt->execute();
                    $_SESSION['cart_id'] = $conn->insert_id;
                } else {
                    $row = $res->fetch_assoc();
                    $_SESSION['cart_id'] = $row['id'];
                }
                header('Location: ./index.php');
            } else {
                $error = 'Incorrect email or password';
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <section id="content-app" class="py-1">
        <div class="container login__box">
            <div class="row">
                <div class="col-4 mx-auto bg-white rounded shadow-sm">
                    <?php
                    if (isset($error)) :
                    ?>
                        <p class="mt-3 alert alert-danger"><?= $error ?></p>
                    <?php
                    endif;

                    if (isset($successfully)) :
                    ?>
                        <p class="mt-3 alert alert-success"><?= $successfully ?></p>
                    <?php
                    endif;
                    ?>
                    <h2 class="mt-4 text-center">Login Form</h2>
                    <form action="" method="POST" class="mt-4 mb-2">
                        <input class="form-control" type="email" name="email" placeholder="Email" value="<?= $value = (isset($error) && isset($_POST['email'])) ? $_POST['email'] : '' ?>" required>
                        <input class="mt-2 form-control" type="password" name="password" placeholder="Password" value="<?= $value = (isset($error) && isset($_POST['password'])) ? $_POST['password'] : '' ?>" minlength="5" maxlength="40" required>
                        <div class="mt-1">
                            <input type="checkbox" name="remember" id="remember" <?= $checked = (isset($error) && isset($_POST['remember'])) ? 'checked' : '' ?>>
                            <label for="remember">Remember me !</label>
                        </div>
                        <input class="mt-4 w-100 rounded-pill btn-primary" type="submit" name="login" value="Login">
                    </form>
                    <a href="./registration.php" class="mb-4 login__link">Create your account <i class="fa-solid fa-arrow-right-long"></i></a>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once './layout/footer.php';
    ?>