<?php
declare(strict_types=1);

ob_start();
?>
<section class="pause">
    <h1>Пауза</h1>
    <p>Сделайте перерыв. Мы сохраним ваши матчи и чаты.</p>
    <form method="post" action="<?= BASE_URL ?>pause/activate">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((new CsrfService())->generateToken(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="number" name="days" min="1" max="30" value="7">
        <button class="secondary-button" type="submit">Поставить на паузу</button>
    </form>
    <button class="primary-button" type="button" onclick="window.location='<?= BASE_URL ?>'">Вернуться</button>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
