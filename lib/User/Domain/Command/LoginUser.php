<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Timestamp;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;

final class LoginUser extends Command
{
    private $authTokenId;

    private $authTokenExpiresAt;

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            AggregateId::fromNative($state['aggregateId']),
            Uuid::fromNative($state['authTokenId']),
            Timestamp::fromNative($state['authTokenExpiresAt'])
        );
    }

    public function getAuthTokenId(): Uuid
    {
        return $this->authTokenId;
    }

    public function getAuthTokenExpiresAt(): Timestamp
    {
        return $this->authTokenExpiresAt;
    }

    public function toNative(): array
    {
        return array_merge(
            [
                'authTokenId' => $this->authTokenId->toNative(),
                'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
            ],
            parent::toNative()
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Uuid $authTokenId,
        Timestamp $authTokenExpiresAt
    ) {
        parent::__construct($aggregateId);
        $this->authTokenId = $authTokenId;
        $this->authTokenExpiresAt = $authTokenExpiresAt;
    }
}
