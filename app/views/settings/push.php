<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Настройки push</h1>
    <p>Выберите, какие уведомления получать и когда включать тихий режим.</p>
</section>

<?php if (!empty($flash)) : ?>
    <div class="flash <?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<form class="settings-card" method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <label class="switch">
        <input type="checkbox" name="enabled" <?= (int) $prefs['enabled'] === 1 ? 'checked' : '' ?>>
        <span>Получать уведомления</span>
    </label>
    <label class="switch">
        <input type="checkbox" name="likes" <?= (int) $prefs['likes'] === 1 ? 'checked' : '' ?>>
        <span>Лайки</span>
    </label>
    <label class="switch">
        <input type="checkbox" name="matches" <?= (int) $prefs['matches'] === 1 ? 'checked' : '' ?>>
        <span>Матчи</span>
    </label>
    <label class="switch">
        <input type="checkbox" name="messages" <?= (int) $prefs['messages'] === 1 ? 'checked' : '' ?>>
        <span>Сообщения</span>
    </label>
    <label class="switch">
        <input type="checkbox" name="marketing" <?= (int) $prefs['marketing'] === 1 ? 'checked' : '' ?>>
        <span>Новости и предложения</span>
    </label>
    <div class="form-grid">
        <label>
            Тихий режим с
            <input type="time" name="quiet_start" value="<?= htmlspecialchars($prefs['quiet_start'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Тихий режим до
            <input type="time" name="quiet_end" value="<?= htmlspecialchars($prefs['quiet_end'], ENT_QUOTES, 'UTF-8') ?>">
        </label>
    </div>
    <button class="primary-button" type="submit">Сохранить</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
