<?php
require_once './utils.php';
require_once './config.php';
require_once './connect.php';

// if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
//     if (isset($_SESSION['user'])) {
//         $user = $_SESSION['user'];
//     } elseif (isset($_COOKIE['user'])) {
//         $user = json_decode($_COOKIE['user'], true);
//     }
//     $user_id = intval($user['id']);
// }

// dd($_SESSION['cart']);
// die();

function removeNestedArrayByIndex($array, $index, $searchValue)
{
    foreach ($array as $key => $item) {
        if (isset($item[$index]) && $item[$index] === $searchValue) {
            unset($array[$key]);
            break;
        }
    }
    return array_values($array);
}

function editNestedArrayByIndex($array, $index, $searchValue, $index2, $changeValue)
{
    foreach ($array as $key => $item) {
        if (isset($item[$index]) && $item[$index] === $searchValue) {
            $array[$key][$index2] = $changeValue;
            break;
        }
    }
    return $array;
}


if (isset($_GET['edit']) && isset($_GET['quantity'])) {
    $product_id = intval(sanitize($_GET['edit']));
    $product_qty = intval($_GET['quantity']);
    if ($product_qty < 1) {
        die();
    }
    if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
        $cart_id = intval($_SESSION['cart_id']);
        try {
            $sql = 'UPDATE carts_detail SET quantity = ? WHERE cart_id = ? AND product_id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iii', $product_qty, $cart_id, $product_id);
            $stmt->execute();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    } else {
        $_SESSION['cart'] = editNestedArrayByIndex($_SESSION['cart'], 'id', $product_id, 'quantity', $product_qty);
    }
    die();
}

if (isset($_GET['add']) && isset($_GET['quantity'])) {
    $product_id = intval(sanitize($_GET['add']));
    $product_qty = intval($_GET['quantity']);
    if ($product_qty < 1) {
        die();
    }
    if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
        $cart_id = intval($_SESSION['cart_id']);
        try {
            $conn->begin_transaction();
            $sql = 'INSERT INTO carts_detail (cart_id, product_id, quantity) VALUE (?, ?, ?)';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iii', $cart_id, $product_id, $product_qty);
            $res = $stmt->execute();
            if ($res) {
                $sql = 'SELECT COUNT(id) as quantity FROM carts_detail WHERE cart_id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $cart_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $num_prod_in_cart = $row['quantity'];
                echo $num_prod_in_cart;
            } else {
                die('Error');
            }
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            die($e->getMessage());
        }
    } else {
        array_push($_SESSION['cart'], array('id' => $product_id, 'quantity' => $product_qty));
        echo count($_SESSION['cart']);
    }
    die();
}

if (isset($_GET['remove'])) {
    $product_id = intval(sanitize($_GET['remove']));
    if (isset($_SESSION['user']) || isset($_COOKIE['user'])) {
        $cart_id = intval($_SESSION['cart_id']);
        try {
            $conn->begin_transaction();
            $sql = 'DELETE FROM carts_detail WHERE cart_id = ? AND product_id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $cart_id, $product_id);
            $res = $stmt->execute();
            if ($res) {
                $sql = 'SELECT COUNT(id) as quantity FROM carts_detail WHERE cart_id = ?';
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $cart_id);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $num_prod_in_cart = $row['quantity'];
                echo $num_prod_in_cart;
            } else {
                die('Error');
            }
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
            die($e->getMessage());
        }
    } else {
        $_SESSION['cart'] = removeNestedArrayByIndex($_SESSION['cart'], 'id', $product_id);
        echo count($_SESSION['cart']);
    }
    die();
}
