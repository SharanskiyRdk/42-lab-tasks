<?php
require_once __DIR__ . '/classes/Cart.php';

session_start();

if (!isset($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'];
$cart->clear();

header('Location: cart.php');
exit;