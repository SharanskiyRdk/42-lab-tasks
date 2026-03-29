<?php
require_once __DIR__ . '/classes/Cart.php';

session_start();

if (!isset($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'];
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$cart->remove($productId);

header('Location: cart.php');
exit;