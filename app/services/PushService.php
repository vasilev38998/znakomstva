<?php
declare(strict_types=1);

class PushService
{
    public function subscribe(array $subscription): void
    {
        // TODO: сохранить подписку в БД
    }

    public function send(array $subscription, array $payload): void
    {
        // TODO: отправить web push через VAPID
    }

    public function invalidate(string $endpoint): void
    {
        // TODO: удалить невалидную подписку
    }
}
