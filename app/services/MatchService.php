<?php
declare(strict_types=1);

class MatchService
{
    public function handleReaction(int $fromUserId, int $toUserId, string $type): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO reactions (from_user_id, to_user_id, type) VALUES (:from_id, :to_id, :type)');
        $stmt->execute([
            'from_id' => $fromUserId,
            'to_id' => $toUserId,
            'type' => $type,
        ]);

        if ($type === 'like' || $type === 'super') {
            $stmt = $pdo->prepare(
                'SELECT id FROM reactions WHERE from_user_id = :to_id AND to_user_id = :from_id AND type IN ("like","super") LIMIT 1'
            );
            $stmt->execute([
                'to_id' => $fromUserId,
                'from_id' => $toUserId,
            ]);
            $reverse = $stmt->fetch();
            if ($reverse) {
                $stmt = $pdo->prepare('INSERT INTO matches (user_one_id, user_two_id) VALUES (:one, :two)');
                $stmt->execute([
                    'one' => min($fromUserId, $toUserId),
                    'two' => max($fromUserId, $toUserId),
                ]);
                return ['match' => true];
            }
        }

        return ['match' => false];
    }
}
