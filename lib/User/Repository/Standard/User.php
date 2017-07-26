<?php

namespace Dlx\Security\User\Repository\Standard;

use Daikon\ReadModel\Projection\ProjectionInterface;
use Daikon\ReadModel\Projection\ProjectionTrait;
use Dlx\Security\User\Domain\Entity\AuthToken\AuthToken;
use Dlx\Security\User\Domain\Event\AuthTokenWasAdded;
use Dlx\Security\User\Domain\Event\UserWasLoggedIn;
use Dlx\Security\User\Domain\Event\UserWasLoggedOut;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\UserWasUpdated;
use Dlx\Security\User\Domain\Event\VerifyTokenWasAdded;
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
        return $this->state['passwordHash'];
    }

    public function getLocale(): string
    {
        return $this->state['locale'];
    }

    public function getState(): string
    {
        return $this->state['state'];
    }

    public function getFirstname(): ?string
    {
        return $this->state['firstname'] ?? null;
    }

    public function getLastname(): ?string
    {
        return $this->state['lastname'] ?? null;
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
                'locale' => $userWasRegistered->getLocale()->toNative(),
                'passwordHash' => $userWasRegistered->getPasswordHash()->toNative(),
                'state' => $userWasRegistered->getState()->toNative(),
                'firstname' => $userWasRegistered->getFirstname()
                    ? $userWasRegistered->getFirstname()->toNative() : null,
                'lastname' => $userWasRegistered->getLastname()
                    ? $userWasRegistered->getFirstname()->toNative() : null,
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
                'passwordHash' => $userWasUpdated->getPasswordHash()->toNative()
            ]
        ));
    }

    private function whenAuthTokenWasAdded(AuthTokenWasAdded $tokenWasAdded)
    {
        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateRevision' => $tokenWasAdded->getAggregateRevision()->toNative(),
                'tokens' => [[
                    'id' => $tokenWasAdded->getId()->toNative(),
                    'token' => $tokenWasAdded->getToken()->toNative(),
                    'expiresAt' => $tokenWasAdded->getExpiresAt()->toNative(),
                    '@type' => 'auth_token'
                ]]
            ]
        ));
    }

    private function whenVerifyTokenWasAdded(VerifyTokenWasAdded $tokenWasAdded)
    {
        //@todo better token merging
        return self::fromArray(array_merge_recursive(
            array_merge(
                $this->state,
                ['aggregateRevision' => $tokenWasAdded->getAggregateRevision()->toNative()]
            ),
            [
                'tokens' => [[
                    'id' => $tokenWasAdded->getId()->toNative(),
                    'token' => $tokenWasAdded->getToken()->toNative(),
                    '@type' => 'verify_token'
                ]]
            ]
        ));
    }

    private function whenUserWasLoggedIn(UserWasLoggedIn $userWasLoggedIn)
    {
        //@todo better token updating
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($userWasLoggedIn->getAuthTokenId()->toNative() === $token['id']) {
                $token['expiresAt'] = $userWasLoggedIn->getAuthTokenExpiresAt()->toNative();
            }
            $tokens[] = $token;
        }

        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasLoggedIn->getAggregateRevision()->toNative(),
                'tokens' => $tokens
            ]
        ));
    }

    private function whenUserWasLoggedOut(UserWasLoggedOut $userWasLoggedOut)
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($userWasLoggedOut->getAuthTokenId()->toNative() === $token['id']) {
                $token['token'] = $userWasLoggedOut->getAuthToken()->toNative();
                $token['expiresAt'] = $userWasLoggedOut->getAuthTokenExpiresAt()->toNative();
            }
            $tokens[] = $token;
        }

        return self::fromArray(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasLoggedOut->getAggregateRevision()->toNative(),
                'tokens' => $tokens
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

    public function getToken(string $type)
    {
        foreach ($this->getTokens() as $token) {
            if ($type === $token['@type']) {
                return $token;
            }
        }
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
        $this->state['state'] === 'activated';
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
