<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Timestamp;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateIdInterface;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\ValueObject\UserRole;

final class RegisterUser extends Command
{
    private $username;

    private $email;

    private $role;

    private $locale;

    private $passwordHash;

    private $authTokenExpiresAt;

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            AggregateId::fromNative($state['aggregateId']),
            Text::fromNative($state['username']),
            Email::fromNative($state['email']),
            UserRole::fromNative($state['role']),
            Text::fromNative($state['locale']),
            Text::fromNative($state['passwordHash']),
            Timestamp::fromNative($state['authTokenExpiresAt'])
        );
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

    public function getAuthTokenExpiresAt(): Timestamp
    {
        return $this->authTokenExpiresAt;
    }

    public function toNative(): array
    {
        return array_merge(
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'role' => $this->role->toNative(),
                'locale' => $this->locale->toNative(),
                'passwordHash' => $this->passwordHash->toNative(),
                'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
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
        Timestamp $authTokenExpiresAt
    ) {
        parent::__construct($aggregateId);
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->locale = $locale;
        $this->passwordHash = $passwordHash;
        $this->authTokenExpiresAt = $authTokenExpiresAt;
    }
}
