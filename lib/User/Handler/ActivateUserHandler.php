<?php

namespace Dlx\Security\User\Handler;

use Daikon\EventSourcing\Aggregate\Command\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Dlx\Security\User\Domain\Command\ActivateUser;
use Dlx\Security\User\Domain\User;

final class ActivateUserHandler extends CommandHandler
{
    protected function handleActivateUser(ActivateUser $activateUser, Metadata $metadata): array
    {
        $user = $this->checkout($activateUser->getAggregateId(), $activateUser->getKnownAggregateRevision());
        return [$user->activate($activateUser), $metadata];
    }
}
