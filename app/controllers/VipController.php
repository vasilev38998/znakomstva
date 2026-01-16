<?php
declare(strict_types=1);

class VipController
{
    private CsrfService $csrf;
    private VipService $vipService;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->vipService = new VipService();
    }

    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $status = $this->vipService->getStatus((int) $_SESSION['user_id']);
        $csrfToken = $this->csrf->generateToken();
        $flash = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $pageTitle = APP_NAME . ' — VIP';
        require __DIR__ . '/../views/vip/index.php';
    }

    public function startTrial(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['csrf_token'] ?? null;
        if (!$this->csrf->validateToken($token)) {
            http_response_code(419);
            echo 'Неверный CSRF токен';
            return;
        }

        $started = $this->vipService->startTrial((int) $_SESSION['user_id']);
        if ($started) {
            $_SESSION['flash_success'] = 'Trial VIP активирован на 24 часа.';
        } else {
            $_SESSION['flash_success'] = 'Trial VIP уже использован.';
        }
        header('Location: /vip');
        exit;
    }
}
