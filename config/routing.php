<?php

use Dlx\Security\Controller\LoginController;
use Dlx\Security\Controller\LogoutController;
use Dlx\Security\Controller\RegistrationController;

$cratePrefix = 'dlx.security';
$mount = $configProvider->get('crates.'.$cratePrefix.'.mount', '/dlx/security');

$app->mount($mount, function ($app) use ($cratePrefix) {
    $app->get('/login', [LoginController::class, 'read'])->bind($cratePrefix.'.login');
    $app->match('/logout', [LogoutController::class, 'write'])->bind($cratePrefix.'.logout');
    $app->match('/authenticate', [LoginController::class, 'write'])->bind($cratePrefix.'.authenticate');
    $app->get('/registration', [RegistrationController::class, 'read'])->bind($cratePrefix.'.registration');
    $app->post('/registration', [RegistrationController::class, 'write']);
    require_once __DIR__.'/User/routing.php';
});


