<?php

namespace Dlx\Security\User\Domain;

use Daikon\EventSourcing\Aggregate\AggregateAlias;
use Daikon\EventSourcing\Aggregate\AggregateRootInterface;
use Daikon\EventSourcing\Aggregate\AggregateRootTrait;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\Command\UpdateUser;
use Dlx\Security\User\Domain\Entity\UserEntityType;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\UserWasUpdated;

final class User implements AggregateRootInterface
{
    use AggregateRootTrait;

    private $userState;

    public static function getAlias(): AggregateAlias
    {
        return AggregateAlias::fromNative('dlx.security.user');
    }

    public static function register(RegisterUser $registerUser): self
    {
        return (new self($registerUser->getAggregateId()))
            ->reflectThat(UserWasRegistered::viaCommand($registerUser));
    }

    public function update(UpdateUser $updateUser): self
    {
        return $this->reflectThat(UserWasUpdated::viaCommand($updateUser));
    }

    protected function whenUserWasRegistered(UserWasRegistered $userWasRegistered)
    {
        $this->userState = (new UserEntityType)->makeEntity()
            ->withUsername($userWasRegistered->getUsername())
            ->withEmail($userWasRegistered->getEmail())
            ->withRole($userWasRegistered->getRole())
            ->withPasswordHash($userWasRegistered->getPasswordHash())
            ->withFirstname($userWasRegistered->getFirstname())
            ->withLastname($userWasRegistered->getLastname())
            ->withLocale($userWasRegistered->getLocale());
    }
}
