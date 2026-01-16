<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/services/Router.php';
require_once __DIR__ . '/app/services/Database.php';
require_once __DIR__ . '/app/services/CsrfService.php';
require_once __DIR__ . '/app/services/RateLimiter.php';
require_once __DIR__ . '/app/controllers/HomeController.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/PushController.php';
require_once __DIR__ . '/app/controllers/NotificationController.php';
require_once __DIR__ . '/app/controllers/SettingsController.php';

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/offline', [HomeController::class, 'offline']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'handleRegister']);
$router->get('/verify', [AuthController::class, 'verify']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->post('/api/push/subscribe', [PushController::class, 'subscribe']);
$router->post('/api/push/unsubscribe', [PushController::class, 'unsubscribe']);
$router->get('/notifications', [NotificationController::class, 'index']);
$router->get('/settings/push', [SettingsController::class, 'push']);
$router->post('/settings/push', [SettingsController::class, 'push']);

$router->dispatch($_SERVER['REQUEST_URI']);
