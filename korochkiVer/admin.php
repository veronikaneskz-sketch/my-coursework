<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
    $raw = [
        'application_id' => (string)($_POST['application_id'] ?? ''),
        'status' => (string)($_POST['status'] ?? ''),
    ];
    $applicationId = (int)clean_input($raw['application_id']);
    $status = clean_input($raw['status']);
    if (!has_markup($raw['application_id']) && !has_markup($raw['status']) && in_array($status, ['new', 'in_progress', 'completed'], true)) {
        $stmt = db()->prepare('UPDATE applications SET status = ? WHERE id = ?');
        $stmt->execute([$status, $applicationId]);
        flash('success', 'Статус заявки обновлён.');
    } else {
        flash('error', 'Переданы некорректные данные для обновления статуса.');
    }
    $query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    redirect('admin.php' . $query);
}

$statusFilterRaw = (string)($_GET['status'] ?? '');
$searchRaw = (string)($_GET['search'] ?? '');
$statusFilter = has_markup($statusFilterRaw) ? '' : clean_input($statusFilterRaw);
$search = has_markup($searchRaw) ? '' : clean_input($searchRaw);

$where = [];
$params = [];
if ($statusFilter !== '' && in_array($statusFilter, ['new', 'in_progress', 'completed'], true)) {
    $where[] = 'a.status = ?';
    $params[] = $statusFilter;
}
if ($search !== '') {
    $where[] = '(u.full_name LIKE ? OR u.login LIKE ? OR c.title LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$countStmt = db()->prepare("SELECT COUNT(*) as cnt FROM applications a JOIN users u ON u.id = a.user_id JOIN courses c ON c.id = a.course_id $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['cnt'];
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT a.*, u.full_name, u.login, u.phone, u.email, c.title AS course_title
        FROM applications a
        JOIN users u ON u.id = a.user_id
        JOIN courses c ON c.id = a.course_id
        $whereSql
        ORDER BY a.id DESC
        LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();
$pageTitle = 'Панель администратора';
$metaDescription = 'Панель администратора со списком заявок, поиском, фильтрацией, пагинацией и обновлением статусов.';
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-head portal-head">
            <div>
                <div class="kicker">административная панель</div>
                <h1>Очередь заявок</h1>
            </div>
            <div class="metrics-inline">
                <div><span>Всего заявок</span><strong><?= $total ?></strong></div>
                <div><span>На странице</span><strong><?= count($applications) ?></strong></div>
            </div>
        </div>

        <form method="get" class="filters filters-shell reveal show">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Поиск по ФИО, логину или программе">
            <select name="status">
                <option value="">Все статусы</option>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>Новая</option>
                <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>Идёт обучение</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Обучение завершено</option>
            </select>
            <button class="btn btn-secondary" type="submit">Применить</button>
        </form>

        <div class="admin-ledger">
            <?php if (!$applications): ?>
                <article class="ledger-row empty-state reveal show">
                    <div>
                        <h3>Заявки не найдены</h3>
                        <p>Измените параметры поиска или снимите фильтр по статусу.</p>
                    </div>
                </article>
            <?php endif; ?>
            <?php foreach ($applications as $app): ?>
                <article class="ledger-row reveal show">
                    <div class="ledger-main">
                        <div class="ledger-title">
                            <span class="queue-id">Заявка #<?= $app['id'] ?></span>
                            <h3><?= e($app['course_title']) ?></h3>
                            <p><?= e($app['full_name']) ?> · <?= e($app['login']) ?></p>
                        </div>
                        <span class="status status-<?= e($app['status']) ?>"><?= e(format_status($app['status'])) ?></span>
                    </div>
                    <div class="ledger-grid">
                        <div><span>Телефон</span><strong><?= e($app['phone']) ?></strong></div>
                        <div><span>Email</span><strong><?= e($app['email']) ?></strong></div>
                        <div><span>Дата старта</span><strong><?= e($app['desired_start_date']) ?></strong></div>
                        <div><span>Оплата</span><strong><?= e(format_payment($app['payment_method'])) ?></strong></div>
                    </div>
                    <form method="post" class="ledger-actions">
                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                        <select name="status">
                            <option value="new" <?= $app['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                            <option value="in_progress" <?= $app['status'] === 'in_progress' ? 'selected' : '' ?>>Идёт обучение</option>
                            <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Обучение завершено</option>
                        </select>
                        <button class="btn btn-primary" type="submit">Сохранить</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="?<?= http_build_query(['search' => $search, 'status' => $statusFilter, 'page' => $i]) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
