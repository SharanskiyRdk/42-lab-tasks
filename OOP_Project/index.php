<?php
require_once __DIR__ . '/classes/Product.php';

$products = [
    new Product(1, 'Ноутбук Apple MacBook Air 13"', 89999.00),
    new Product(2, 'Смартфон Xiaomi 13T Pro', 49999.00),
    new Product(3, 'Наушники Sony WH-1000XM5', 29999.00),
    new Product(4, 'Клавиатура Logitech MX Keys', 12999.00),
    new Product(5, 'Мышь Logitech MX Master 3S', 9999.00),
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .product-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
        .product-title { font-size: 18px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .product-price { font-size: 24px; font-weight: bold; color: #2e7d32; margin-bottom: 15px; }
        .btn-add { background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 16px; width: 100%; }
        .btn-add:hover { background: #388e3c; }
        .cart-link { text-align: center; margin-top: 20px; }
        .btn-cart { display: inline-block; background: #2196f3; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-size: 18px; }
        .btn-cart:hover { background: #1976d2; }
    </style>
</head>
<body>
<div class="container">
    <h1>Товары</h1>

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-title"><?= htmlspecialchars($product->getTitle()) ?></div>
                <div class="product-price"><?= $product->getFormattedPrice() ?></div>
                <form action="add.php" method="GET">
                    <input type="hidden" name="id" value="<?= $product->getId() ?>">
                    <button type="submit" class="btn-add">Добавить в корзину</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-link">
        <a href="cart.php" class="btn-cart">Перейти в корзину</a>
    </div>
</div>
</body>
</html>