<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Уведомления</h1>
    <p>Ваши события и сигналы в одном месте.</p>
</section>

<section class="notifications">
    <?php if (empty($items)) : ?>
        <div class="feature">Пока нет уведомлений.</div>
    <?php else : ?>
        <?php foreach ($items as $item) : ?>
            <div class="notification-item">
                <strong><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                <p><?= htmlspecialchars($item['body'], ENT_QUOTES, 'UTF-8') ?></p>
                <span><?= htmlspecialchars($item['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
