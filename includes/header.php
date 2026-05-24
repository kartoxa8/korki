<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = $pageTitle ?? 'Корочки.есть';
$user = current_user();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="page-shell">
    <header class="liquid-header">
        <a class="brand" href="<?= $user ? (is_admin() ? '/admin/requests.php' : '/requests.php') : '/index.php' ?>">
            Корочки.есть
        </a>
        <?php if ($user): ?>
            <nav class="top-nav" aria-label="Основная навигация">
                <?php if (is_admin()): ?>
                    <a class="nav-link" href="/admin/requests.php">Заявки</a>
                    <a class="nav-link" href="/logout.php">Выйти</a>
                <?php else: ?>
                    <a class="nav-link" href="/requests.php">Заявки</a>
                    <a class="nav-link" href="/create_request.php">Подать заявку</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </header>

    <main class="content">
