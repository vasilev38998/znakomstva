<?php
declare(strict_types=1);

ob_start();
?>
<section class="auth">
    <div class="auth-card">
        <h1>Вход</h1>
        <p>Вернитесь к своим матчам и чатам.</p>
        <?php if (!empty($error)) : ?>
            <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label>
                Email
                <input type="email" name="email" required>
            </label>
            <label>
                Пароль
                <input type="password" name="password" required>
            </label>
            <button class="primary-button" type="submit">Войти</button>
        </form>
        <a class="text-link" href="<?= BASE_URL ?>register">Нет аккаунта? Регистрация</a>
    </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
