<?php
require_once __DIR__ . '/config.php';

function db()
{
    static $pdo = null;

    if ($pdo === null) {
        if (DB_DRIVER !== 'mysql') {
            throw new RuntimeException('В config.php поддерживается только драйвер mysql.');
        }

        $host = trim((string) DB_HOST);
        $port = trim((string) DB_PORT);
        $dbName = trim((string) DB_NAME);
        $charset = trim((string) DB_CHARSET);

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $dbName,
            $charset
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $safeMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            die(
                '<h2>Ошибка подключения к базе данных</h2>'
                . '<p><strong>PDO:</strong> ' . $safeMessage . '</p>'
                . '<p>Проверьте файл <code>includes/config.php</code> и убедитесь, что:</p>'
                . '<ul>'
                . '<li>MySQL запущен;</li>'
                . '<li>имя базы в <code>DB_NAME</code> совпадает с реально созданной базой;</li>'
                . '<li>файл <code>database.sql</code> импортирован именно в эту базу;</li>'
                . '<li>для Open Server обычно подходит <code>DB_HOST=localhost</code>, а не <code>127.0.0.1</code>;</li>'
                . '<li>порт, логин и пароль указаны верно.</li>'
                . '</ul>'
            );
        }
    }

    return $pdo;
}
