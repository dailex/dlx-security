<?php

namespace Dlx\Security\User\Repository\Standard;

use Daikon\ReadModel\Projection\ProjectionInterface;
use Daikon\ReadModel\Projection\ProjectionTrait;
use Dlx\Security\User\Domain\Entity\AuthToken\AuthToken;
use Dlx\Security\User\Domain\Event\AuthTokenWasAdded;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\UserWasUpdated;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

final class User implements ProjectionInterface, AdvancedUserInterface
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

    public function getState(): string
    {
        return $this->state['state'];
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
                'password_hash' => $userWasRegistered->getPasswordHash()->toNative(),
                'state' => $userWasRegistered->getState()->toNative()
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

    private function whenAuthTokenWasAdded(AuthTokenWasAdded $tokenWasAdded)
    {
        return self::fromArray(array_merge_recursive(
            $this->state,
            [
                'tokens' => [[
                    'id' => $tokenWasAdded->getId()->toNative(),
                    'token' => $tokenWasAdded->getToken()->toNative(),
                    'expires_at' => $tokenWasAdded->getExpiresAt()->toNative(),
                    '@type' => AuthToken::class
                ]]
            ]
        ));
    }

    public function getRoles(): array
    {
        return [$this->state['role']];
    }

    public function getTokens(): array
    {
        return $this->state['tokens'];
    }

    public function getPassword(): string
    {
        return $this->getPasswordHash();
    }

    public function isAccountNonExpired()
    {
        return $this->isEnabled();
    }

    public function isAccountNonLocked()
    {
        return $this->state['state'] !== 'deleted';
    }

    /*
     * Login event is applied after symfony authentication so performing token
     * checks here will block valid login. UserTokenAuthenticator handles
     * checks instead. RememberMe services do not do post-auth checks,
     * so in any case this is not executed for auto-logins via cookie...
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /*
     * So instead we have a method for doing additional checks outside the
     * standard symfony flow...
     */
    public function isAuthenticationTokenNonExpired()
    {
        /*
         * @todo need to invalidate on token string changes as well but that should be
         * done somehow in the AbstractToken::hasUserChanged() method, which is private..
         */
    }

    public function isEnabled()
    {
        return $this->state['state'] !== 'deactivated';
    }

    public function isVerified()
    {
        $this->state['state'] === 'verified';
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    public function toArray(): array
    {
        return $this->state;
    }
}
