<?php
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Cart.php';

session_start();

$products = [
    1 => new Product(1, 'Ноутбук Apple MacBook Air 13"', 89999.00),
    2 => new Product(2, 'Смартфон Xiaomi 13T Pro', 49999.00),
    3 => new Product(3, 'Наушники Sony WH-1000XM5', 29999.00),
    4 => new Product(4, 'Клавиатура Logitech MX Keys', 12999.00),
    5 => new Product(5, 'Мышь Logitech MX Master 3S', 9999.00),
];

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!isset($products[$productId])) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = new Cart();
}

$cart = $_SESSION['cart'];
$cart->add($products[$productId]);

header('Location: index.php');
exit;