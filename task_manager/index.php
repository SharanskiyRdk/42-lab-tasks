<?php
session_start();

define('UPLOAD_DIR', __DIR__ . '/uploads/');
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);

if (!isset($_SESSION['tasks'])) $_SESSION['tasks'] = [];
if (!isset($_SESSION['next_id'])) $_SESSION['next_id'] = 1;
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

function redirect($url) { header("Location: $url"); exit; }
function escape($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) return false;
if (strpos($_SERVER["REQUEST_URI"], '/uploads/') === 0) {
    $file = __DIR__ . $_SERVER["REQUEST_URI"];
    if (file_exists($file)) { header("Content-Type: " . mime_content_type($file)); readfile($file); exit; }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// === ОБРАБОТКА ДЕЙСТВИЙ ===

if ($uri === '/tasks' && $method === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Ошибка безопасности');

    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $errors = [];

    if (strlen($title) < 3) $errors[] = 'Заголовок должен быть не менее 3 символов';
    if (strlen($title) > 100) $errors[] = 'Заголовок не более 100 символов';

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['title' => $title, 'text' => $text];
        redirect('/tasks/create');
    }

    $id = $_SESSION['next_id']++;
    $task = ['id' => $id, 'title' => $title, 'text' => $text, 'created_at' => date('Y-m-d H:i:s'), 'image' => null];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['image']['size'] < 5*1024*1024) {
            $filename = $id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $filename);
            $task['image'] = $filename;
        }
    }

    $_SESSION['tasks'][$id] = $task;
    $_SESSION['success'] = 'Задача создана!';
    redirect('/');
}

if (preg_match('#^/tasks/(\d+)$#', $uri, $m) && $method === 'POST') {
    $id = (int)$m[1];
    if (!isset($_SESSION['tasks'][$id])) redirect('/');
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Ошибка безопасности');

    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $errors = [];

    if (strlen($title) < 3) $errors[] = 'Заголовок должен быть не менее 3 символов';
    if (strlen($title) > 100) $errors[] = 'Заголовок не более 100 символов';

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['title' => $title, 'text' => $text];
        redirect("/tasks/{$id}/edit");
    }

    $task = &$_SESSION['tasks'][$id];
    $task['title'] = $title;
    $task['text'] = $text;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['image']['size'] < 5*1024*1024) {
            if ($task['image'] && file_exists(UPLOAD_DIR . $task['image'])) unlink(UPLOAD_DIR . $task['image']);
            $filename = $id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $filename);
            $task['image'] = $filename;
        }
    }

    $_SESSION['success'] = 'Задача обновлена!';
    redirect('/');
}

if (preg_match('#^/tasks/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    $id = (int)$m[1];
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die('Ошибка безопасности');

    if (isset($_SESSION['tasks'][$id])) {
        if ($_SESSION['tasks'][$id]['image']) {
            @unlink(UPLOAD_DIR . $_SESSION['tasks'][$id]['image']);
        }
        unset($_SESSION['tasks'][$id]);
        $_SESSION['success'] = 'Задача удалена!';
    }
    redirect('/');
}

// === HTML ===

$success = $_SESSION['success'] ?? null;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['success'], $_SESSION['errors']);

function header_html() {
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Менеджер задач</title>
    <style>
        * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  padding: 2rem;
  line-height: 1.6;
}

.container {
  max-width: 60rem;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 2rem;
  padding: 2.5rem;
  box-shadow: 0 25px 45px rgba(0,0,0,0.1);
}

.btn {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s ease;
  font-size: 0.95rem;
  letter-spacing: 0.025em;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-danger {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.task-card {
  background: white;
  border-radius: 1.25rem;
  padding: 1.75rem;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(0,0,0,0.05);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.task-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.task-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.task-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  color: #2d3748;
  line-height: 1.3;
}

.task-text {
  color: #718096;
  margin: 1rem 0;
  line-height: 1.7;
}

.task-image img {
  max-width: 12.5rem;
  border-radius: 1rem;
  margin: 1rem 0;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.task-actions {
  margin-top: 1.25rem;
  display: flex;
  gap: 0.75rem;
}

.form-group {
  margin-bottom: 1.75rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #4a5568;
  font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: white;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group textarea {
  min-height: 6.5rem;
  resize: vertical;
  font-family: inherit;
}

.flash {
  padding: 1.25rem;
  border-radius: 1rem;
  margin-bottom: 1.75rem;
  font-weight: 500;
}

.flash-success {
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
  color: #155724;
  border: 1px solid #c3e6cb;
}

.flash-error {
  background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: #a0aec0;
  font-size: 1.1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

h1 {
  margin-bottom: 2rem;
  font-weight: 700;
  color: #2d3748;
  font-size: 2.25rem;
}

    </style>
</head>
<body>
<div class="container">
    <h1> Менеджер задач</h1>
HTML;
}

function footer_html() {
    echo '</div></body></html>';
}

if ($success) echo "<div class='flash flash-success'>✅ $success</div>";
if ($errors) echo "<div class='flash flash-error'>❌ " . implode('<br>', array_map('escape', $errors)) . "</div>";

if ($uri === '/' && $method === 'GET') {
    header_html();
    echo '<div style="text-align:right; margin-bottom:20px;"><a href="/tasks/create" class="btn btn-primary">➕ Создать задачу</a></div>';

    if (empty($_SESSION['tasks'])) {
        echo '<div class="empty-state"><p>Нет задач</p><a href="/tasks/create" class="btn btn-primary">Создать первую</a></div>';
    } else {
        foreach (array_reverse($_SESSION['tasks']) as $task) {
            echo '<div class="task-card">';
            echo '<div class="task-title">' . escape($task['title']) . '</div>';
            echo '<div class="task-meta" style="color:#999;font-size:0.85em;">' . escape($task['created_at']) . '</div>';
            if (!empty($task['text'])) echo '<div class="task-text">' . nl2br(escape($task['text'])) . '</div>';
            if (!empty($task['image']) && file_exists(UPLOAD_DIR . $task['image'])) {
                echo '<div class="task-image"><img src="/uploads/' . $task['image'] . '" onclick="window.open(this.src)"></div>';
            }
            echo '<div class="task-actions">';
            echo '<a href="/tasks/' . $task['id'] . '/edit" class="btn btn-secondary">✏️ Редактировать</a>';
            echo '<form action="/tasks/' . $task['id'] . '/delete" method="POST" style="flex:1">';
            echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
            echo '<button type="submit" class="btn btn-danger" onclick="return confirm(\'Удалить?\')">🗑️ Удалить</button>';
            echo '</form></div></div>';
        }
    }
    footer_html();
}

elseif ($uri === '/tasks/create' && $method === 'GET') {
    $form_data = $_SESSION['form_data'] ?? ['title'=>'', 'text'=>''];
    unset($_SESSION['form_data']);

    header_html();
    echo '<h2>Создание задачи</h2>';
    echo '<form action="/tasks" method="POST" enctype="multipart/form-data">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<div class="form-group"><label>Заголовок *</label><input type="text" name="title" value="' . escape($form_data['title']) . '" required></div>';
    echo '<div class="form-group"><label>Описание</label><textarea name="text">' . escape($form_data['text']) . '</textarea></div>';
    echo '<div class="form-group"><label>Изображение</label><input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"><small> Max 5MB</small></div>';
    echo '<div class="form-actions"><button type="submit" class="btn btn-primary">Создать</button><a href="/" class="btn btn-secondary">Отмена</a></div>';
    echo '</form>';
    footer_html();
}

elseif (preg_match('#^/tasks/(\d+)/edit$#', $uri, $m) && $method === 'GET') {
    $id = (int)$m[1];
    if (!isset($_SESSION['tasks'][$id])) { $_SESSION['error'] = 'Задача не найдена'; redirect('/'); }

    $task = $_SESSION['tasks'][$id];
    $form_data = $_SESSION['form_data'] ?? $task;
    unset($_SESSION['form_data']);

    header_html();
    echo '<h2>Редактирование задачи</h2>';
    echo '<form action="/tasks/' . $id . '" method="POST" enctype="multipart/form-data">';
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    echo '<div class="form-group"><label>Заголовок *</label><input type="text" name="title" value="' . escape($form_data['title']) . '" required></div>';
    echo '<div class="form-group"><label>Описание</label><textarea name="text">' . escape($form_data['text']) . '</textarea></div>';
    echo '<div class="form-group"><label>Изображение</label><input type="file" name="image" accept=".jpg,.jpeg,.png,.gif"><small> Max 5MB</small>';
    if (!empty($task['image']) && file_exists(UPLOAD_DIR . $task['image'])) {
        echo '<div><img src="/uploads/' . $task['image'] . '" style="max-width:100px; margin-top:10px;"></div>';
    }
    echo '</div>';
    echo '<div class="form-actions"><button type="submit" class="btn btn-primary">Сохранить</button><a href="/" class="btn btn-secondary">Отмена</a></div>';
    echo '</form>';
    footer_html();
}

else {
    http_response_code(404);
    header_html();
    echo '<h2>404 - Страница не найдена</h2>';
    echo '<p><a href="/">Вернуться на главную</a></p>';
    footer_html();
}