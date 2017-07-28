<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class ActivateUser extends Command
{
    private $state;

    public static function getAggregateRootClass(): string
    {
        return User::class;
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            UserState::fromNative('activated')
        );
    }

    public function getState(): UserState
    {
        return $this->state;
    }

    protected function __construct(
        AggregateId $aggregateId,
        UserState $state
    ) {
        parent::__construct($aggregateId);
        $this->state = $state;
    }
}
