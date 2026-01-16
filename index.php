<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/services/Router.php';
require_once __DIR__ . '/app/controllers/HomeController.php';

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/offline', [HomeController::class, 'offline']);

$router->dispatch($_SERVER['REQUEST_URI']);
