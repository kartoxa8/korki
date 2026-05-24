<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_user();
$user = current_user();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $review = trim($_POST['review'] ?? '');

    if ($review === '') {
        $errors[$requestId] = 'Напишите отзыв перед отправкой.';
    } else {
        $stmt = db()->prepare('UPDATE applications SET review = ? WHERE id = ? AND user_id = ? AND status = "completed"');
        $stmt->bind_param('sii', $review, $requestId, $user['id']);
        $stmt->execute();
        $message = $stmt->affected_rows > 0 ? 'Отзыв сохранен.' : 'Отзыв можно оставить только после завершения обучения.';
    }
}

$stmt = db()->prepare('SELECT id, course_name, start_date, payment_method, status, review, created_at FROM applications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Мои заявки';
require __DIR__ . '/includes/header.php';
?>
<section class="work-panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Личный кабинет</p>
            <h1>Мои заявки</h1>
        </div>
        <a class="primary-button compact" href="/create_request.php">Подать заявку</a>
    </div>

    <?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>

    <?php if (!$applications): ?>
        <p class="empty-state">Заявок пока нет.</p>
    <?php endif; ?>

    <div class="request-grid">
        <?php foreach ($applications as $application): ?>
            <article class="request-card">
                <div class="card-topline">
                    <strong><?= e($application['course_name']) ?></strong>
                    <span class="status status-<?= e($application['status']) ?>"><?= e(REQUEST_STATUSES[$application['status']] ?? $application['status']) ?></span>
                </div>
                <p>Дата начала: <?= e(format_training_date($application['start_date'])) ?></p>
                <p>Оплата: <?= e(PAYMENT_METHODS[$application['payment_method']] ?? $application['payment_method']) ?></p>

                <?php if ($application['status'] === 'completed'): ?>
                    <form class="review-form" method="post">
                        <input type="hidden" name="request_id" value="<?= (int)$application['id'] ?>">
                        <label>
                            <span>Отзыв</span>
                            <textarea name="review" rows="3"><?= e($application['review']) ?></textarea>
                            <?php if (isset($errors[(int)$application['id']])): ?><small><?= e($errors[(int)$application['id']]) ?></small><?php endif; ?>
                        </label>
                        <button class="secondary-button" type="submit">Сохранить отзыв</button>
                    </form>
                <?php else: ?>
                    <p class="muted">Отзыв будет доступен после завершения обучения.</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
