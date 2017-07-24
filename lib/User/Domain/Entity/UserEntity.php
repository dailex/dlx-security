<?php

namespace Dlx\Security\User\Domain\Entity;

use Daikon\Entity\Entity\Entity;
use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\Entity\ValueObject\ValueObjectInterface;
use Daikon\EventSourcing\Aggregate\AggregateId;
use Dlx\Security\User\Domain\ValueObject\UserRole;
use Dlx\Security\User\Domain\ValueObject\UserState;
use Daikon\Entity\Entity\NestedEntityList;

final class UserEntity extends Entity
{
    public function getIdentity(): ValueObjectInterface
    {
        return $this->get('identity');
    }

    public function withIdentity(Uuid $identity): self
    {
        return $this->withValue('identity', $identity);
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
        return $this->get('password_hash');
    }

    public function withPasswordHash(Text $passwordHash): self
    {
        return $this->withValue('password_hash', $passwordHash);
    }

    public function getFirstname(): Text
    {
        return $this->get('firstname');
    }

    public function withFirstname(Text $firstname): self
    {
        return $this->withValue('firstname', $firstname);
    }

    public function getLastname(): Text
    {
        return $this->get('lastname');
    }

    public function withLastname(Text $lastname): self
    {
        return $this->withValue('lastname', $lastname);
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

    public function getTokens(): NestedEntityList
    {
        return $this->get('tokens');
    }

    public function withAuthTokenAdded(array $tokenPayload): self
    {
        return $this->addToken($tokenPayload, "auth_token");
    }

    private function addToken(array $tokenPayload, string $type): self
    {
        $tokensAttribute = $this->getEntityType()->getAttribute('tokens');
        $tokenType = $tokensAttribute->getValueType()->get($type);
        $token = $tokenType->makeEntity($tokenPayload, $this);
        $userState = $this->withValue('tokens', $this->getTokens()->push($token));
        return $userState;
    }
}
