<?php
declare(strict_types=1);

class NotificationService
{
    public function create(int $userId, string $type, string $title, string $body, array $payload = []): int
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
        return (int) $pdo->lastInsertId();
    }

    public function markDelivered(int $notificationId, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT delivered_at, payload FROM notifications WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
        $row = $stmt->fetch();
        if (!$row || $row['delivered_at']) {
            return false;
        }

        $pdo->beginTransaction();
        $update = $pdo->prepare('UPDATE notifications SET delivered_at = NOW() WHERE id = :id');
        $update->execute(['id' => $notificationId]);
        $this->touchAdminStats($pdo, $row['payload'], 'stats_delivered');
        $pdo->commit();

        return true;
    }

    public function markClicked(int $notificationId, int $userId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT delivered_at, clicked_at, payload FROM notifications WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
        $row = $stmt->fetch();
        if (!$row || $row['clicked_at']) {
            return false;
        }

        $pdo->beginTransaction();
        $updates = ['id' => $notificationId];
        $query = 'UPDATE notifications SET clicked_at = NOW()';
        if (!$row['delivered_at']) {
            $query .= ', delivered_at = NOW()';
        }
        $query .= ' WHERE id = :id';
        $update = $pdo->prepare($query);
        $update->execute($updates);
        $this->touchAdminStats($pdo, $row['payload'], 'stats_clicked');
        if (!$row['delivered_at']) {
            $this->touchAdminStats($pdo, $row['payload'], 'stats_delivered');
        }
        $pdo->commit();

        return true;
    }

    private function touchAdminStats(PDO $pdo, ?string $payload, string $field): void
    {
        if (!$payload) {
            return;
        }
        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['job_id'])) {
            return;
        }
        $stmt = $pdo->prepare("UPDATE admin_push_jobs SET {$field} = {$field} + 1 WHERE id = :id");
        $stmt->execute(['id' => (int) $data['job_id']]);
    }
}
