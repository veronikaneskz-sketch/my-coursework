<?php require_once __DIR__ . '/functions.php';
$user = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$metaDescription = $metaDescription ?? 'Платформа онлайн-записи на программы дополнительного профессионального образования: регистрация, подача заявок, отслеживание статуса и личный кабинет.';
$metaKeywords = $metaKeywords ?? 'онлайн обучение, дополнительное профессиональное образование, запись на курс, личный кабинет, заявка на обучение';
$ogImage = $ogImage ?? 'assets/img/image10.webp';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="ru_RU">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/app.js"></script>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="site-header">
    <div class="container nav-wrap">
        <a href="index.php" class="brand">
            <img src="assets/img/image03.png" alt="Логотип платформы">
            <span>Корочки.есть</span>
        </a>
        <button class="burger" type="button" aria-label="Открыть меню" data-burger>
            <span></span><span></span><span></span>
        </button>
        <nav class="nav" data-nav>
            <a href="index.php">Главная</a>
            <?php if (!$user): ?>
                <a href="register.php">Создать аккаунт</a>
                <a href="login.php">Войти</a>
            <?php elseif ($user['role'] === 'admin'): ?>
                <a href="admin.php">Заявки</a>
                <a href="logout.php">Выход</a>
            <?php else: ?>
                <a href="create_application.php">Новая заявка</a>
                <a href="applications.php">Мои заявки</a>
                <a href="logout.php">Выход</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <div class="container flash-stack">
        <?php foreach (get_flashes() as $flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
    </div>
