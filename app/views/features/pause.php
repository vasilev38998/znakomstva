<?php
declare(strict_types=1);

ob_start();
?>
<section class="pause">
    <h1>Пауза</h1>
    <p>Сделайте перерыв. Мы сохраним ваши матчи и чаты.</p>
    <button class="primary-button" type="button" onclick="window.location='/'">Вернуться</button>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
