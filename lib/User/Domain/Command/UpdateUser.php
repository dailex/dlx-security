<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateIdInterface;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;

final class UpdateUser extends Command
{
    private $username;

    private $email;

    private $locale;

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            AggregateId::fromNative($state['aggregateId']),
            Text::fromNative($state['username']),
            Email::fromNative($state['email']),
            Text::fromNative($state['locale'])
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

    public function getLocale(): Text
    {
        return $this->locale;
    }

    public function toNative(): array
    {
        return array_merge(
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'locale' => $this->locale->toNative()
            ],
            parent::toNative()
        );
    }

    protected function __construct(
        AggregateId $aggregateId,
        Text $username,
        Email $email,
        Text $locale
    ) {
        parent::__construct($aggregateId);
        $this->username = $username;
        $this->email = $email;
        $this->locale = $locale;
    }
}
