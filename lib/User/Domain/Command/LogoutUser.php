<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Timestamp;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\ValueObject\RandomToken;

final class LogoutUser extends Command
{
    private $authTokenId;

    private $authToken;

    private $authTokenExpiresAt;

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            AggregateId::fromNative($state['aggregateId']),
            Uuid::fromNative($state['authTokenId']),
            RandomToken::generate(),
            Timestamp::now()
        );
    }

    public function getAuthTokenId(): Uuid
    {
        return $this->authTokenId;
    }

    public function getAuthToken(): RandomToken
    {
        return $this->authToken;
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
                'authToken' => $this->authToken->toNative(),
                'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
            ],
            parent::toNative()
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Uuid $authTokenId,
        RandomToken $authToken,
        Timestamp $authTokenExpiresAt
    ) {
        parent::__construct($aggregateId);
        $this->authTokenId = $authTokenId;
        $this->authToken = $authToken;
        $this->authTokenExpiresAt = $authTokenExpiresAt;
    }
}
