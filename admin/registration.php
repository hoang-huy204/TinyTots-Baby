<?php
require_once '../utils.php';
require_once '../config.php';
require_once '../connect.php';
require_once './check_login.php';

if (isset($_POST['registration'])) {
    $name = sanitize($_POST['name']);
    $name = ucwords($name);
    $fullname = sanitize($_POST['fullname']);
    $fullname = ucwords($fullname);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = sanitize($_POST['password']);
    $name = $conn->real_escape_string($name);
    $fullname = $conn->real_escape_string($fullname);
    $email = $conn->real_escape_string($email);
    $phone = $conn->real_escape_string($phone);
    $password = $conn->real_escape_string($password);

    if (strlen($name) === 0 || strlen($name) > 30) {
        $error = 'Name cannot be blank or larger than 30 characters';
    } else {
        if (strlen($fullname) < 5 || strlen($fullname) > 100) {
            $error = 'Full name must be greater than 5 characters and less than 100 characters';
        } else {
            $regex_email = '/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/';
            if (!(preg_match($regex_email, $email) === 1)) {
                $error = 'Email invalidate';
            } else {
                if (!preg_match('/^(0|\+84)\d{9,10}$/', $phone)) {
                    $error = 'Phone invalidate';
                } else {
                    if (strlen($password) < 5 || strlen($password) > 40) {
                        $error = 'Password must be greater than 5 characters and less than 40 characters';
                    }
                }
            }
        }
    }

    if (!isset($error)) {
        try {
            $sql = 'SELECT * FROM users WHERE email = ? AND role = "admin"';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $error = 'Email already exist';
            } else {
                $sql = 'INSERT INTO users (name, fullname, email, phone, password, role) VALUE (?, ?, ?, ?, sha1(?), "admin")';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssss', $name, $fullname, $email, $phone, $password);
                $res = $stmt->execute();
                if ($res) {
                    $successfully = 'Registered an account successfully';
                } else {
                    $error = 'Registration failed';
                }
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}

require_once './layout/header.php'
?>

<section id="content-app" class="py-1" style="min-height: 100vh;">
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
                <h2 class="mt-4 text-center">Registration admin</h2>
                <form action="" method="POST" class="mt-4 mb-1">
                    <input class="mt-2 form-control" type="text" name="name" placeholder="Name" value="<?= $value = (!isset($successfully) && isset($_POST['name'])) ? $_POST['name'] : '' ?>" maxlength="30" required>
                    <input class="mt-2 form-control" type="text" name="fullname" placeholder="Full name" value="<?= $value = (!isset($successfully) && isset($_POST['fullname'])) ? $_POST['fullname'] : '' ?>" minlength="5" maxlength="100" required>
                    <input class="mt-2 form-control" type="email" name="email" placeholder="Email" value="<?= $value = (!isset($successfully) && isset($_POST['email'])) ? $_POST['email'] : '' ?>" required>
                    <input type="text" name="phone" class="mt-2 form-control" placeholder="Phone" value="<?= $value = (!isset($successfully) && isset($_POST['phone'])) ? $_POST['phone'] : '' ?>" require>
                    <input class="mt-2 form-control" type="password" name="password" placeholder="Password" value="<?= $value = (!isset($successfully) && isset($_POST['password'])) ? $_POST['password'] : '' ?>" minlength="5" maxlength="40" required>
                    <input class="my-4 w-100 rounded-pill btn-primary" type="submit" name="registration" value="Registration">
                </form>
            </div>
        </div>
    </div>
</section>

<?php
require_once './layout/footer.php'
?>