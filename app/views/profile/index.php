<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Профиль</h1>
    <p>Ваши данные, настроение и цель знакомства.</p>
</section>

<?php if (!empty($flash)) : ?>
    <div class="flash success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form class="profile-card" method="post" action="/profile/update">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label>
        Имя
        <input type="text" name="name" value="<?= htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
    </label>
    <label>
        Возраст
        <input type="number" name="age" value="<?= htmlspecialchars((string) ($profile['age'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    </label>
    <label>
        Город
        <input type="text" name="city" value="<?= htmlspecialchars($profile['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </label>
    <label>
        Цель
        <input type="text" name="goal" value="<?= htmlspecialchars($profile['goal'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </label>
    <label>
        О себе
        <textarea name="about" rows="3"><?= htmlspecialchars($profile['about'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </label>
    <label>
        Настроение
        <input type="text" name="mood" value="<?= htmlspecialchars($profile['mood'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </label>
    <button class="primary-button" type="submit">Сохранить</button>
</form>

<div class="profile-card">
    <h2>Фото</h2>
    <?php if (!empty($photos)) : ?>
        <div class="photo-grid">
            <?php foreach ($photos as $photo) : ?>
                <img src="<?= htmlspecialchars($photo['path'], ENT_QUOTES, 'UTF-8') ?>" alt="Фото">
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p>Пока нет загруженных фото.</p>
    <?php endif; ?>
    <form method="post" action="/profile/photo" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <input type="file" name="photo" accept="image/*" required>
        <button class="secondary-button" type="submit">Загрузить</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
