<?php

use Dlx\Security\Controller\RegistrationController;

$cratePrefix = 'dlx.security';
$mount = $configProvider->get('crates.'.$cratePrefix.'.mount', '/dlx/security');

$app->mount($mount, function ($app) use ($cratePrefix) {
    $app->get('/registration', [RegistrationController::class, 'read'])->bind($cratePrefix.'.registration');
    $app->post('/registration', [RegistrationController::class, 'write']);
    require_once __DIR__.'/User/routing.php';
});


