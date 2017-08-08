<?php

namespace Dlx\Security\User\Domain\Entity;

use Daikon\Entity\Entity\Attribute;
use Daikon\Entity\Entity\AttributeMap;
use Daikon\Entity\Entity\Entity;
use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\Entity\ValueObject\ValueObjectInterface;
use Dlx\Security\User\Domain\ValueObject\TokenList;
use Dlx\Security\User\Domain\ValueObject\UserRole;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class UserEntity extends Entity
{
    public static function getAttributeMap(): AttributeMap
    {
        return new AttributeMap([
            Attribute::define('username', Text::class),
            Attribute::define('email', Email::class),
            Attribute::define('role', UserRole::class),
            Attribute::define('locale', Text::class),
            Attribute::define('passwordHash', Text::class),
            Attribute::define('state', UserState::class),
            Attribute::define('tokens', TokenList::class)
        ]);
    }

    public function getIdentity(): ValueObjectInterface
    {
        return $this->get('username');
    }

    public function getUsername(): Text
    {
        return $this->get('username');
    }

    public function withUsername(Text $username): self
    {
        return $this->withValue('username', $username);
    }

    public function getEmail(): Email
    {
        return $this->get('email');
    }

    public function withEmail(Email $email): self
    {
        return $this->withValue('email', $email);
    }

    public function getRole(): UserRole
    {
        return $this->get('role');
    }

    public function withRole(UserRole $role): self
    {
        return $this->withValue('role', $role);
    }

    public function getPasswordHash(): Text
    {
        return $this->get('passwordHash');
    }

    public function withPasswordHash(Text $passwordHash): self
    {
        return $this->withValue('passwordHash', $passwordHash);
    }

    public function getLocale(): Text
    {
        return $this->get('locale');
    }

    public function withLocale(Text $locale): self
    {
        return $this->withValue('locale', $locale);
    }

    public function getState(): UserState
    {
        return $this->get('state');
    }

    public function withState(UserState $state): self
    {
        return $this->withValue('state', $state);
    }

    public function getTokens(): TokenList
    {
        return $this->get('tokens') ?? TokenList::makeEmpty();
    }

    public function withAuthTokenAdded(AuthToken $authToken): self
    {
        return $this->withValue('tokens', $this->getTokens()->push($authToken));
    }

    public function withVerifyTokenAdded(VerifyToken $verifyToken): self
    {
        return $this->withValue('tokens', $this->getTokens()->push($verifyToken));
    }

    public function withVerifyTokenRemoved(): self
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if (!$token instanceof VerifyToken) {
                $tokens[] = $token;
            }
        }
        return $this->withValue('tokens', TokenList::wrap($tokens));
    }

    public function withUserLoggedIn(array $payload): self
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($token->getIdentity()->equals($payload['id'])) {
                $token = $token->withValue('expiresAt', $payload['expiresAt']);
            }
            $tokens[] = $token;
        }
        return $this->withValue('tokens', TokenList::wrap($tokens));
    }

    public function withUserLoggedOut(array $payload): self
    {
        $tokens = [];
        foreach ($this->getTokens() as $token) {
            if ($token->getIdentity()->equals($payload['id'])) {
                $token = $token
                    ->withValue('token', $payload['token'])
                    ->withValue('expiresAt', $payload['expiresAt']);
            }
            $tokens[] = $token;
        }
        return $this->withValue('tokens', TokenList::wrap($tokens));
    }

    public function withUserActivated(array $payload)
    {
        return $this->withState($payload['state']);
    }
}
