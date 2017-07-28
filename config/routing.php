<?php

use Dlx\Security\Controller\LoginController;
use Dlx\Security\Controller\LogoutController;
use Dlx\Security\Controller\RegisterController;

$cratePrefix = 'dlx.security';
$mount = $configProvider->get('crates.'.$cratePrefix.'.mount', '/dlx/security');

$app->mount($mount, function ($app) use ($cratePrefix) {
    $app->get('/login', [LoginController::class, 'read'])->bind($cratePrefix.'.login');
    $app->match('/logout', [LogoutController::class, 'write'])->bind($cratePrefix.'.logout');
    $app->match('/authenticate', [LoginController::class, 'write'])->bind($cratePrefix.'.authenticate');
    $app->get('/register', [RegisterController::class, 'read'])->bind($cratePrefix.'.register');
    $app->post('/register', [RegisterController::class, 'write']);
    $app->get('/activate', [RegisterController::class, 'activate'])->bind($cratePrefix.'.activate');
    require_once __DIR__.'/User/routing.php';
});


