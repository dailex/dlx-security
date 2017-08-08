<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;

final class ActivateUser extends Command
{
    public static function getAggregateRootClass(): string
    {
        return User::class;
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId'])
        );
    }

    protected function __construct(AggregateId $aggregateId)
    {
        parent::__construct($aggregateId);
    }
}
