<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

if (isset($_POST['send-franchising'])) {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $message = sanitize($_POST['message']);
    $fullname = $conn->real_escape_string($fullname);
    $email = $conn->real_escape_string($email);
    $message = $conn->real_escape_string($message);
    if (strlen($fullname) < 5 || strlen($fullname) > 60) {
        $error = 'Fullname must be greater than 5 characters and less than 60 characters';
    } elseif (!(preg_match('/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/', $email) === 1)) {
        $error = 'Email invalidate';
    } elseif (!preg_match('/^(0|\+84)\d{9,10}$/', $phone)) {
        $error = 'Phone invalid';
    } elseif (strlen($message) < 5 || strlen($message) > 500) {
        $error = 'Message must be greater than 5 characters and less than 500 characters';
    }

    if (!isset($error)) {
        try {
            $sql = 'INSERT INTO franchising (fullname, email, phone, message) VALUE (?, ?, ?, ?)';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssss', $fullname, $email, $phone, $message);
            $res = $stmt->execute();
            if ($res) {
                $successfully = 'Thanks for the info';
            } else {
                $error = 'Franchising error';
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}

require_once './layout/header.php';
?>
<div id="contents-with-footer">
    <section id="content-app">
        <div class="py-4 bg-light">
            <div class="container content-section">
                <h2 class="text-center text-uppercase content__heading">Franchising</h2>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><i class="d-inline-block ti-location-pin" style="width: 25px"></i>New York, US</p>
                        <p class="mb-1"><i class="d-inline-block ti-mobile" style="width: 25px"></i>Phone: <a href="tell:+01 151515">+01 151515</a></p>
                        <p class="mb-1"><i class="d-inline-block ti-email" style="width: 25px"></i>Email: <a href="mailto:tinytots@gmail.com">tinytots@gmail.com</a></p>
                    </div>
                    <div class="col-md-6">
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
                        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                            <div class="row">
                                <div class="col-12">
                                    <input type="text" name="fullname" placeholder="Fullname" minlength="10" maxlength="60" required class="form-control">
                                </div>
                            </div>
                            <div class="row mt-2" style="margin: 0 -4px;">
                                <div class="col-md-6 px-1">
                                    <input type="text" name="phone" placeholder="Phone number" minlength="10" maxlength="13" required class="form-control">
                                </div>
                                <div class="col-md-6 px-1">
                                    <input type="email" name="email" placeholder="Email" required class="form-control">
                                </div>

                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <input type="text" name="message" placeholder="Message" required class="form-control">
                                </div>
                            </div>
                            <button class="mt-3 d-block ms-auto btn btn-dark text-uppercase" name="send-franchising">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    require_once './layout/footer.php';
    ?>