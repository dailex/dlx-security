<?php

use Dlx\Security\User\Controller\CollectionController;
use Dlx\Security\User\Controller\ResourceController;

$app->mount('/users', function ($app) use ($cratePrefix) {
    $app->get('/', [CollectionController::class, 'read'])->bind($cratePrefix.'.users');
    $app->post('/', [CollectionController::class, 'write']);
    $app->get('/{userId}', [ResourceController::class, 'read'])->bind($cratePrefix.'.users.resource');
});
