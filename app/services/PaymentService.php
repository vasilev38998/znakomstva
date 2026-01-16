<?php
declare(strict_types=1);

class PaymentService
{
    public function handleWebhook(array $payload): void
    {
        $externalId = $payload['payment_id'] ?? null;
        if (!$externalId) {
            throw new InvalidArgumentException('Missing payment_id');
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM payments WHERE external_id = :external_id');
        $stmt->execute(['external_id' => $externalId]);
        if ($stmt->fetch()) {
            return;
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $amount = (float) ($payload['amount'] ?? 0);
        $status = $payload['status'] ?? 'pending';

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO payments (external_id, user_id, amount, status, payload) VALUES (:external_id, :user_id, :amount, :status, :payload)');
        $stmt->execute([
            'external_id' => $externalId,
            'user_id' => $userId ?: null,
            'amount' => $amount,
            'status' => $status,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        if ($status === 'paid' && $userId > 0) {
            $stmt = $pdo->prepare('INSERT INTO wallets (user_id, balance) VALUES (:user_id, :balance) ON DUPLICATE KEY UPDATE balance = balance + :balance');
            $stmt->execute(['user_id' => $userId, 'balance' => $amount]);

            $stmt = $pdo->prepare('INSERT INTO ledger_entries (user_id, amount, type, source, reference) VALUES (:user_id, :amount, :type, :source, :reference)');
            $stmt->execute([
                'user_id' => $userId,
                'amount' => $amount,
                'type' => 'credit',
                'source' => 'payment',
                'reference' => $externalId,
            ]);
        }

        $pdo->commit();
    }
}
