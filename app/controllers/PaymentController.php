<?php
declare(strict_types=1);

class PaymentController
{
    public function webhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo 'Invalid payload';
            return;
        }

        $externalId = $payload['payment_id'] ?? null;
        if (!$externalId) {
            http_response_code(422);
            echo 'Missing payment_id';
            return;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM payments WHERE external_id = :external_id');
        $stmt->execute(['external_id' => $externalId]);
        if ($stmt->fetch()) {
            echo 'OK';
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO payments (external_id, user_id, amount, status, payload) VALUES (:external_id, :user_id, :amount, :status, :payload)');
        $stmt->execute([
            'external_id' => $externalId,
            'user_id' => $payload['user_id'] ?? null,
            'amount' => $payload['amount'] ?? 0,
            'status' => $payload['status'] ?? 'pending',
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        echo 'OK';
    }
}
