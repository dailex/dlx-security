<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;

final class ActivateUser extends Command
{
    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            AggregateId::fromNative($state['aggregateId'])
        );
    }

    protected function __construct(AggregateId $aggregateId)
    {
        parent::__construct($aggregateId);
    }
}
