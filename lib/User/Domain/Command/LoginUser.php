<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Timestamp;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;

final class LoginUser extends Command
{
    private $authTokenId;

    private $authTokenExpiresAt;

    public static function getAggregateRootClass(): string
    {
        return User::class;
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Uuid::fromNative($nativeValues['authTokenId']),
            Timestamp::fromNative($nativeValues['authTokenExpiresAt'])
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

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'authTokenId' => $this->authTokenId->toNative(),
                'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
            ]
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
