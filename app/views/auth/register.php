<?php
declare(strict_types=1);

ob_start();
?>
<section class="auth">
    <div class="auth-card">
        <h1>Регистрация</h1>
        <p>Создайте профиль и откройте новые сценарии знакомств.</p>
        <?php if (!empty($error)) : ?>
            <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="flash success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($_SESSION['verification_link'])) : ?>
                <a class="text-link" href="<?= htmlspecialchars($_SESSION['verification_link'], ENT_QUOTES, 'UTF-8') ?>">Ссылка подтверждения (demo)</a>
                <?php unset($_SESSION['verification_link']); ?>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label>
                Имя
                <input type="text" name="name" required>
            </label>
            <label>
                Email
                <input type="email" name="email" required>
            </label>
            <label>
                Пароль
                <input type="password" name="password" required>
            </label>
            <button class="primary-button" type="submit">Создать аккаунт</button>
        </form>
        <a class="text-link" href="<?= BASE_URL ?>login">Уже есть аккаунт? Вход</a>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
