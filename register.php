<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (current_user()) {
    redirect(is_admin() ? '/admin/requests.php' : '/requests.php');
}

$errors = [];
$values = [
    'login' => '',
    'full_name' => '',
    'phone' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $value) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    $password = (string)($_POST['password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $values['login'])) {
        $errors['login'] = 'Логин: латиница и цифры, минимум 6 символов.';
    }

    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Пароль должен быть не короче 8 символов.';
    }

    if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $values['full_name'])) {
        $errors['full_name'] = 'ФИО должно содержать только кириллицу и пробелы.';
    }

    if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $values['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате 8(XXX)XXX-XX-XX.';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный адрес электронной почты.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE login = ? LIMIT 1');
        $stmt->bind_param('s', $values['login']);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()) {
            $errors['login'] = 'Пользователь с таким логином уже существует.';
        }
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO users (login, password_hash, full_name, phone, email) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $values['login'], $passwordHash, $values['full_name'], $values['phone'], $values['email']);
        $stmt->execute();

        login_user($values['login'], $password);
        redirect('/requests.php');
    }
}

$pageTitle = 'Регистрация';
require __DIR__ . '/includes/header.php';
?>
<section class="single-panel">
    <form class="form-panel" method="post" novalidate>
        <h1>Регистрация</h1>

        <label>
            <span>Логин</span>
            <input type="text" name="login" value="<?= e($values['login']) ?>" autocomplete="username">
            <?php if (isset($errors['login'])): ?><small><?= e($errors['login']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="new-password">
            <?php if (isset($errors['password'])): ?><small><?= e($errors['password']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>ФИО</span>
            <input type="text" name="full_name" value="<?= e($values['full_name']) ?>" autocomplete="name">
            <?php if (isset($errors['full_name'])): ?><small><?= e($errors['full_name']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>Телефон</span>
            <input type="tel" name="phone" value="<?= e($values['phone']) ?>" placeholder="8(999)123-45-67" autocomplete="tel">
            <?php if (isset($errors['phone'])): ?><small><?= e($errors['phone']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>Email</span>
            <input type="email" name="email" value="<?= e($values['email']) ?>" autocomplete="email">
            <?php if (isset($errors['email'])): ?><small><?= e($errors['email']) ?></small><?php endif; ?>
        </label>

        <button class="primary-button" type="submit">Зарегистрироваться</button>
        <a class="text-link" href="/index.php">Уже зарегистрированы? Войти</a>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
