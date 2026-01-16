<?php
declare(strict_types=1);

class NotificationController
{
    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT title, body, created_at FROM notifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 30');
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $items = $stmt->fetchAll();

        $pageTitle = APP_NAME . ' — уведомления';
        require __DIR__ . '/../views/notifications/index.php';
    }
}
