<?php

namespace Dlx\Security\User\Handler;

use Daikon\EventSourcing\Aggregate\Command\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\User;

final class RegisterUserHandler extends CommandHandler
{
    protected function handleRegisterUser(RegisterUser $registerUser, Metadata $metadata): array
    {
        return [User::register($registerUser), $metadata];
    }
}
