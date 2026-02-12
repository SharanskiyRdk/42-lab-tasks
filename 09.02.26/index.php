<?php

if (!isset($_GET['name']) || !isset($_GET['role']) || $_GET['name'] === '' || $_GET['role'] === '') {

    http_response_code(400);
    echo '<!DOCTYPE html>';
    echo '<html lang="ru">';
    echo '<head><meta charset="UTF-8"><title>Ошибка 400</title></head>';
    echo '<body>';
    echo '<h1>400 Bad Request</h1>';
    echo '<p>Параметры <code>name</code> и <code>role</code> обязательны.</p>';
    echo '</body>';
    echo '</html>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

$name = isset($_GET['name']) ? $_GET['name'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeRole = htmlspecialchars($role, ENT_QUOTES, 'UTF-8');

$name = preg_replace('/[^a-zA-Z\s]/', '', $name);
$name = substr($name, 0, 20);
$role = preg_replace('/[^a-z]/i', '', $role);

$hi = 'Добрый день: ';

if ($safeRole === 'admin') {
    $rolePart = '<span class="admin-role">админ</span>';
} else {
    $rolePart = '';
}

$hi .= $rolePart . ' ' . $safeName;

$requestMethod = $_SERVER['REQUEST_METHOD'];
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$fullUri = $scheme . '://' . $host . $uri;

echo '<!DOCTYPE html>';
echo '<html lang="ru">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Профиль</title>';
echo '<style>';
echo '
    .admin-role {
        color: red;
        font-weight: bold;
    }
';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<p>' . $hi . '</p>';
echo '<p>Метод: ' . htmlspecialchars($requestMethod) . '</p>';
echo '<p>URI: ' . htmlspecialchars($fullUri) . '</p>';
echo '</body>';
echo '</html>';