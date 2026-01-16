<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/services/Database.php';
require_once __DIR__ . '/../app/services/SegmentService.php';
require_once __DIR__ . '/../app/services/NotificationService.php';
require_once __DIR__ . '/../app/services/PushService.php';
require_once __DIR__ . '/../app/services/PushDispatchService.php';

$dispatcher = new PushDispatchService();
$sent = $dispatcher->dispatchDueJobs();

echo 'Отправлено уведомлений: ' . $sent . PHP_EOL;
