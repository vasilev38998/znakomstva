<?php
declare(strict_types=1);

class PauseController
{
    private CsrfService $csrf;

    public function __construct()
    {
        $this->csrf = new CsrfService();
    }

    public function activate(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            return;
        }

        $days = max(1, (int) ($_POST['days'] ?? 1));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET pause_until = DATE_ADD(NOW(), INTERVAL :days DAY) WHERE id = :id');
        $stmt->execute(['days' => $days, 'id' => (int) $_SESSION['user_id']]);

        header('Location: ' . BASE_URL . 'pause');
        exit;
    }
}
