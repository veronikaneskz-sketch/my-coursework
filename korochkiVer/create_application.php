<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
if (is_admin()) { redirect('admin.php'); }
$errors = [];
$courseList = courses();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = [
        'course_id' => (string)($_POST['course_id'] ?? ''),
        'start_date' => (string)($_POST['start_date'] ?? ''),
        'payment_method' => (string)($_POST['payment_method'] ?? ''),
    ];
    $data = [
        'course_id' => clean_input($raw['course_id']),
        'start_date' => normalize_application_start_date(clean_input($raw['start_date'])),
        'payment_method' => clean_input($raw['payment_method']),
    ];
    with_old($data);
    $errors = validate_application($data, $raw);
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO applications (user_id, course_id, desired_start_date, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([current_user()['id'], $data['course_id'], $data['start_date'], $data['payment_method'], 'new', date('Y-m-d H:i:s')]);
        clear_old();
        flash('success', 'Заявка отправлена и передана на обработку.');
        redirect('applications.php');
    }
}
$pageTitle = 'Подача заявки';
$metaDescription = 'Форма подачи заявки на обучение с выбором программы, даты и способа оплаты.';
include __DIR__ . '/includes/header.php';
?>
<section class="section auth-shell">
  <div class="narrow compact-wrap">
    <div class="form-card compact-card reveal show">
      <div class="kicker">новая заявка</div>
      <h1>Оформление заявки</h1>
      <p>Выберите программу, укажите желаемую дату старта и отметьте подходящий способ оплаты.</p>
      <form method="post" novalidate>
        <label>Программа обучения
          <select name="course_id">
            <option value="">Выберите программу</option>
            <?php foreach ($courseList as $course): ?>
              <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="error"><?= e(validation_error($errors, 'course_id')) ?></small>
        </label>
        <label>Желаемая дата начала
          <input type="date" name="start_date" value="<?= e(application_start_date_for_input(old('start_date'))) ?>" min="<?= e(date('Y-m-d')) ?>">
          <small class="error"><?= e(validation_error($errors, 'start_date')) ?></small>
        </label>
        <label>Способ оплаты</label>
        <div class="radio-grid">
          <label class="option-box"><input type="radio" name="payment_method" value="cash" <?= old('payment_method') === 'cash' ? 'checked' : '' ?>><span>Наличными</span></label>
          <label class="option-box"><input type="radio" name="payment_method" value="phone_transfer" <?= old('payment_method') === 'phone_transfer' ? 'checked' : '' ?>><span>Перевод по номеру телефона</span></label>
        </div>
        <small class="error"><?= e(validation_error($errors, 'payment_method')) ?></small>
        <button class="btn btn-primary full" type="submit">Отправить заявку</button>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
