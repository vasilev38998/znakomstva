<?php
declare(strict_types=1);

class AnalyticsController
{
    private CsrfService $csrf;
    private RateLimiter $rateLimiter;
    private AnalyticsService $analytics;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->rateLimiter = new RateLimiter(__DIR__ . '/../../storage');
        $this->analytics = new AnalyticsService();
    }

    public function ping(): void
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            $this->respondJson(['status' => 'error', 'message' => 'Требуется вход']);
            return;
        }

        if ($this->rateLimiter->tooManyAttempts('ping:' . $this->ip(), 30, 60)) {
            http_response_code(429);
            $this->respondJson(['status' => 'error', 'message' => 'Слишком часто']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            $this->respondJson(['status' => 'error', 'message' => 'CSRF токен недействителен']);
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $this->analytics->markActive($userId);
        $this->analytics->track($userId, 'activity.ping');
        $this->respondJson(['status' => 'ok']);
    }

    private function respondJson(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
