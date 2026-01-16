<?php
declare(strict_types=1);

class PushDispatchService
{
    private SegmentService $segmentService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->segmentService = new SegmentService();
        $this->notificationService = new NotificationService();
    }

    public function dispatchDueJobs(): int
    {
        $pdo = Database::getConnection();
        $jobs = $pdo->query(
            'SELECT * FROM admin_push_jobs WHERE sent_at IS NULL AND (scheduled_at IS NULL OR scheduled_at <= NOW()) ORDER BY id ASC'
        )->fetchAll();

        $totalSent = 0;

        foreach ($jobs as $job) {
            $segment = json_decode($job['segment'] ?? '[]', true) ?: [];
            [$where, $params] = $this->segmentService->buildWhere($segment);

            $query = 'SELECT users.id, COALESCE(push_preferences.enabled, 1) AS push_enabled, '
                . 'push_preferences.quiet_start, push_preferences.quiet_end '
                . 'FROM users '
                . 'LEFT JOIN profiles ON profiles.user_id = users.id '
                . 'LEFT JOIN push_preferences ON push_preferences.user_id = users.id '
                . $where;
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll();

            $sent = 0;
            foreach ($users as $user) {
                if (!$this->canSend((int) $user['id'], (int) $user['push_enabled'], $user['quiet_start'], $user['quiet_end'])) {
                    continue;
                }
                $this->notificationService->create(
                    (int) $user['id'],
                    'admin',
                    $job['title'],
                    $job['body'],
                    ['job_id' => $job['id']]
                );
                $sent++;
            }

            $stmt = $pdo->prepare('UPDATE admin_push_jobs SET sent_at = NOW(), stats_sent = :sent WHERE id = :id');
            $stmt->execute(['sent' => $sent, 'id' => $job['id']]);
            $totalSent += $sent;
        }

        return $totalSent;
    }

    private function canSend(int $userId, int $enabled, ?string $quietStart, ?string $quietEnd): bool
    {
        if ($enabled === 0) {
            return false;
        }

        if ($quietStart && $quietEnd) {
            $now = (new DateTime())->format('H:i:s');
            if ($quietStart < $quietEnd) {
                if ($now >= $quietStart && $now <= $quietEnd) {
                    return false;
                }
            } else {
                if ($now >= $quietStart || $now <= $quietEnd) {
                    return false;
                }
            }
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $stmt->execute(['user_id' => $userId]);
        $recent = (int) $stmt->fetchColumn();
        return $recent === 0;
    }
}
