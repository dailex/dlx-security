<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\Entity\ValueObject\Timestamp;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Command\CommandInterface;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\User;

final class UserWasLoggedIn extends DomainEvent
{
    private $authTokenId;

    private $authTokenExpiresAt;

    public static function viaCommand(LoginUser $loginUser): self
    {
        return new self(
            $loginUser->getAggregateId(),
            $loginUser->getAuthTokenId(),
            $loginUser->getAuthTokenExpiresAt()
        );
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Uuid::fromNative($nativeValues['authTokenId']),
            Timestamp::fromNative($nativeValues['authTokenExpiresAt']),
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
        return array_merge([
            'authTokenId' => $this->authTokenId->toNative(),
            'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
        ], parent::toArray());
    }

    protected function __construct(
        AggregateId $aggregateId,
        Uuid $authTokenId,
        Timestamp $authTokenExpiresAt,
        AggregateRevision $aggregateRevision = null
    ) {
        parent::__construct($aggregateId, $aggregateRevision);
        $this->authTokenId = $authTokenId;
        $this->authTokenExpiresAt = $authTokenExpiresAt;
    }
}
