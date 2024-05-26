<?php
    if (!(isset($_SESSION['admin']) || isset($_COOKIE['admin']))) {
        header('Location: ./login.php');
    } else {
        if (isset($_SESSION['admin'])) {
            $admin = $_SESSION['admin'];
        } elseif (isset($_COOKIE['admin'])) {
            $admin = json_decode($_COOKIE['admin'], true);
        }
    }
?>