<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Аналитика</h1>
    <p>События пользователей и базовые метрики.</p>
</section>

<div class="analytics-card">
    <?php if (empty($events)) : ?>
        <p>Пока нет данных.</p>
    <?php else : ?>
        <?php foreach ($events as $event) : ?>
            <div class="analytics-row">
                <span><?= htmlspecialchars($event['event'], ENT_QUOTES, 'UTF-8') ?></span>
                <strong><?= (int) $event['total'] ?></strong>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
