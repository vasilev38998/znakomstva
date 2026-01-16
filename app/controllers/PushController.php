<?php
declare(strict_types=1);

class PushController
{
    private CsrfService $csrf;

    public function __construct()
    {
        $this->csrf = new CsrfService();
    }

    public function subscribe(): void
    {
        $this->respondJson($this->handleSubscription(true));
    }

    public function unsubscribe(): void
    {
        $this->respondJson($this->handleSubscription(false));
    }

    private function handleSubscription(bool $active): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            return ['status' => 'error', 'message' => 'CSRF токен недействителен'];
        }

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            return ['status' => 'error', 'message' => 'Требуется вход'];
        }

        $subscription = $input['subscription'] ?? null;
        if (!is_array($subscription) || empty($subscription['endpoint'])) {
            http_response_code(422);
            return ['status' => 'error', 'message' => 'Неверный формат подписки'];
        }

        $pdo = Database::getConnection();
        if ($active) {
            $stmt = $pdo->prepare('REPLACE INTO push_subscriptions (user_id, endpoint, p256dh, auth) VALUES (:user_id, :endpoint, :p256dh, :auth)');
            $stmt->execute([
                'user_id' => (int) $_SESSION['user_id'],
                'endpoint' => $subscription['endpoint'],
                'p256dh' => $subscription['keys']['p256dh'] ?? '',
                'auth' => $subscription['keys']['auth'] ?? '',
            ]);
            return ['status' => 'ok'];
        }

        $stmt = $pdo->prepare('DELETE FROM push_subscriptions WHERE endpoint = :endpoint AND user_id = :user_id');
        $stmt->execute([
            'endpoint' => $subscription['endpoint'],
            'user_id' => (int) $_SESSION['user_id'],
        ]);

        return ['status' => 'ok'];
    }

    private function respondJson(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
