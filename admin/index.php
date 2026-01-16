<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/services/Database.php';
require_once __DIR__ . '/../app/services/CsrfService.php';

$csrfService = new CsrfService();
$csrfToken = $csrfService->generateToken();

$pdo = Database::getConnection();
$userId = $_SESSION['user_id'] ?? null;
$isAdmin = false;

if ($userId) {
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    $isAdmin = $user && $user['role'] === 'admin';
}

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $token = $_POST['csrf_token'] ?? null;
    if (!$csrfService->validateToken($token)) {
        $flash = ['type' => 'error', 'message' => 'Неверный CSRF токен.'];
    } elseif (!empty($_POST['verification_id']) && !empty($_POST['verification_status'])) {
        $stmt = $pdo->prepare('UPDATE selfie_verifications SET status = :status WHERE id = :id');
        $stmt->execute([
            'status' => $_POST['verification_status'],
            'id' => (int) $_POST['verification_id'],
        ]);
        $flash = ['type' => 'success', 'message' => 'Статус верификации обновлен.'];
    } else {
        $segment = array_filter([
            'status' => $_POST['status'] ?? null,
            'vip' => $_POST['vip'] ?? null,
            'trial' => $_POST['trial'] ?? null,
            'online' => $_POST['online'] ?? null,
            'city' => $_POST['city'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'goal' => $_POST['goal'] ?? null,
            'trust_min' => $_POST['trust_min'] ?? null,
            'last_active_days' => $_POST['last_active_days'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $stmt = $pdo->prepare(
            'INSERT INTO admin_push_jobs (title, body, segment, scheduled_at) VALUES (:title, :body, :segment, :scheduled_at)'
        );
        $stmt->execute([
            'title' => trim($_POST['title'] ?? ''),
            'body' => trim($_POST['body'] ?? ''),
            'segment' => json_encode($segment, JSON_UNESCAPED_UNICODE),
            'scheduled_at' => $_POST['scheduled_at'] ?: null,
        ]);

        $flash = ['type' => 'success', 'message' => 'Рассылка сохранена.'];
    }
}

$jobs = $pdo->query('SELECT id, title, body, scheduled_at, sent_at, stats_sent, stats_delivered, stats_clicked FROM admin_push_jobs ORDER BY id DESC LIMIT 10')->fetchAll();
$verifications = $pdo->query('SELECT id, user_id, code_phrase, status, created_at FROM selfie_verifications ORDER BY id DESC LIMIT 10')->fetchAll();
$payments = $pdo->query('SELECT id, external_id, user_id, amount, status, created_at FROM payments ORDER BY id DESC LIMIT 10')->fetchAll();

?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body>
<div class="app">
    <header class="top-bar">
        <div class="logo">ADMIN CENTER</div>
        <a class="ghost-button" href="<?= BASE_URL ?>">На сайт</a>
    </header>

    <?php if (!$isAdmin) : ?>
        <section class="hero">
            <h1>Доступ только для администратора</h1>
            <p>Войдите под админ-аккаунтом, чтобы управлять пуш-центром.</p>
        </section>
    <?php else : ?>
        <section class="hero">
            <h1>Push-центр</h1>
            <p>Создание уведомлений, сегментация и расписание рассылок.</p>
        </section>

        <?php if ($flash) : ?>
            <div class="flash <?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="admin-grid">
            <form class="admin-card" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <h2>Создать рассылку</h2>
                <label>
                    Заголовок
                    <input type="text" name="title" required>
                </label>
                <label>
                    Текст
                    <textarea name="body" rows="3" required></textarea>
                </label>
                <label>
                    Отправка (дата/время)
                    <input type="datetime-local" name="scheduled_at">
                </label>
                <h3>Сегментация</h3>
                <div class="form-grid">
                    <label>
                        Статус
                        <select name="status">
                            <option value="">Все</option>
                            <option value="active">Активные</option>
                            <option value="blocked">Заблокированные</option>
                        </select>
                    </label>
                    <label>
                        VIP
                        <select name="vip">
                            <option value="">Все</option>
                            <option value="yes">VIP</option>
                            <option value="no">Free</option>
                        </select>
                    </label>
                    <label>
                        Trial
                        <select name="trial">
                            <option value="">Все</option>
                            <option value="yes">На trial</option>
                            <option value="no">Без trial</option>
                        </select>
                    </label>
                    <label>
                        Online
                        <select name="online">
                            <option value="">Все</option>
                            <option value="yes">Онлайн</option>
                            <option value="no">Оффлайн</option>
                        </select>
                    </label>
                    <label>
                        Город
                        <input type="text" name="city">
                    </label>
                    <label>
                        Пол
                        <select name="gender">
                            <option value="">Любой</option>
                            <option value="male">Мужчины</option>
                            <option value="female">Женщины</option>
                            <option value="other">Другое</option>
                        </select>
                    </label>
                    <label>
                        Цель
                        <input type="text" name="goal">
                    </label>
                    <label>
                        Доверие от
                        <input type="number" name="trust_min" min="0" max="100">
                    </label>
                    <label>
                        Активность (дней назад)
                        <input type="number" name="last_active_days" min="0">
                    </label>
                </div>
                <button class="primary-button" type="submit">Сохранить рассылку</button>
            </form>

            <div class="admin-card">
                <h2>История отправок</h2>
                <div class="admin-list">
                    <?php foreach ($jobs as $job) : ?>
                        <div class="admin-list-item">
                            <div>
                                <strong><?= htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p><?= htmlspecialchars($job['body'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="admin-meta">
                                <span>ID <?= (int) $job['id'] ?></span>
                                <span>Запланировано: <?= htmlspecialchars($job['scheduled_at'] ?? 'сразу', ENT_QUOTES, 'UTF-8') ?></span>
                                <span>Отправлено: <?= htmlspecialchars($job['sent_at'] ?? 'нет', ENT_QUOTES, 'UTF-8') ?></span>
                                <span>Отправок: <?= (int) $job['stats_sent'] ?></span>
                                <span>Доставлено: <?= (int) $job['stats_delivered'] ?></span>
                                <span>Клики: <?= (int) $job['stats_clicked'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="admin-card">
            <h2>Селфи-верификации</h2>
            <div class="admin-list">
                <?php foreach ($verifications as $item) : ?>
                    <div class="admin-list-item">
                        <div>
                            <strong>Пользователь #<?= (int) $item['user_id'] ?></strong>
                            <p>Код-фраза: <?= htmlspecialchars($item['code_phrase'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Статус: <?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="verification_id" value="<?= (int) $item['id'] ?>">
                            <select name="verification_status">
                                <option value="pending">pending</option>
                                <option value="verified">verified</option>
                                <option value="rejected">rejected</option>
                            </select>
                            <button class="secondary-button" type="submit">Обновить</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-card">
            <h2>Платежи</h2>
            <div class="admin-list">
                <?php foreach ($payments as $payment) : ?>
                    <div class="admin-list-item">
                        <div>
                            <strong>Платеж #<?= (int) $payment['id'] ?></strong>
                            <p>External: <?= htmlspecialchars($payment['external_id'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Пользователь: <?= htmlspecialchars((string) ($payment['user_id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="admin-meta">
                            <span>Сумма: <?= htmlspecialchars((string) $payment['amount'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span>Статус: <?= htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span>Дата: <?= htmlspecialchars($payment['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
</body>
</html>
