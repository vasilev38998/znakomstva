<?php
declare(strict_types=1);

class SegmentService
{
    public function buildWhere(array $segment): array
    {
        $clauses = [];
        $params = [];

        if (!empty($segment['status'])) {
            $clauses[] = 'users.status = :status';
            $params['status'] = $segment['status'];
        }

        if (!empty($segment['vip'])) {
            $clauses[] = $segment['vip'] === 'yes'
                ? 'users.vip_until IS NOT NULL AND users.vip_until > NOW()'
                : '(users.vip_until IS NULL OR users.vip_until <= NOW())';
        }

        if (!empty($segment['trial'])) {
            $clauses[] = $segment['trial'] === 'yes'
                ? 'users.trial_until IS NOT NULL AND users.trial_until > NOW()'
                : '(users.trial_until IS NULL OR users.trial_until <= NOW())';
        }

        if (!empty($segment['online'])) {
            $clauses[] = $segment['online'] === 'yes'
                ? 'users.last_active_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
                : '(users.last_active_at IS NULL OR users.last_active_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE))';
        }

        if (!empty($segment['city'])) {
            $clauses[] = 'profiles.city = :city';
            $params['city'] = $segment['city'];
        }

        if (!empty($segment['gender'])) {
            $clauses[] = 'profiles.gender = :gender';
            $params['gender'] = $segment['gender'];
        }

        if (!empty($segment['goal'])) {
            $clauses[] = 'profiles.goal = :goal';
            $params['goal'] = $segment['goal'];
        }

        if (!empty($segment['trust_min'])) {
            $clauses[] = 'profiles.trust_score >= :trust_min';
            $params['trust_min'] = (int) $segment['trust_min'];
        }

        if (!empty($segment['last_active_days'])) {
            $clauses[] = 'users.last_active_at >= DATE_SUB(NOW(), INTERVAL :days DAY)';
            $params['days'] = (int) $segment['last_active_days'];
        }

        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        return [$where, $params];
    }
}
