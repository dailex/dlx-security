<?php

namespace Dlx\Security\User\Repository\Standard;

use Daikon\ReadModel\Projection\ProjectionTrait;
use Dlx\Security\User\Domain\Entity\VerifyToken;
use Dlx\Security\User\Domain\Event\AuthTokenWasAdded;
use Dlx\Security\User\Domain\Event\UserWasActivated;
use Dlx\Security\User\Domain\Event\UserWasLoggedIn;
use Dlx\Security\User\Domain\Event\UserWasLoggedOut;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Domain\Event\VerifyTokenWasAdded;
use Dlx\Security\User\Domain\Event\VerifyTokenWasRemoved;
use Dlx\Security\User\Repository\DailexUserInterface;

final class User implements DailexUserInterface
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

    public function getLocale(): string
    {
        return $this->state['locale'];
    }

    public function getRoles(): array
    {
        return [$this->state['role']];
    }

    public function getTokens(): array
    {
        return $this->state['tokens'];
    }

    public function getToken(string $tokenFqcn): ?array
    {
        foreach ($this->getTokens() as $token) {
            if ($tokenFqcn === $token['@type']) {
                return $token;
            }
        }
        return null;
    }

    public function getPassword(): string
    {
        return $this->state['passwordHash'];
    }

    public function isAccountNonExpired(): bool
    {
        return $this->state['state'] !== 'deleted';
    }

    public function isAccountNonLocked(): bool
    {
        return $this->state['state'] !== 'deactivated';
    }

    /*
     * Login event is applied after symfony authentication so performing token
     * checks here will block valid login. UserTokenAuthenticator handles
     * checks instead. RememberMe services do not do post-auth checks,
     * so in any case this is not executed for auto-logins via cookie...
     */
    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    /*
     * So instead we have a method for doing additional checks outside the
     * standard symfony flow...
     */
    public function isAuthTokenNonExpired(): bool
    {
        /*
         * @todo need to invalidate on token string changes as well but that should be
         * done somehow in the AbstractToken::hasUserChanged() method, which is private..
         */
        $token = $this->getToken(AuthToken::class);
        return new \DateTimeImmutable('now') < new \DateTimeImmutable($token['expiresAt']);
    }

    public function isEnabled(): bool
    {
        return $this->isAccountNonExpired() && $this->isAccountNonLocked();
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
    }

    private function whenUserWasRegistered(UserWasRegistered $userWasRegistered): self
    {
        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateId' => $userWasRegistered->getAggregateId()->toNative(),
                'aggregateRevision' => $userWasRegistered->getAggregateRevision()->toNative(),
                'username' => $userWasRegistered->getUsername()->toNative(),
                'email' => $userWasRegistered->getEmail()->toNative(),
                'role' => $userWasRegistered->getRole()->toNative(),
                'locale' => $userWasRegistered->getLocale()->toNative(),
                'passwordHash' => $userWasRegistered->getPasswordHash()->toNative(),
                'state' => $userWasRegistered->getState()->toNative()
            ]
        ));
    }

    private function whenUserWasActivated(UserWasActivated $userWasActivated): self
    {
        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasActivated->getAggregateRevision()->toNative(),
                'state' => $userWasActivated->getState()->toNative()
            ]
        ));
    }

    private function whenAuthTokenWasAdded(AuthTokenWasAdded $tokenWasAdded): self
    {
        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateRevision' => $tokenWasAdded->getAggregateRevision()->toNative(),
                'tokens' => [[
                    'id' => $tokenWasAdded->getId()->toNative(),
                    'token' => $tokenWasAdded->getToken()->toNative(),
                    'expiresAt' => $tokenWasAdded->getExpiresAt()->toNative(),
                    '@type' => AuthToken::class
                ]]
            ]
        ));
    }

    private function whenVerifyTokenWasAdded(VerifyTokenWasAdded $tokenWasAdded): self
    {
        //@todo better token merging
        return self::fromNative(array_merge_recursive(
            array_merge(
                $this->state,
                ['aggregateRevision' => $tokenWasAdded->getAggregateRevision()->toNative()]
            ),
            [
                'tokens' => [[
                    'id' => $tokenWasAdded->getId()->toNative(),
                    'token' => $tokenWasAdded->getToken()->toNative(),
                    '@type' => VerifyToken::class
                ]]
            ]
        ));
    }

    private function whenVerifyTokenWasRemoved(VerifyTokenWasRemoved $tokenWasRemoved): self
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($token['@type'] !== VerifyToken::class) {
                $tokens[] = $token;
            }
        }
        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateRevision' => $tokenWasRemoved->getAggregateRevision()->toNative(),
                'tokens' => $tokens
            ]
        ));
    }

    private function whenUserWasLoggedIn(UserWasLoggedIn $userWasLoggedIn): self
    {
        //@todo better token updating
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($userWasLoggedIn->getAuthTokenId()->toNative() === $token['id']) {
                $token['expiresAt'] = $userWasLoggedIn->getAuthTokenExpiresAt()->toNative();
            }
            $tokens[] = $token;
        }

        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasLoggedIn->getAggregateRevision()->toNative(),
                'tokens' => $tokens
            ]
        ));
    }

    private function whenUserWasLoggedOut(UserWasLoggedOut $userWasLoggedOut): self
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($userWasLoggedOut->getAuthTokenId()->toNative() === $token['id']) {
                $token['token'] = $userWasLoggedOut->getAuthToken()->toNative();
                $token['expiresAt'] = $userWasLoggedOut->getAuthTokenExpiresAt()->toNative();
            }
            $tokens[] = $token;
        }

        return self::fromNative(array_merge(
            $this->state,
            [
                'aggregateRevision' => $userWasLoggedOut->getAggregateRevision()->toNative(),
                'tokens' => $tokens
            ]
        ));
    }
}
