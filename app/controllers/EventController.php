<?php
declare(strict_types=1);

class EventController
{
    private CsrfService $csrf;
    private RateLimiter $rateLimiter;
    private EventBus $eventBus;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->rateLimiter = new RateLimiter(__DIR__ . '/../../storage');
        $this->eventBus = new EventBus();
    }

    public function emit(): void
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            $this->respondJson(['status' => 'error', 'message' => 'Требуется вход']);
            return;
        }

        if ($this->rateLimiter->tooManyAttempts('event:' . $this->ip(), 10, 60)) {
            http_response_code(429);
            $this->respondJson(['status' => 'error', 'message' => 'Слишком много событий.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            $this->respondJson(['status' => 'error', 'message' => 'CSRF токен недействителен']);
            return;
        }

        $event = $input['event'] ?? '';
        $payload = is_array($input['payload'] ?? null) ? $input['payload'] : [];

        if ($event === '') {
            http_response_code(422);
            $this->respondJson(['status' => 'error', 'message' => 'Событие не указано']);
            return;
        }

        $this->eventBus->emit($event, $payload, (int) $_SESSION['user_id']);
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
