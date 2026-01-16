<?php
declare(strict_types=1);

class MessageService
{
    public function sendMessage(int $matchId, int $senderId, string $message): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO messages (match_id, sender_id, body) VALUES (:match_id, :sender_id, :body)');
        $stmt->execute([
            'match_id' => $matchId,
            'sender_id' => $senderId,
            'body' => $message,
        ]);
    }

    public function getMessages(int $matchId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT sender_id, body, created_at FROM messages WHERE match_id = :match_id ORDER BY id ASC');
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetchAll();
    }
}
