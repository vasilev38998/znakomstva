<?php
declare(strict_types=1);

class SettingsController
{
    private CsrfService $csrf;

    public function __construct()
    {
        $this->csrf = new CsrfService();
    }

    public function push(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getConnection();
        $userId = (int) $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT * FROM push_preferences WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $prefs = $stmt->fetch();

        if (!$prefs) {
            $pdo->prepare('INSERT INTO push_preferences (user_id) VALUES (:user_id)')->execute(['user_id' => $userId]);
            $stmt = $pdo->prepare('SELECT * FROM push_preferences WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $userId]);
            $prefs = $stmt->fetch();
        }

        $flash = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!$this->csrf->validateToken($token)) {
                $flash = ['type' => 'error', 'message' => 'Неверный CSRF токен.'];
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE push_preferences SET enabled = :enabled, likes = :likes, matches = :matches, messages = :messages, marketing = :marketing, quiet_start = :quiet_start, quiet_end = :quiet_end WHERE user_id = :user_id'
                );
                $stmt->execute([
                    'enabled' => isset($_POST['enabled']) ? 1 : 0,
                    'likes' => isset($_POST['likes']) ? 1 : 0,
                    'matches' => isset($_POST['matches']) ? 1 : 0,
                    'messages' => isset($_POST['messages']) ? 1 : 0,
                    'marketing' => isset($_POST['marketing']) ? 1 : 0,
                    'quiet_start' => $_POST['quiet_start'] ?: '23:00:00',
                    'quiet_end' => $_POST['quiet_end'] ?: '08:00:00',
                    'user_id' => $userId,
                ]);
                $flash = ['type' => 'success', 'message' => 'Настройки обновлены.'];
                $stmt = $pdo->prepare('SELECT * FROM push_preferences WHERE user_id = :user_id LIMIT 1');
                $stmt->execute(['user_id' => $userId]);
                $prefs = $stmt->fetch();
            }
        }

        $pageTitle = APP_NAME . ' — настройки уведомлений';
        $csrfToken = $this->csrf->generateToken();
        require __DIR__ . '/../views/settings/push.php';
    }
}
