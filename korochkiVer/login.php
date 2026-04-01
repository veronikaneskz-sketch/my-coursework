<?php
require_once __DIR__ . '/includes/functions.php';
require_guest();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = ['login' => (string)($_POST['login'] ?? ''), 'password' => (string)($_POST['password'] ?? '')];
    $data = ['login' => clean_input($raw['login']), 'password' => $raw['password']];
    with_old(['login' => $data['login']]);
    $errors = validate_login($data, $raw);
    if (!$errors) {
        $stmt = db()->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
        $stmt->execute([$data['login']]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            $errors['login'] = 'Неверный логин или пароль.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            clear_old();
            flash('success', 'Вы вошли в личный кабинет.');
            redirect($user['role'] === 'admin' ? 'admin.php' : 'applications.php');
        }
    }
}
$pageTitle = 'Вход';
$metaDescription = 'Авторизация в системе подачи заявок на программы дополнительного образования.';
include __DIR__ . '/includes/header.php';
?>
<section class="section auth-shell">
  <div class="narrow compact-wrap">
    <div class="form-card compact-card reveal show">
      <div class="kicker">авторизация</div>
      <h1>Вход в кабинет</h1>
      <p>Введите логин и пароль, чтобы продолжить работу с заявками и программами обучения.</p>
      <form method="post" novalidate>
        <label>Логин
          <input type="text" name="login" value="<?= e(old('login')) ?>" placeholder="Введите логин">
          <small class="error"><?= e(validation_error($errors, 'login')) ?></small>
        </label>
        <label>Пароль
          <input type="password" name="password" placeholder="Введите пароль">
          <small class="error"><?= e(validation_error($errors, 'password')) ?></small>
        </label>
        <button class="btn btn-primary full" type="submit">Войти</button>
      </form>
      <p class="switch-link">Нет аккаунта? <a href="register.php">Создать</a></p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
