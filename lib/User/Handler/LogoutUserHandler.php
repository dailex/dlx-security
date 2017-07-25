<?php

namespace Dlx\Security\User\Handler;

use Daikon\EventSourcing\Aggregate\Command\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Dlx\Security\User\Domain\Command\LogoutUser;
use Dlx\Security\User\Domain\User;

final class LogoutUserHandler extends CommandHandler
{
    protected function handleLogoutUser(LogoutUser $logoutUser, Metadata $metadata): array
    {
        $user = $this->checkout($logoutUser->getAggregateId(), $logoutUser->getKnownAggregateRevision());
        return [$user->logout($logoutUser), $metadata];
    }
}
