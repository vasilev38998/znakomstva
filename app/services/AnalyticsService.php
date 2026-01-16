<?php
declare(strict_types=1);

class AnalyticsService
{
    public function track(int $userId, string $event, array $payload = []): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO analytics_events (user_id, event, payload) VALUES (:user_id, :event, :payload)');
        $stmt->execute([
            'user_id' => $userId,
            'event' => $event,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function markActive(int $userId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET last_active_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }
}
