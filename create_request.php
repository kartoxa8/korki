<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_user();
$user = current_user();
$errors = [];
$values = [
    'course_name' => '',
    'start_date' => '',
    'payment_method' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $value) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if (!in_array($values['course_name'], COURSE_LIST, true)) {
        $errors['course_name'] = 'Выберите курс из списка.';
    }

    $mysqlDate = validate_training_date($values['start_date']);
    if ($mysqlDate === null) {
        $errors['start_date'] = 'Введите дату в формате ДД.ММ.ГГГГ.';
    }

    if (!array_key_exists($values['payment_method'], PAYMENT_METHODS)) {
        $errors['payment_method'] = 'Выберите способ оплаты.';
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO applications (user_id, course_name, start_date, payment_method) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isss', $user['id'], $values['course_name'], $mysqlDate, $values['payment_method']);
        $stmt->execute();
        redirect('/requests.php');
    }
}

$pageTitle = 'Подать заявку';
require __DIR__ . '/includes/header.php';
?>
<section class="single-panel">
    <form class="form-panel" method="post" novalidate>
        <p class="eyebrow">Новая заявка</p>
        <h1>Запись на курс</h1>

        <label>
            <span>Курс</span>
            <select name="course_name">
                <option value="">Выберите курс</option>
                <?php foreach (COURSE_LIST as $course): ?>
                    <option value="<?= e($course) ?>" <?= $values['course_name'] === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course_name'])): ?><small><?= e($errors['course_name']) ?></small><?php endif; ?>
        </label>

        <label>
            <span>Дата начала обучения</span>
            <input type="text" name="start_date" value="<?= e($values['start_date']) ?>" placeholder="25.05.2026">
            <?php if (isset($errors['start_date'])): ?><small><?= e($errors['start_date']) ?></small><?php endif; ?>
        </label>

        <fieldset>
            <legend>Способ оплаты</legend>
            <?php foreach (PAYMENT_METHODS as $key => $label): ?>
                <label class="radio-line">
                    <input type="radio" name="payment_method" value="<?= e($key) ?>" <?= $values['payment_method'] === $key ? 'checked' : '' ?>>
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
            <?php if (isset($errors['payment_method'])): ?><small><?= e($errors['payment_method']) ?></small><?php endif; ?>
        </fieldset>

        <button class="primary-button" type="submit">Отправить</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
