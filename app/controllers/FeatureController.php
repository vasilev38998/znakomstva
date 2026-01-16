<?php
declare(strict_types=1);

class FeatureController
{
    public function index(): void
    {
        $pageTitle = APP_NAME . ' — фишки';
        require __DIR__ . '/../views/features/index.php';
    }

    public function pause(): void
    {
        $pageTitle = APP_NAME . ' — пауза';
        require __DIR__ . '/../views/features/pause.php';
    }
}
