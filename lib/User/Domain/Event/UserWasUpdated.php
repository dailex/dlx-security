<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;
use Dlx\Security\User\Domain\Command\UpdateUser;

final class UserWasUpdated extends DomainEvent
{
    private $username;

    private $email;

    private $firstname;

    private $lastname;

    private $locale;

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
            Text::fromNative($nativeValues['firstname']),
            Text::fromNative($nativeValues['lastname']),
            Text::fromNative($nativeValues['locale']),
            AggregateRevision::fromNative($nativeValues['aggregateRevision'])
        );
    }

    public static function viaCommand(UpdateUser $updateUser): self
    {
        return new self(
            $updateUser->getAggregateId(),
            $updateUser->getUsername(),
            $updateUser->getEmail(),
            $updateUser->getFirstname(),
            $updateUser->getLastname(),
            $updateUser->getLocale()
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

    public function getFirstname(): Text
    {
        return $this->firstname;
    }

    public function getLastname(): Text
    {
        return $this->lastname;
    }

    public function getLocale(): Text
    {
        return $this->locale;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'firstname' => $this->firstname->toNative(),
                'lastname' => $this->lastname->toNative(),
                'locale' => $this->locale->toNative()
            ]
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $username,
        Email $email,
        Text $firstname,
        Text $lastname,
        Text $locale,
        AggregateRevision $revision = null
    ) {
        parent::__construct($aggregateId, $revision);
        $this->username = $username;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->locale = $locale;
    }
}
