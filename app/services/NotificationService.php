<?php
declare(strict_types=1);

class NotificationService
{
    public function create(int $userId, string $type, string $title, string $body, array $payload = []): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (user_id, type, title, body, payload) VALUES (:user_id, :type, :title, :body, :payload)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }
}
