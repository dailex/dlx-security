<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Command\CommandInterface;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\ActivateUser;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class UserWasActivated extends DomainEvent
{
    private $state;

    public static function viaCommand(ActivateUser $activateUser): self
    {
        return new self(
            $activateUser->getAggregateId(),
            UserState::fromNative(UserState::ACTIVATED)
        );
    }

    /** @param array $payload */
    public static function fromNative($payload): MessageInterface
    {
        return new self(
            AggregateId::fromNative($payload['aggregateId']),
            UserState::fromNative($payload['state']),
            AggregateRevision::fromNative($payload['aggregateRevision'])
        );
    }

    public function conflictsWith(DomainEventInterface $otherEvent): bool
    {
        return false;
    }

    public function getState(): UserState
    {
        return $this->state;
    }

    public function toNative(): array
    {
        return array_merge([ 'state' => $this->state->toNative() ], parent::toNative());
    }

    protected function __construct(
        AggregateId $aggregateId,
        UserState $state,
        AggregateRevision $aggregateRevision = null
    ) {
        parent::__construct($aggregateId, $aggregateRevision);
        $this->state = $state;
    }
}
