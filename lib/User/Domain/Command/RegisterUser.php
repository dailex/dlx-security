<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Timestamp;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateIdInterface;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;
use Dlx\Security\User\Domain\ValueObject\UserRole;

final class RegisterUser extends Command
{
    private $username;

    private $email;

    private $role;

    private $locale;

    private $passwordHash;

    private $authTokenExpiresAt;

    private $firstname;

    private $lastname;

    public static function getAggregateRootClass(): string
    {
        return User::class;
    }

    public static function fromArray(array $nativeValues): MessageInterface
    {
        return new self(
            AggregateId::fromNative($nativeValues['aggregateId']),
            Text::fromNative($nativeValues['username']),
            Email::fromNative($nativeValues['email']),
            UserRole::fromNative($nativeValues['role']),
            Text::fromNative($nativeValues['locale']),
            Text::fromNative($nativeValues['passwordHash']),
            Timestamp::fromNative($nativeValues['authTokenExpiresAt']),
            array_key_exists('firstname', $nativeValues) ? Text::fromNative($nativeValues['firstname']) : null,
            array_key_exists('lastname', $nativeValues) ? Text::fromNative($nativeValues['lastname']) : null
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

    public function getFirstname(): ?Text
    {
        return $this->firstname;
    }

    public function getLastname(): ?Text
    {
        return $this->lastname;
    }

    public function toArray(): array
    {
        $mandatoryValues = [
            'username' => $this->username->toNative(),
            'email' => $this->email->toNative(),
            'role' => $this->role->toNative(),
            'locale' => $this->locale->toNative(),
            'passwordHash' => $this->passwordHash->toNative(),
            'authTokenExpiresAt' => $this->authTokenExpiresAt->toNative()
        ];

        $optionalValues = [];
        if (!is_null($this->firstname)) {
            $optionalValues['firstname'] = $this->firstname->toNative();
        }
        if (!is_null($this->lastname)) {
            $optionalValues['lastname'] = $this->lastname->toNative();
        }

        return array_merge(
            $mandatoryValues,
            $optionalValues,
            parent::toArray()
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $username,
        Email $email,
        UserRole $role,
        Text $locale,
        Text $passwordHash,
        Timestamp $authTokenExpiresAt,
        Text $firstname = null,
        Text $lastname = null
    ) {
        parent::__construct($aggregateId);
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->locale = $locale;
        $this->passwordHash = $passwordHash;
        $this->authTokenExpiresAt = $authTokenExpiresAt;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }
}
