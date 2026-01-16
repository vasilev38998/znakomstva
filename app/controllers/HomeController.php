<?php
declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $pageTitle = APP_NAME;
        $csrf = new CsrfService();
        $csrfToken = $csrf->generateToken();
        $userId = $_SESSION['user_id'] ?? null;
        require __DIR__ . '/../views/home.php';
    }

    public function offline(): void
    {
        $pageTitle = APP_NAME . ' — офлайн';
        require __DIR__ . '/../views/offline.php';
    }
}
