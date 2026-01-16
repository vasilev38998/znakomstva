<?php
declare(strict_types=1);

class InteractionController
{
    private CsrfService $csrf;
    private MatchService $matchService;
    private EventBus $eventBus;
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->matchService = new MatchService();
        $this->eventBus = new EventBus();
        $this->rateLimiter = new RateLimiter(__DIR__ . '/../../storage');
    }

    public function react(): void
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            $this->respondJson(['status' => 'error', 'message' => 'Требуется вход']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            $this->respondJson(['status' => 'error', 'message' => 'CSRF токен недействителен']);
            return;
        }

        $targetId = (int) ($input['target_id'] ?? 0);
        $type = $input['type'] ?? 'like';
        if ($targetId <= 0 || !in_array($type, ['like', 'dislike', 'super'], true)) {
            http_response_code(422);
            $this->respondJson(['status' => 'error', 'message' => 'Неверные параметры']);
            return;
        }

        if ($this->rateLimiter->tooManyAttempts('react:' . ($_SESSION['user_id'] ?? '0'), 20, 60)) {
            http_response_code(429);
            $this->respondJson(['status' => 'error', 'message' => 'Слишком много реакций']);
            return;
        }

        $result = $this->matchService->handleReaction((int) $_SESSION['user_id'], $targetId, $type);
        if ($type === 'like') {
            $this->eventBus->emit('like.new', [], $targetId);
        }
        if ($type === 'super') {
            $this->eventBus->emit('super.new', [], $targetId);
        }
        if ($result['match']) {
            $this->eventBus->emit('match.new', [], $targetId);
            $this->eventBus->emit('match.new', [], (int) $_SESSION['user_id']);
        }

        $this->respondJson(['status' => 'ok', 'match' => $result['match']]);
    }

    private function respondJson(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
