<?php
    session_start();
    if (isset($_SESSION['user'])) {
        unset($_SESSION['user']);
    } else {
        setcookie('user', '', time() + 0);
    }
    header('Location: ./index.php');
?>