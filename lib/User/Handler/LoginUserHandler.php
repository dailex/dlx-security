<?php

namespace Dlx\Security\User\Handler;

use Daikon\EventSourcing\Aggregate\Command\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\User;

final class LoginUserHandler extends CommandHandler
{
    protected function handleLoginUser(LoginUser $loginUser, Metadata $metadata): array
    {
        $user = $this->checkout($loginUser->getAggregateId(), $loginUser->getKnownAggregateRevision());
        return [$user->login($loginUser), $metadata];
    }
}
