<?php

namespace Dlx\Security\User\Domain;

use Daikon\EventSourcing\Aggregate\AggregateAlias;
use Daikon\EventSourcing\Aggregate\AggregateRootInterface;
use Daikon\EventSourcing\Aggregate\AggregateRootTrait;
use Dlx\Security\User\Domain\Command\ActivateUser;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\Command\LogoutUser;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\Command\UpdateUser;
use Dlx\Security\User\Domain\Entity\UserEntityType;
use Dlx\Security\User\Domain\Event\AuthTokenWasAdded;
use Dlx\Security\User\Domain\Event\UserWasLoggedIn;
use Dlx\Security\User\Domain\Event\UserWasLoggedOut;
use Dlx\Security\User\Domain\Event\UserWasActivated;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\UserWasUpdated;
use Dlx\Security\User\Domain\Event\VerifyTokenWasAdded;
use Dlx\Security\User\Domain\Event\VerifyTokenWasRemoved;

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
            ->reflectThat(UserWasRegistered::viaCommand($registerUser))
            ->reflectThat(AuthTokenWasAdded::viaCommand($registerUser))
            ->reflectThat(VerifyTokenWasAdded::viaCommand($registerUser));
    }

    public function login(LoginUser $loginUser): self
    {
        return $this->reflectThat(UserWasLoggedIn::viaCommand($loginUser));
    }

    public function logout(LogoutUser $logoutUser): self
    {
        return $this->reflectThat(UserWasLoggedOut::viaCommand($logoutUser));
    }

    public function activate(ActivateUser $activateUser): self
    {
        return $this->reflectThat(UserWasActivated::viaCommand($activateUser))
            ->reflectThat(VerifyTokenWasRemoved::viaCommand($activateUser));
    }

    private function whenUserWasRegistered(UserWasRegistered $userWasRegistered): void
    {
        $this->userState = (new UserEntityType)->makeEntity()
            ->withUsername($userWasRegistered->getUsername())
            ->withEmail($userWasRegistered->getEmail())
            ->withRole($userWasRegistered->getRole())
            ->withPasswordHash($userWasRegistered->getPasswordHash())
            ->withLocale($userWasRegistered->getLocale());
    }

    private function whenUserWasActivated(UserWasActivated $userWasActivated): void
    {
        $this->userState = $this->userState->withUserActivated([
            'state' => $userWasActivated->getState()
        ]);
    }

    private function whenAuthTokenWasAdded(AuthTokenWasAdded $tokenWasAdded): void
    {
        $this->userState = $this->userState->withAuthTokenAdded([
            'id' => $tokenWasAdded->getId(),
            'token' => $tokenWasAdded->getToken(),
            'expiresAt' => $tokenWasAdded->getExpiresAt()
        ]);
    }

    private function whenVerifyTokenWasAdded(VerifyTokenWasAdded $tokenWasAdded): void
    {
        $this->userState = $this->userState->withVerifyTokenAdded([
            'id' => $tokenWasAdded->getId(),
            'token' => $tokenWasAdded->getToken()
        ]);
    }

    private function whenVerifyTokenWasRemoved(VerifyTokenWasRemoved $tokenWasRemoved): void
    {
        $this->userState = $this->userState->withVerifyTokenRemoved();
    }

    private function whenUserWasLoggedIn(UserWasLoggedIn $userWasLoggedIn): void
    {
        $this->userState = $this->userState->withUserLoggedIn([
            'id' => $userWasLoggedIn->getAuthTokenId(),
            'expiresAt' => $userWasLoggedIn->getAuthTokenExpiresAt()
        ]);
    }

    private function whenUserWasLoggedOut(UserWasLoggedOut $userWasLoggedOut): void
    {
        $this->userState = $this->userState->withUserLoggedOut([
            'id' => $userWasLoggedOut->getAuthTokenId(),
            'token' => $userWasLoggedOut->getAuthToken(),
            'expiresAt' => $userWasLoggedOut->getAuthTokenExpiresAt()
        ]);
    }
}
