<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>VIP-доступ</h1>
    <p>Больше совпадений, приоритет и расширенные сценарии.</p>
</section>

<?php if (!empty($flash)) : ?>
    <div class="flash success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="vip-card">
    <h2>Ваш статус</h2>
    <p>Trial до: <?= htmlspecialchars($status['trial_until'] ?? 'не активирован', ENT_QUOTES, 'UTF-8') ?></p>
    <p>VIP до: <?= htmlspecialchars($status['vip_until'] ?? 'не активирован', ENT_QUOTES, 'UTF-8') ?></p>
    <form method="post" action="/vip/trial">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <button class="primary-button" type="submit">Активировать trial</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
