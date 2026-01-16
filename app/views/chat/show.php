<?php
declare(strict_types=1);

ob_start();
?>
<section class="hero">
    <h1>Чат</h1>
    <p>Диалог внутри матча.</p>
</section>

<section class="chat">
    <?php if (empty($messages)) : ?>
        <div class="feature">Сообщений пока нет.</div>
    <?php else : ?>
        <?php foreach ($messages as $message) : ?>
            <div class="chat-message <?= (int) $message['sender_id'] === (int) $_SESSION['user_id'] ? 'mine' : '' ?>">
                <p><?= htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8') ?></p>
                <span><?= htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<form class="chat-form" method="post" action="<?= BASE_URL ?>chat/send">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="match_id" value="<?= htmlspecialchars((string) ($_GET['match_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <input type="text" name="body" placeholder="Сообщение" required>
    <button class="primary-button" type="submit">Отправить</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
