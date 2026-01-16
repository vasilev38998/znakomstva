<?php
declare(strict_types=1);

class TelegramController
{
    public function auth(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo 'Invalid payload';
            return;
        }

        $signature = $_SERVER['HTTP_X_TELEGRAM_SIGNATURE'] ?? '';
        if (TELEGRAM_WEBHOOK_SECRET !== '' && !$this->verifySignature($payload, $signature)) {
            http_response_code(401);
            echo 'Invalid signature';
            return;
        }

        echo json_encode(['status' => 'ok']);
    }

    private function verifySignature(array $payload, string $signature): bool
    {
        $data = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $hash = hash_hmac('sha256', $data ?: '', TELEGRAM_WEBHOOK_SECRET);
        return hash_equals($hash, $signature);
    }
}
