<?php
declare(strict_types=1);

ob_start();
?>
<header class="top-bar">
    <div class="logo">ZNAKOMSTVA</div>
    <div class="top-actions">
        <?php if ($userId) : ?>
            <form method="post" action="/logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="ghost-button" type="submit">Выйти</button>
            </form>
        <?php else : ?>
            <a class="ghost-button" href="/login">Войти</a>
        <?php endif; ?>
    </div>
</header>

<section class="hero">
    <h1>Живые знакомства, которые чувствуются.</h1>
    <p>Эмоции, доверие и интерактив. Минимум текста, максимум момента.</p>
    <div class="hero-actions">
        <a class="primary-button" href="/register">Создать профиль</a>
        <button class="secondary-button" type="button">Посмотреть демо</button>
    </div>
    <div class="hero-actions">
        <button class="ghost-button" type="button" data-push-subscribe>Включить push</button>
        <button class="ghost-button" type="button" data-push-unsubscribe>Отключить push</button>
    </div>
</section>

<section class="cards">
    <div class="card">
        <div class="card-header">
            <span class="card-status">● online</span>
            <span class="card-tag">Верифицирован</span>
        </div>
        <div class="card-body">
            <h2>Искра дня</h2>
            <p>Поймай человека, который совпал с твоим настроением.</p>
        </div>
        <div class="card-actions">
            <button class="icon-button" type="button">💫</button>
            <button class="icon-button" type="button">❤️</button>
            <button class="icon-button" type="button">⭐</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-status">● 2 км</span>
            <span class="card-tag">Ночная комната</span>
        </div>
        <div class="card-body">
            <h2>7 секунд</h2>
            <p>Режим быстрых знакомств с таймером и эмоциями.</p>
        </div>
        <div class="card-actions">
            <button class="icon-button" type="button">🔥</button>
            <button class="icon-button" type="button">💬</button>
            <button class="icon-button" type="button">⏳</button>
        </div>
    </div>
</section>

<section class="features">
    <div class="feature">
        <h3>Уровень доверия</h3>
        <p>Селфи-верификация и прозрачный рейтинг надежности.</p>
    </div>
    <div class="feature">
        <h3>Интерактивные сценарии</h3>
        <p>Квесты, слепые матчи и тепловая лента интересов.</p>
    </div>
    <div class="feature">
        <h3>Умные уведомления</h3>
        <p>Push-центр с лимитами, тихим режимом и приоритетами.</p>
    </div>
</section>

<nav class="bottom-nav">
    <button class="nav-item" type="button">Лента</button>
    <button class="nav-item" type="button">Матчи</button>
    <button class="nav-item active" type="button">Дом</button>
    <button class="nav-item" type="button">Чаты</button>
    <button class="nav-item" type="button">Профиль</button>
</nav>
<script>
    window.APP_CONFIG = {
        csrfToken: "<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>",
        vapidPublicKey: "<?= htmlspecialchars(VAPID_PUBLIC_KEY, ENT_QUOTES, 'UTF-8') ?>"
    };
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
