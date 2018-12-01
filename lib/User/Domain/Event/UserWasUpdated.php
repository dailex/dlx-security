<?php

namespace Dlx\Security\User\Domain\Event;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateRevision;
use Daikon\EventSourcing\Aggregate\Event\DomainEvent;
use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\Command\UpdateUser;

final class UserWasUpdated extends DomainEvent
{
    private $username;

    private $email;

    private $locale;

    /** @param array $payload */
    public static function fromNative($payload): MessageInterface
    {
        return new self(
            AggregateId::fromNative($payload['aggregateId']),
            Text::fromNative($payload['username']),
            Email::fromNative($payload['email']),
            Text::fromNative($payload['locale']),
            AggregateRevision::fromNative($payload['aggregateRevision'])
        );
    }

    public static function viaCommand(UpdateUser $updateUser): self
    {
        return new self(
            $updateUser->getAggregateId(),
            $updateUser->getUsername(),
            $updateUser->getEmail(),
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

    public function getLocale(): Text
    {
        return $this->locale;
    }

    public function toNative(): array
    {
        return array_merge(
            parent::toNative(),
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'locale' => $this->locale->toNative()
            ]
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $username,
        Email $email,
        Text $locale,
        AggregateRevision $aggregateRevision = null
    ) {
        parent::__construct($aggregateId, $aggregateRevision);
        $this->username = $username;
        $this->email = $email;
        $this->locale = $locale;
    }
}
