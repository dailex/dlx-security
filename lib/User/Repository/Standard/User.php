<?php

namespace Dlx\Security\User\Repository\Standard;

use Daikon\ReadModel\Projection\ProjectionInterface;
use Daikon\ReadModel\Projection\ProjectionTrait;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\UserWasUpdated;

final class User implements ProjectionInterface
{
    use ProjectionTrait;

    public function getUsername(): string
    {
        return $this->state['username'];
    }

    public function getEmail(): string
    {
        return $this->state['email'];
    }

    public function getRole(): string
    {
        return $this->state['role'];
    }

    public function getPasswordHash(): string
    {
        return $this->state['password_hash'];
    }

    public function getFirstname(): string
    {
        return $this->state['firstname'];
    }

    public function getLastname(): string
    {
        return $this->state['lastname'];
    }

    public function getLocale(): string
    {
        return $this->state['locale'];
    }

    private function whenUserWasRegistered(UserWasRegistered $userWasRegistered)
    {
        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateId' => $userWasRegistered->getAggregateId()->toNative(),
                'aggregateRevision' => $userWasRegistered->getAggregateRevision()->toNative(),
                'username' => $userWasRegistered->getUsername()->toNative(),
                'email' => $userWasRegistered->getEmail()->toNative(),
                'role' => $userWasRegistered->getRole()->toNative(),
                'firstname' => $userWasRegistered->getFirstname()->toNative(),
                'lastname' => $userWasRegistered->getLastname()->toNative(),
                'locale' => $userWasRegistered->getLocale()->toNative(),
                'password_hash' => $userWasRegistered->getPasswordHash()->toNative()
            ]
        ));
    }

    private function whenUserWasUpdated(UserWasUpdated $userWasUpdated)
    {
        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasUpdated->getAggregateRevision()->toNative(),
                'username' => $userWasUpdated->getUsername()->toNative(),
                'email' => $userWasUpdated->getEmail()->toNative(),
                'role' => $userWasUpdated->getRole()->toNative(),
                'firstname' => $userWasUpdated->getFirstname()->toNative(),
                'lastname' => $userWasUpdated->getLastname()->toNative(),
                'locale' => $userWasUpdated->getLocale()->toNative(),
                'password_hash' => $userWasUpdated->getPasswordHash()->toNative()
            ]
        ));
    }
}
