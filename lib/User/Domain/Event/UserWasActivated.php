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

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            UserState::fromNative($nativeValues['state']),
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

    public function getState(): UserState
    {
        return $this->state;
    }

    public function toArray(): array
    {
        return array_merge([ 'state' => $this->state->toNative() ], parent::toArray());
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
