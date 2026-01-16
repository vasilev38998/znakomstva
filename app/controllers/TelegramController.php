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

        echo json_encode(['status' => 'ok']);
    }
}
