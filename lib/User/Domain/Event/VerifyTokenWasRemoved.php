<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Command\CommandInterface;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\ActivateUser;

final class VerifyTokenWasRemoved extends DomainEvent
{
    public static function viaCommand(ActivateUser $activateUser): self
    {
        return new self(
            $activateUser->getAggregateId()
        );
    }

    /** @param array $payload */
    public static function fromNative($payload): MessageInterface
    {
        return new self(
            AggregateId::fromNative($payload['aggregateId']),
            AggregateRevision::fromNative($payload['aggregateRevision'])
        );
    }

    public function conflictsWith(DomainEventInterface $otherEvent): bool
    {
        return false;
    }
}
