<?php

namespace Dlx\Security\User\Domain;

use Daikon\EventSourcing\Aggregate\AggregateAlias;
use Daikon\EventSourcing\Aggregate\AggregateRootInterface;
use Daikon\EventSourcing\Aggregate\AggregateRootTrait;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\Command\LogoutUser;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\Command\UpdateUser;
use Dlx\Security\User\Domain\Entity\UserEntityType;
use Dlx\Security\User\Domain\Event\AuthTokenWasAdded;
use Dlx\Security\User\Domain\Event\UserWasLoggedIn;
use Dlx\Security\User\Domain\Event\UserWasLoggedOut;
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
            ->reflectThat(UserWasRegistered::viaCommand($registerUser))
            ->reflectThat(AuthTokenWasAdded::viaCommand($registerUser));
    }

    public function login(LoginUser $loginUser): self
    {
        return $this->reflectThat(UserWasLoggedIn::viaCommand($loginUser));
    }

    public function logout(LogoutUser $logoutUser): self
    {
        return $this->reflectThat(UserWasLoggedOut::viaCommand($logoutUser));
    }

    public function update(UpdateUser $updateUser): self
    {
        return $this->reflectThat(UserWasUpdated::viaCommand($updateUser));
    }

    private function whenUserWasRegistered(UserWasRegistered $userWasRegistered)
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

    private function whenAuthTokenWasAdded(AuthTokenWasAdded $tokenWasAdded)
    {
        $this->userState = $this->userState->withAuthTokenAdded([
            'id' => $tokenWasAdded->getId(),
            'token' => $tokenWasAdded->getToken(),
            'expiresAt' => $tokenWasAdded->getExpiresAt()
        ]);
    }

    private function whenUserWasLoggedIn(UserWasLoggedIn $userWasLoggedIn)
    {
        $this->userState = $this->userState->withUserLoggedIn([
            'id' => $userWasLoggedIn->getAuthTokenId(),
            'expiresAt' => $userWasLoggedIn->getAuthTokenExpiresAt()
        ]);
    }

    private function whenUserWasLoggedOut(UserWasLoggedOut $userWasLoggedOut)
    {
        $this->userState = $this->userState->withUserLoggedOut([
            'id' => $userWasLoggedOut->getAuthTokenId(),
            'token' => $userWasLoggedOut->getAuthToken(),
            'expiresAt' => $userWasLoggedOut->getAuthTokenExpiresAt()
        ]);
    }
}
