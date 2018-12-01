<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\ValueObject\UserRole;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class UserWasRegistered extends DomainEvent
{
    private $username;

    private $email;

    private $role;

    private $locale;

    private $passwordHash;

    private $state;

    public static function viaCommand(RegisterUser $registerUser): self
    {
        return new self(
            $registerUser->getAggregateId(),
            $registerUser->getUsername(),
            $registerUser->getEmail(),
            $registerUser->getRole(),
            $registerUser->getLocale(),
            $registerUser->getPasswordHash(),
            UserState::fromNative(UserState::UNVERIFIED)
        );
    }

    /** @param array $payload */
    public static function fromNative($payload): MessageInterface
    {
        return new self(
            AggregateId::fromNative($payload['aggregateId']),
            Text::fromNative($payload['username']),
            Email::fromNative($payload['email']),
            UserRole::fromNative($payload['role']),
            Text::fromNative($payload['locale']),
            Text::fromNative($payload['password_hash']),
            UserState::fromNative($payload['state']),
            AggregateRevision::fromNative($payload['aggregateRevision'])
        );
    }

    public function conflictsWith(DomainEventInterface $otherEvent): bool
    {
        return false;
    }

    public function getUsername(): Text
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getLocale(): Text
    {
        return $this->locale;
    }

    public function getPasswordHash(): Text
    {
        return $this->passwordHash;
    }

    public function getState(): UserState
    {
        return $this->state;
    }

    public function toNative(): array
    {
        return array_merge(
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'role' => $this->role->toNative(),
                'locale' => $this->locale->toNative(),
                'password_hash' => $this->passwordHash->toNative(),
                'state' => $this->state->toNative()
            ],
            parent::toNative()
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $username,
        Email $email,
        UserRole $role,
        Text $locale,
        Text $passwordHash,
        UserState $state,
        AggregateRevision $revision = null
    ) {
        parent::__construct($aggregateId, $revision);
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->locale = $locale;
        $this->passwordHash = $passwordHash;
        $this->state = $state;
    }
}
