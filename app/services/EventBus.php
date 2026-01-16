<?php
declare(strict_types=1);

class EventBus
{
    private NotificationService $notificationService;
    private PushService $pushService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->pushService = new PushService();
    }

    public function emit(string $event, array $payload, int $userId): void
    {
        $definition = $this->definitions()[$event] ?? null;
        if (!$definition) {
            return;
        }

        if (!$this->isAllowed($userId, $definition['preference'])) {
            return;
        }

        $title = $definition['title'];
        $body = $definition['body'];

        if (!empty($payload['title'])) {
            $title = $payload['title'];
        }
        if (!empty($payload['body'])) {
            $body = $payload['body'];
        }

        $notificationId = $this->notificationService->create($userId, $definition['type'], $title, $body, $payload);
        $payload['notification_id'] = $notificationId;
        $this->pushService->sendToUser($userId, [
            'title' => $title,
            'body' => $body,
            'data' => $payload,
        ]);
    }

    private function definitions(): array
    {
        return [
            'like.new' => [
                'type' => 'like',
                'title' => '❤️ Новый лайк',
                'body' => 'Кто-то поставил вам лайк.',
                'preference' => 'likes',
            ],
            'match.new' => [
                'type' => 'match',
                'title' => '🔥 Взаимный матч',
                'body' => 'У вас новый матч. Напишите первым!',
                'preference' => 'matches',
            ],
            'message.new' => [
                'type' => 'message',
                'title' => '💬 Новое сообщение',
                'body' => 'Проверьте чат — вас ждут.',
                'preference' => 'messages',
            ],
            'super.new' => [
                'type' => 'super',
                'title' => '⭐ Супер-интерес',
                'body' => 'Кто-то отправил вам супер-интерес.',
                'preference' => 'matches',
            ],
            'match.expiring' => [
                'type' => 'expiring',
                'title' => '⏳ Матч скоро исчезнет',
                'body' => 'Успейте написать до исчезновения.',
                'preference' => 'matches',
            ],
            'night.open' => [
                'type' => 'night',
                'title' => '🌙 Ночная комната открыта',
                'body' => 'Заходите в закрытый вечерний режим.',
                'preference' => 'marketing',
            ],
            'vip.trial_expiring' => [
                'type' => 'trial',
                'title' => '💎 Trial VIP заканчивается',
                'body' => 'Продлите доступ к премиум-функциям.',
                'preference' => 'marketing',
            ],
            'vip.expiring' => [
                'type' => 'vip',
                'title' => '💎 VIP скоро истекает',
                'body' => 'Проверьте статус подписки.',
                'preference' => 'marketing',
            ],
        ];
    }

    private function isAllowed(int $userId, string $preference): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT enabled, likes, matches, messages, marketing FROM push_preferences WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $prefs = $stmt->fetch();

        if (!$prefs) {
            return true;
        }

        if ((int) $prefs['enabled'] === 0) {
            return false;
        }

        if (!isset($prefs[$preference])) {
            return true;
        }

        return (int) $prefs[$preference] === 1;
    }
}
