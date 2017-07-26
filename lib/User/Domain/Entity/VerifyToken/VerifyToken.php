<?php

namespace Dlx\Security\User\Domain\Entity\VerifyToken;

use Daikon\Entity\Entity\NestedEntity;
use Daikon\Entity\ValueObject\Uuid;
use Daikon\Entity\ValueObject\ValueObjectInterface;
use Dlx\Security\User\Domain\ValueObject\RandomToken;

final class VerifyToken extends NestedEntity
{
    public function getIdentity(): ValueObjectInterface
    {
        return $this->getId();
    }

    public function getId(): Uuid
    {
        return $this->get('id');
    }

    public function getToken(): RandomToken
    {
        return $this->get('token');
    }
}
