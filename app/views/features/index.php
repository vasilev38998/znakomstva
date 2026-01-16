<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Killer-фичи</h1>
    <p>Слепой матч, искра дня, квесты и ночная комната.</p>
</section>

<section class="features-grid">
    <div class="feature-card">
        <h3>Слепой матч</h3>
        <p>Случайные совпадения без фото первые 60 секунд.</p>
    </div>
    <div class="feature-card">
        <h3>Квест-знакомство</h3>
        <p>Небольшие миссии для мягкого старта общения.</p>
    </div>
    <div class="feature-card">
        <h3>Искра дня</h3>
        <p>Один человек в день, который совпадает по настроению.</p>
    </div>
    <div class="feature-card">
        <h3>Ночная комната</h3>
        <p>Закрытый режим с фильтрами доверия.</p>
    </div>
    <div class="feature-card">
        <h3>7 секунд</h3>
        <p>Режим быстрых знакомств с таймером.</p>
    </div>
    <div class="feature-card">
        <h3>Скрытая симпатия</h3>
        <p>Анонимные сигналы для вовлечения.</p>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
