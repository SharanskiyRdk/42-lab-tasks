<?php
$products = [
    ['id' => 1, 'name' => 'Книга PHP', 'price' => 1000, 'tags' => ['книга', 'php']],
    ['id' => 2, 'name' => 'Клавиатура', 'price' => 2500, 'tags' => ['электроника']],
    ['id' => 3, 'name' => 'Мышь', 'price' => 800, 'tags' => ['электроника']],
    ['id' => 4, 'name' => 'Монитор', 'price' => 8000, 'tags' => ['электроника']],
    ['id' => 5, 'name' => 'Книга js', 'price' => 1200, 'tags' => ['книга', 'js']],
    ['id' => 6, 'name' => 'Ноутбук', 'price' => 40000, 'tags' => ['электроника']],
    ['id' => 7, 'name' => 'Наушники', 'price' => 2000, 'tags' => ['электроника']],
    ['id' => 8, 'name' => 'Книга Python', 'price' => 1500, 'tags' => ['книга', 'python']],
    ['id' => 9, 'name' => 'SSD диск', 'price' => 5000, 'tags' => ['электроника']],
    ['id' => 10, 'name' => 'Видеокарта', 'price' => 30000, 'tags' => ['электроника']]
];

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$search = $_GET['q'] ?? '';
$minPrice = isset($_GET['min']) ? (float)$_GET['min'] : 0;
$maxPrice = isset($_GET['max']) ? (float)$_GET['max'] : 999999;
$sortField = $_GET['sort'] ?? 'name';
$sortDir = $_GET['dir'] ?? 'asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 3;

$filteredProducts = [];
foreach ($products as $product) {
    $found = true;
    if ($search !== '') {
        $found = false;
        if (stripos($product['name'], $search) !== false) {
            $found = true;
        }
        foreach ($product['tags'] as $tag) {
            if (stripos($tag, $search) !== false) {
                $found = true;
                break;
            }
        }
    }

    if ($product['price'] < $minPrice || $product['price'] > $maxPrice) {
        $found = false;
    }

    if ($found) {
        $filteredProducts[] = $product;
    }
}

usort($filteredProducts, function($a, $b) use ($sortField, $sortDir) {
    if ($sortField === 'price') {
        if ($a['price'] == $b['price']) {
            $result = 0;
        } else {
            $result = ($a['price'] < $b['price']) ? -1 : 1;
        }
    } else {
        $result = strcmp($a['name'], $b['name']);
    }

    if ($sortDir === 'desc') {
        $result = -$result;
    }

    return $result;
});

$totalItems = count($filteredProducts);
$totalPages = ceil($totalItems / $perPage);

if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $perPage);

$params = $_GET;
unset($params['page']);
$queryString = '';
foreach ($params as $key => $value) {
    if ($queryString !== '') {
        $queryString .= '&';
    }
    $queryString .= $key . '=' . urlencode($value);
}
if ($queryString !== '') {
    $queryString .= '&';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Каталог товаров</title>
</head>
<body>
<h1>Каталог товаров</h1>
<form method="GET">
    <p>
        <label>Поиск: <input type="text" name="q" value="<?= e($search) ?>"></label>
    </p>
    <p>
        <label>Цена от: <input type="number" name="min" value="<?= $minPrice ?: '' ?>"></label>
        <label>до: <input type="number" name="max" value="<?= $maxPrice != 999999 ? $maxPrice : '' ?>"></label>
    </p>
    <p>
        <label>Сортировать:
            <select name="sort">
                <option value="name" <?= $sortField == 'name' ? 'selected' : '' ?>>По названию</option>
                <option value="price" <?= $sortField == 'price' ? 'selected' : '' ?>>По цене</option>
            </select>
        </label>
        <label>
            <select name="dir">
                <option value="asc" <?= $sortDir == 'asc' ? 'selected' : '' ?>>По возрастанию</option>
                <option value="desc" <?= $sortDir == 'desc' ? 'selected' : '' ?>>По убыванию</option>
            </select>
        </label>
    </p>
    <p>
        <label>Товаров на странице:
            <select name="perPage">
                <option value="3" <?= $perPage == 3 ? 'selected' : '' ?>>3</option>
                <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
            </select>
        </label>
    </p>
    <p>
        <button type="submit">Применить</button>
        <a href="?">Сбросить все фильтры</a>
    </p>
</form>

<hr>
<h3>Найдено товаров: <?= $totalItems ?></h3>

<?php if (count($paginatedProducts) > 0): ?>
    <ul>
        <?php foreach ($paginatedProducts as $product): ?>
            <li>
                <strong><?= e($product['name']) ?></strong> - <?= $product['price'] ?> руб.
                <br>
                <small>Теги:
                    <?php
                    $tags = '';
                    foreach ($product['tags'] as $index => $tag) {
                        if ($index > 0) $tags .= ', ';
                        $tags .= e($tag);
                    }
                    echo $tags;
                    ?>
                </small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Товары не найдены</p>
<?php endif; ?>

<hr>
<?php if ($totalPages > 1): ?>
    <p>
        Страница <?= $page ?> из <?= $totalPages ?>
    </p>
    <p>
        <?php if ($page > 1): ?>
            <a href="?<?= $queryString ?>page=1">[Первая]</a>
            <a href="?<?= $queryString ?>page=<?= $page - 1 ?>">[Предыдущая]</a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= $queryString ?>page=<?= $page + 1 ?>">[Следующая]</a>
            <a href="?<?= $queryString ?>page=<?= $totalPages ?>">[Последняя]</a>
        <?php endif; ?>
    </p>
<?php endif; ?>
</body>
</html>