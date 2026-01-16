<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="app">
    <header class="top-bar">
        <div class="logo">ADMIN CENTER</div>
        <button class="ghost-button" type="button">Выйти</button>
    </header>
    <section class="hero">
        <h1>Центр управления продуктом</h1>
        <p>Здесь будет: push-центр, сегментация, статистика, модерация.</p>
    </section>
    <section class="features">
        <div class="feature">
            <h3>Push-центр</h3>
            <p>Создание, расписание, статистика и приоритеты.</p>
        </div>
        <div class="feature">
            <h3>Сегментация</h3>
            <p>Гибкие фильтры по поведению, статусу, гео.</p>
        </div>
        <div class="feature">
            <h3>Модерация</h3>
            <p>Профили, селфи-верификация, жалобы.</p>
        </div>
    </section>
</div>
</body>
</html>
