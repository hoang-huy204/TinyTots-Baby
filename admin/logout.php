<?php
    session_start();
    if (isset($_SESSION['admin'])) {
        unset($_SESSION['admin']);
    } else {
        setcookie('admin', '', time() + 0);
    }
    header('Location: ./login.php');
?>