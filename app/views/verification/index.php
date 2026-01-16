<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Селфи-верификация</h1>
    <p>Загрузите селфи с код-фразой, чтобы получить значок доверия.</p>
</section>

<?php if (!empty($flash)) : ?>
    <div class="flash success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="verification-card">
    <p>Статус: <?= htmlspecialchars($verification['status'] ?? 'нет заявки', ENT_QUOTES, 'UTF-8') ?></p>
    <p>Код-фраза: <?= htmlspecialchars($verification['code_phrase'] ?? 'не указана', ENT_QUOTES, 'UTF-8') ?></p>
</div>

<form class="verification-card" method="post" action="<?= BASE_URL ?>verification/submit">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label>
        Код-фраза
        <input type="text" name="code_phrase" placeholder="Например: Я подтверждаю" required>
    </label>
    <button class="primary-button" type="submit">Отправить</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
