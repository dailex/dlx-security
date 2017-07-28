<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Command\CommandInterface;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\ActivateUser;
use Dlx\Security\User\Domain\User;

final class VerifyTokenWasRemoved extends DomainEvent
{
    public static function viaCommand(ActivateUser $activateUser): self
    {
        return new self(
            $activateUser->getAggregateId()
        );
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            AggregateRevision::fromNative($nativeValues['aggregateRevision'])
        );
    }

    public static function getAggregateRootClass(): string
    {
        return User::class;
    }

    public function conflictsWith(DomainEventInterface $otherEvent): bool
    {
        return false;
    }
}
