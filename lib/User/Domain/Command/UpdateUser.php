<?php

namespace Dlx\Security\User\Domain\Command;

use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Daikon\EventSourcing\Aggregate\AggregateIdInterface;
use Daikon\EventSourcing\Aggregate\Command\Command;
use Daikon\MessageBus\MessageInterface;
use Dlx\Security\User\Domain\User;

final class UpdateUser extends Command
{
    private $username;

    private $email;

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
            Text::fromNative($nativeValues['locale'])
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

    public function toArray(): array
    {
        return array_merge(
            [
                'username' => $this->username->toNative(),
                'email' => $this->email->toNative(),
                'locale' => $this->locale->toNative()
            ],
            parent::toArray()
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
