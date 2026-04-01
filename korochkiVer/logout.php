<?php
require_once __DIR__ . '/includes/functions.php';
session_destroy();
session_start();
flash('success', 'Вы вышли из системы.');
redirect('login.php');
