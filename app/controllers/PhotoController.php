<?php
declare(strict_types=1);

class PhotoController
{
    private CsrfService $csrf;
    private PhotoService $photoService;

    public function __construct()
    {
        $this->csrf = new CsrfService();
        $this->photoService = new PhotoService();
    }

    public function upload(): void
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

        $file = $_FILES['photo'] ?? null;
        if (!$file) {
            header('Location: /profile');
            exit;
        }

        $this->photoService->store((int) $_SESSION['user_id'], $file);
        header('Location: /profile');
        exit;
    }
}
