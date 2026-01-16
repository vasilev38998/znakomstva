<?php
declare(strict_types=1);

class AnalyticsReportController
{
    public function index(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pdo = Database::getConnection();
        $events = $pdo->query('SELECT event, COUNT(*) as total FROM analytics_events GROUP BY event ORDER BY total DESC')->fetchAll();
        $pageTitle = APP_NAME . ' — аналитика';
        require __DIR__ . '/../views/analytics/index.php';
    }
}
