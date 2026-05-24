<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (current_user()) {
    redirect(is_admin() ? '/admin/requests.php' : '/requests.php');
}

$errors = [];
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($login === '') {
        $errors['login'] = 'Введите логин.';
    }

    if ($password === '') {
        $errors['password'] = 'Введите пароль.';
    }

    if (!$errors && login_user($login, $password)) {
        redirect(is_admin() ? '/admin/requests.php' : '/requests.php');
    }

    if (!$errors) {
        $errors['form'] = 'Неверный логин или пароль.';
    }
}

$pageTitle = 'Авторизация';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-card">
    <div class="slider" data-slider>
        <button class="slider-button prev" type="button" data-prev aria-label="Предыдущее изображение">‹</button>
        <div class="slides">
            <img class="slide active" src="/assets/img/slide-1.svg" alt="Онлайн обучение">
            <img class="slide" src="/assets/img/slide-2.svg" alt="Веб-дизайн">
            <img class="slide" src="/assets/img/slide-3.svg" alt="Базы данных">
            <img class="slide" src="/assets/img/slide-4.svg" alt="Программирование">
        </div>
        <button class="slider-button next" type="button" data-next aria-label="Следующее изображение">›</button>
    </div>

    <form class="form-panel" method="post" novalidate>
        <h1>Вход на портал</h1>
        <?php if (isset($errors['form'])): ?>
            <p class="alert"><?= e($errors['form']) ?></p>
        <?php endif; ?>

        <label>
            <span>Логин</span>
            <input type="text" name="login" value="<?= e($login) ?>" autocomplete="username">
            <?php if (isset($errors['login'])): ?><small><?= e($errors['login']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="current-password">
            <?php if (isset($errors['password'])): ?><small><?= e($errors['password']) ?></small><?php endif; ?>
        </label>

        <button class="primary-button" type="submit">Войти</button>
        <a class="text-link" href="/register.php">Еще не зарегистрированы? Регистрация</a>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
