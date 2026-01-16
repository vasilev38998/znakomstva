<?php
declare(strict_types=1);

class EventBus
{
    public function emit(string $event, array $payload = []): void
    {
        // TODO: маршрутизация событий к push и внутренним уведомлениям
    }
}
