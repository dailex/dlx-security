<?php

namespace Dlx\Security\User\Handler;

use Daikon\EventSourcing\Aggregate\Command\CommandHandler;
use Daikon\MessageBus\Metadata\Metadata;
use Dlx\Security\User\Domain\Command\UpdateUser;

final class UpdateUserHandler extends CommandHandler
{
    protected function handleUpdateUser(UpdateUser $updateUser, Metadata $metadata): array
    {
        $user = $this->checkout($updateUser->getAggregateId(), $updateUser->getKnownAggregateRevision());
        return [$user->update($updateUser), $metadata];
    }
}
