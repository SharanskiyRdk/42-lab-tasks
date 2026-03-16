<?php
$routes = [
    'GET' => [
        '/' => function() {
            return '<h1>Главная страница</h1>
                    <p>Добро пожаловать на наш сайт!</p>
                    <a href="/form">Перейти к форме</a>';
        },
        '/form' => function() {
            return '
                <h1>Форма обратной связи</h1>
                <form method="POST" action="/form">
                    <div>
                        <label for="name">Имя:</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div style="margin-top: 10px;">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div style="margin-top: 10px;">
                        <label for="message">Сообщение:</label><br>
                        <textarea name="message" id="message" rows="5" cols="40" required></textarea>
                    </div>
                    <div style="margin-top: 10px;">
                        <button type="submit">Отправить</button>
                    </div>
                </form>
                <p><a href="/">На главную</a></p>
            ';
        },
        '/about' => function() {
            return '<h1>О нас</h1>
                    <p>Это пример простого роутера на PHP.</p>
                    <p><a href="/">На главную</a></p>';
        }
    ],
    'POST' => [
        '/form' => function() {
            $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
            $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
            $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

            return '
                <h1>Данные формы успешно получены!</h1>
                <div style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
                    <h2>Вы отправили:</h2>
                    <p><strong>Имя:</strong> ' . $name . '</p>
                    <p><strong>Email:</strong> ' . $email . '</p>
                    <p><strong>Сообщение:</strong><br>' . nl2br($message) . '</p>
                </div>
                <p><a href="/">На главную</a> | <a href="/form">Вернуться к форме</a></p>
            ';
        }
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

$url = parse_url($_SERVER['REQUEST_URI']);
$path = $url['path'];

function renderPage($content) {
    return '<!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Простой роутер на PHP</title>
            </head>
            <body>
                ' . $content . '
            </body>
            </html>';
}

if (isset($routes[$method][$path])) {
    echo renderPage($routes[$method][$path]());
} else {
    http_response_code(404);

    $content = '<h1>404 - Страница не найдена</h1>
                <p>Извините, запрошенная страница не существует.</p>
                <p><a href="/">Вернуться на главную</a></p>';

    echo renderPage($content);
}