<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_admin();

$message = '';
$statusFilter = $_GET['status'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $newStatus = (string)($_POST['status'] ?? '');

    if (array_key_exists($newStatus, REQUEST_STATUSES)) {
        $stmt = db()->prepare('UPDATE applications SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $requestId);
        $stmt->execute();
        $message = 'Статус заявки обновлен.';
    }
}

$where = '';
$types = '';
$params = [];

if ($statusFilter !== 'all' && array_key_exists($statusFilter, REQUEST_STATUSES)) {
    $where = 'WHERE a.status = ?';
    $types = 's';
    $params[] = $statusFilter;
}

$countSql = "SELECT COUNT(*) AS total FROM applications a $where";
$stmt = db()->prepare($countSql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['total'];
$pages = max(1, (int)ceil($total / $perPage));

$sql = "SELECT a.*, u.full_name, u.phone, u.email
        FROM applications a
        INNER JOIN users u ON u.id = a.user_id
        $where
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = db()->prepare($sql);

if ($types) {
    $typesWithLimit = $types . 'ii';
    $paramsWithLimit = [...$params, $perPage, $offset];
    $stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}

$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Панель администратора';
require __DIR__ . '/../includes/header.php';
?>
<section class="work-panel admin-panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">Панель администратора</p>
            <h1>Заявки пользователей</h1>
        </div>
    </div>

    <?php if ($message): ?><p class="notice"><?= e($message) ?></p><?php endif; ?>

    <form class="filter-line" method="get">
        <label>
            <span>Фильтр</span>
            <select name="status" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Все заявки</option>
                <?php foreach (REQUEST_STATUSES as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Пользователь</th>
                <th>Курс</th>
                <th>Дата</th>
                <th>Оплата</th>
                <th>Статус</th>
                <th>Отзыв</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($applications as $application): ?>
                <tr>
                    <td>
                        <strong><?= e($application['full_name']) ?></strong>
                        <span><?= e($application['phone']) ?></span>
                        <span><?= e($application['email']) ?></span>
                    </td>
                    <td><?= e($application['course_name']) ?></td>
                    <td><?= e(format_training_date($application['start_date'])) ?></td>
                    <td><?= e(PAYMENT_METHODS[$application['payment_method']] ?? $application['payment_method']) ?></td>
                    <td>
                        <form method="post" class="status-form">
                            <input type="hidden" name="request_id" value="<?= (int)$application['id'] ?>">
                            <select name="status">
                                <?php foreach (REQUEST_STATUSES as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $application['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="secondary-button" type="submit">OK</button>
                        </form>
                    </td>
                    <td><?= $application['review'] ? e($application['review']) : '<span class="muted">Нет отзыва</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$applications): ?>
        <p class="empty-state">Заявки по выбранному фильтру не найдены.</p>
    <?php endif; ?>

    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="<?= $i === $page ? 'active' : '' ?>" href="/admin/requests.php?status=<?= e($statusFilter) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
