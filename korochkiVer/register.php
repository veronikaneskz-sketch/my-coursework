<?php
require_once __DIR__ . '/includes/functions.php';
require_guest();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = [
        'login' => (string)($_POST['login'] ?? ''),
        'password' => (string)($_POST['password'] ?? ''),
        'full_name' => (string)($_POST['full_name'] ?? ''),
        'phone' => (string)($_POST['phone'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
    ];
    $data = [
        'login' => clean_input($raw['login']),
        'password' => $raw['password'],
        'full_name' => clean_input($raw['full_name']),
        'phone' => clean_input($raw['phone']),
        'email' => clean_input($raw['email']),
    ];
    with_old([
        'login' => $data['login'],
        'full_name' => $data['full_name'],
        'phone' => $data['phone'],
        'email' => $data['email'],
    ]);
    $errors = validate_registration($data, $raw);
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (login, password_hash, full_name, phone, email, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['login'], password_hash($data['password'], PASSWORD_DEFAULT), $data['full_name'], $data['phone'], $data['email'], 'user', date('Y-m-d H:i:s')]);
        clear_old();
        flash('success', 'Аккаунт успешно создан. Теперь можно войти в личный кабинет.');
        redirect('login.php');
    }
}
$pageTitle = 'Создать аккаунт';
$metaDescription = 'Регистрация пользователя на платформе онлайн-записи на программы обучения.';
include __DIR__ . '/includes/header.php';
?>
<section class="section auth-shell">
  <div class="narrow compact-wrap">
    <div class="form-card compact-card reveal show">
      <div class="kicker">регистрация</div>
      <h1>Создание аккаунта</h1>
      <p>Заполните поля, чтобы получить доступ к личному кабинету и подаче заявок на обучение.</p>
      <form method="post" novalidate>
        <label>Логин
          <input type="text" name="login" value="<?= e(old('login')) ?>" placeholder="Например: student01">
          <small class="error"><?= e(validation_error($errors, 'login')) ?></small>
        </label>
        <label>Пароль
          <input type="password" name="password" placeholder="Минимум 8 символов">
          <small class="error"><?= e(validation_error($errors, 'password')) ?></small>
        </label>
        <label>ФИО
          <input type="text" name="full_name" value="<?= e(old('full_name')) ?>" placeholder="Иванов Иван Иванович">
          <small class="error"><?= e(validation_error($errors, 'full_name')) ?></small>
        </label>
        <label>Телефон
          <input type="text" name="phone" value="<?= e(old('phone')) ?>" placeholder="8(900)123-45-67">
          <small class="error"><?= e(validation_error($errors, 'phone')) ?></small>
        </label>
        <label>Email
          <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="mail@example.com">
          <small class="error"><?= e(validation_error($errors, 'email')) ?></small>
        </label>
        <button class="btn btn-primary full" type="submit">Создать аккаунт</button>
      </form>
      <p class="switch-link">Уже зарегистрированы? <a href="login.php">Войти</a></p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
