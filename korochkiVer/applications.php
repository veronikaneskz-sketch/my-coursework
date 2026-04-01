<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
if (is_admin()) {
    redirect('admin.php');
}
$user = current_user();
$courseMap = course_map();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_application_id'])) {
    $applicationId = (int)($_POST['review_application_id'] ?? 0);
    $rawReviewText = (string)($_POST['review_text'] ?? '');
    $reviewText = clean_input($rawReviewText);
    $stmt = db()->prepare('SELECT * FROM applications WHERE id = ? AND user_id = ? AND status = ?');
    $stmt->execute([$applicationId, $user['id'], 'completed']);
    $application = $stmt->fetch();
    if (!$application) {
        flash('error', 'Оставить отзыв можно только после завершения обучения.');
    } else {
        $reviewError = validate_review($reviewText, $rawReviewText);
        if ($reviewError !== '') {
            flash('error', $reviewError);
        } else {
            $exists = db()->prepare('SELECT id FROM reviews WHERE application_id = ? LIMIT 1');
            $exists->execute([$applicationId]);
            if ($exists->fetch()) {
                flash('error', 'Отзыв по этой заявке уже оставлен.');
            } else {
                $ins = db()->prepare('INSERT INTO reviews (application_id, user_id, review_text, created_at) VALUES (?, ?, ?, ?)');
                $ins->execute([$applicationId, $user['id'], $reviewText, date('Y-m-d H:i:s')]);
                flash('success', 'Спасибо! Ваш отзыв сохранён.');
            }
        }
    }
    redirect('applications.php');
}

$stmt = db()->prepare('SELECT a.*, r.review_text FROM applications a LEFT JOIN reviews r ON r.application_id = a.id WHERE a.user_id = ? ORDER BY a.id DESC');
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();
$pageTitle = 'Мои заявки';
$metaDescription = 'Личный кабинет пользователя со статусами заявок, программами обучения и формой отзыва.';
include __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="section-head editorial-head">
            <div>
                <div class="kicker">личный кабинет</div>
                <h1>Мои заявки</h1>
            </div>
            <a class="btn btn-primary" href="create_application.php">Подать новую заявку</a>
        </div>
        <div class="cabinet-stack">
            <?php if (!$applications): ?>
                <article class="cabinet-card reveal">
                    <h3>Заявок пока нет</h3>
                    <p>Создайте первую заявку, чтобы программа появилась в кабинете и стала доступна для отслеживания.</p>
                </article>
            <?php endif; ?>
            <?php foreach ($applications as $app): ?>
                <article class="cabinet-card reveal">
                    <div class="cabinet-head">
                        <div>
                            <span class="cabinet-id">Заявка #<?= $app['id'] ?></span>
                            <h3><?= e($courseMap[$app['course_id']]['title'] ?? '-') ?></h3>
                        </div>
                        <span class="status status-<?= e($app['status']) ?>"><?= e(format_status($app['status'])) ?></span>
                    </div>
                    <div class="cabinet-meta">
                        <div><span>Дата начала</span><strong><?= e($app['desired_start_date']) ?></strong></div>
                        <div><span>Оплата</span><strong><?= e(format_payment($app['payment_method'])) ?></strong></div>
                    </div>
                    <?php if ($app['review_text']): ?>
                        <div class="review-panel">
                            <span>Отзыв</span>
                            <p><?= e($app['review_text']) ?></p>
                        </div>
                    <?php elseif ($app['status'] === 'completed'): ?>
                        <form method="post" class="review-form">
                            <input type="hidden" name="review_application_id" value="<?= $app['id'] ?>">
                            <label>Отзыв
                                <textarea name="review_text" placeholder="Опишите впечатление от программы"></textarea>
                            </label>
                            <button class="btn btn-secondary" type="submit">Сохранить отзыв</button>
                        </form>
                    <?php else: ?>
                        <div class="review-panel muted-panel">
                            <span>Отзыв</span>
                            <p>Форма отзыва откроется после того, как статус обучения станет «Обучение завершено».</p>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
