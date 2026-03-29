<?php
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Cart.php';

session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = new Cart();
}

$cart = $_SESSION['cart'];
$items = $cart->getItems();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .cart-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-collapse: collapse; }
        .cart-table th { background: #333; color: white; padding: 15px; text-align: left; }
        .cart-table td { padding: 15px; border-bottom: 1px solid #eee; }
        .cart-table tr:last-child td { border-bottom: none; }
        .quantity { font-weight: 600; color: #555; }
        .price { color: #2e7d32; font-weight: 600; }
        .subtotal { color: #e65100; font-weight: 600; }
        .btn-remove { background: #f44336; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-remove:hover { background: #d32f2f; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .total-row td { font-size: 18px; }
        .total-amount { color: #e65100; font-size: 22px; }
        .actions { margin-top: 20px; display: flex; gap: 15px; justify-content: space-between; }
        .btn-clear { background: #ff9800; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; }
        .btn-clear:hover { background: #f57c00; }
        .btn-continue { background: #2196f3; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; }
        .btn-continue:hover { background: #1976d2; }
        .empty-cart { text-align: center; padding: 50px; background: white; border-radius: 12px; }
        .empty-cart p { font-size: 20px; color: #666; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Корзина</h1>

    <?php if (empty($items)): ?>
        <div class="empty-cart">
            <p>Корзина пуста</p>
            <a href="index.php" class="btn-continue">Вернуться к покупкам</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>Товар</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item):
                $product = $item['product'];
                $quantity = $item['quantity'];
                $subtotal = $product->getPrice() * $quantity;
                ?>
                <tr>
                    <td><?= htmlspecialchars($product->getTitle()) ?></td>
                    <td class="price"><?= $product->getFormattedPrice() ?></td>
                    <td class="quantity"><?= $quantity ?> шт.</td>
                    <td class="subtotal"><?= number_format($subtotal, 2, '.', ' ') ?> руб.</td>
                    <td>
                        <form action="remove.php" method="GET">
                            <input type="hidden" name="id" value="<?= $product->getId() ?>">
                            <button type="submit" class="btn-remove">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Итого:</td>
                <td colspan="2" class="total-amount"><?= $cart->getFormattedTotal() ?></td>
            </tr>
            </tbody>
        </table>

        <div class="actions">
            <a href="index.php" class="btn-continue">Продолжить покупки</a>
            <a href="clear.php" class="btn-clear" onclick="return confirm('Очистить корзину?')">Очистить корзину</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>