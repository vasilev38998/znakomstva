<?php
declare(strict_types=1);

ob_start();
?>
<section class="offline">
    <h1>Вы офлайн</h1>
    <p>Базовые экраны доступны. Как только связь появится — синхронизируемся.</p>
    <button class="primary-button" type="button" onclick="window.location='<?= BASE_URL ?>'">Обновить</button>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
