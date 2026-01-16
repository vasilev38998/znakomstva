<?php
declare(strict_types=1);

class VipService
{
    public function startTrial(int $userId, int $hours = 24): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT trial_until FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        if (!empty($row['trial_until'])) {
            return false;
        }

        $stmt = $pdo->prepare('UPDATE users SET trial_until = DATE_ADD(NOW(), INTERVAL :hours HOUR) WHERE id = :id');
        $stmt->execute(['hours' => $hours, 'id' => $userId]);
        return true;
    }

    public function grantVip(int $userId, int $days): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET vip_until = DATE_ADD(NOW(), INTERVAL :days DAY) WHERE id = :id');
        $stmt->execute(['days' => $days, 'id' => $userId]);
    }

    public function getStatus(int $userId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT vip_until, trial_until FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        return [
            'vip_until' => $row['vip_until'] ?? null,
            'trial_until' => $row['trial_until'] ?? null,
        ];
    }
}
