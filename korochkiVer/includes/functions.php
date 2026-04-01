<?php
require_once __DIR__ . '/db.php';

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function clean_input($value)
{
    return trim(strip_tags((string)$value));
}

function has_markup($value)
{
    $value = (string)$value;
    if ($value === '') {
        return false;
    }
    return preg_match('/<[^>]*>|&lt;|&gt;|javascript:|<\s*script|<\s*style|on\w+\s*=|data:text\/html/i', $value) === 1;
}

function validate_no_markup(array $rawData, array $labels)
{
    $errors = [];
    foreach ($labels as $field => $label) {
        if (isset($rawData[$field]) && has_markup($rawData[$field])) {
            $errors[$field] = $label . ' не должно содержать HTML-теги или скрипты.';
        }
    }
    return $errors;
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = false;
    if ($user === false) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user ?: null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_guest()
{
    if (is_logged_in()) {
        redirect(is_admin() ? 'admin.php' : 'applications.php');
    }
}

function require_auth()
{
    if (!is_logged_in()) {
        flash('error', 'Сначала выполните вход в систему.');
        redirect('login.php');
    }
}

function require_admin()
{
    if (!is_admin()) {
        flash('error', 'Доступ разрешен только администратору.');
        redirect('login.php');
    }
}

function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function with_old($data)
{
    $_SESSION['old'] = $data;
}

function clear_old()
{
    unset($_SESSION['old']);
}

function flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes()
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function validate_registration($data, $raw = [])
{
    $errors = validate_no_markup($raw, [
        'login' => 'Логин',
        'password' => 'Пароль',
        'full_name' => 'ФИО',
        'phone' => 'Телефон',
        'email' => 'Email',
    ]);

    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $data['login'])) {
        $errors['login'] = 'Логин должен содержать латиницу и цифры, не менее 6 символов.';
    }
    if (mb_strlen($data['password']) < 8) {
        $errors['password'] = 'Пароль должен содержать минимум 8 символов.';
    }
    if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $data['full_name'])) {
        $errors['full_name'] = 'ФИО должно содержать только кириллицу и пробелы.';
    }
    if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $data['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате 8(XXX)XXX-XX-XX.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Укажите корректный адрес электронной почты.';
    }
    $stmt = db()->prepare('SELECT id FROM users WHERE login = ? LIMIT 1');
    $stmt->execute([$data['login']]);
    if ($stmt->fetch()) {
        $errors['login'] = 'Пользователь с таким логином уже существует.';
    }
    return $errors;
}

function validate_login($data, $raw = [])
{
    $errors = validate_no_markup($raw, [
        'login' => 'Логин',
        'password' => 'Пароль',
    ]);

    if ($data['login'] === '') {
        $errors['login'] = 'Введите логин.';
    }
    if ($data['password'] === '') {
        $errors['password'] = 'Введите пароль.';
    }
    return $errors;
}

function normalize_application_start_date($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if ($dt && $dt->format('Y-m-d') === $value) {
            return $dt->format('d.m.Y');
        }
        return '';
    }

    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
        $dt = DateTime::createFromFormat('d.m.Y', $value);
        if ($dt && $dt->format('d.m.Y') === $value) {
            return $value;
        }
    }

    return '';
}

function application_start_date_for_input($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }

    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
        $dt = DateTime::createFromFormat('d.m.Y', $value);
        if ($dt && $dt->format('d.m.Y') === $value) {
            return $dt->format('Y-m-d');
        }
    }

    return '';
}

function validate_application($data, $raw = [])
{
    $errors = validate_no_markup($raw, [
        'course_id' => 'Курс',
        'start_date' => 'Дата начала обучения',
        'payment_method' => 'Способ оплаты',
    ]);

    if ($data['course_id'] === '') {
        $errors['course_id'] = 'Выберите курс из списка.';
    }

    if ($data['start_date'] === '') {
        $errors['start_date'] = 'Укажите дату начала обучения.';
    } else {
        $dt = DateTime::createFromFormat('d.m.Y', $data['start_date']);
        if (!$dt || $dt->format('d.m.Y') !== $data['start_date']) {
            $errors['start_date'] = 'Укажите корректную дату начала обучения.';
        } else {
            $selectedDate = $dt->format('Y-m-d');
            $today = (new DateTime('today'))->format('Y-m-d');
            if ($selectedDate < $today) {
                $errors['start_date'] = 'Нельзя выбрать дату начала обучения раньше текущего дня.';
            }
        }
    }

    if (!in_array($data['payment_method'], ['cash', 'phone_transfer'], true)) {
        $errors['payment_method'] = 'Выберите способ оплаты.';
    }
    return $errors;
}

function validate_review($text, $rawText)
{
    if (has_markup($rawText)) {
        return 'Отзыв не должен содержать HTML-теги или скрипты.';
    }
    if (mb_strlen($text) < 10) {
        return 'Отзыв должен содержать не менее 10 символов.';
    }
    return '';
}

function validation_error($errors, $field)
{
    return $errors[$field] ?? '';
}

function courses()
{
    return db()->query('SELECT * FROM courses ORDER BY id')->fetchAll();
}

function course_map()
{
    $items = [];
    foreach (courses() as $course) {
        $items[$course['id']] = $course;
    }
    return $items;
}

function format_payment($value)
{
    return $value === 'cash' ? 'Наличными' : 'Перевод по номеру телефона';
}

function format_status($value)
{
    $map = [
        'new' => 'Новая',
        'in_progress' => 'Идет обучение',
        'completed' => 'Обучение завершено',
        'approved' => 'Подтверждена',
        'rejected' => 'Отклонена',
    ];
    return $map[$value] ?? $value;
}
