<?php
    function dd($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    function sanitize($data) {
        $data = trim($data);
        stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
?>