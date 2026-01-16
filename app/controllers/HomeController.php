<?php
declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $pageTitle = APP_NAME;
        require __DIR__ . '/../views/home.php';
    }

    public function offline(): void
    {
        $pageTitle = APP_NAME . ' — офлайн';
        require __DIR__ . '/../views/offline.php';
    }
}
