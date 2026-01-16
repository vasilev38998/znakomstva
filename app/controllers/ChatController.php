<?php
declare(strict_types=1);

class ChatController
{
    private CsrfService $csrf;
    private MessageService $messageService;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->messageService = new MessageService();
    }

    public function show(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $matchId = (int) ($_GET['match_id'] ?? 0);
        if ($matchId <= 0) {
            header('Location: /');
            exit;
        }

        $messages = $this->messageService->getMessages($matchId);
        $pageTitle = APP_NAME . ' — чат';
        $csrfToken = $this->csrf->generateToken();
        require __DIR__ . '/../views/chat/show.php';
    }

    public function send(): void
    {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo 'Требуется вход';
            return;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            return;
        }

        $matchId = (int) ($_POST['match_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        if ($matchId <= 0 || $body === '') {
            header('Location: /chat?match_id=' . $matchId);
            exit;
        }

        $this->messageService->sendMessage($matchId, (int) $_SESSION['user_id'], $body);
        header('Location: /chat?match_id=' . $matchId);
        exit;
    }
}
