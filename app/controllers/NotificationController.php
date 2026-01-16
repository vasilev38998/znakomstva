<?php
declare(strict_types=1);

class NotificationController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT title, body, created_at FROM notifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 30');
        $stmt->execute(['user_id' => (int) $_SESSION['user_id']]);
        $items = $stmt->fetchAll();

        $pageTitle = APP_NAME . ' — уведомления';
        require __DIR__ . '/../views/notifications/index.php';
    }

    public function delivered(): void
    {
        $this->track('delivered');
    }

    public function clicked(): void
    {
        $this->track('clicked');
    }

    private function track(string $type): void
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            $this->respondJson(['status' => 'error', 'message' => 'Требуется вход']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = (int) ($input['notification_id'] ?? 0);
        if ($notificationId <= 0) {
            http_response_code(422);
            $this->respondJson(['status' => 'error', 'message' => 'notification_id обязателен']);
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $updated = $type === 'clicked'
            ? $this->notificationService->markClicked($notificationId, $userId)
            : $this->notificationService->markDelivered($notificationId, $userId);

        $this->respondJson(['status' => $updated ? 'ok' : 'ignored']);
    }

    private function respondJson(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
