<?php

namespace Dlx\Security\User\Domain\Entity\VerifyToken;

use Daikon\Entity\Entity\TypedEntityInterface;
use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\AttributeInterface;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\ValueObject\Uuid;
use Dlx\Security\User\Domain\ValueObject\RandomToken;

final class VerifyTokenType extends EntityType
{
    public function __construct(AttributeInterface $parentAttribute)
    {
        parent::__construct('VerifyToken', [
            Attribute::define('id', Uuid::class, $this),
            Attribute::define('token', RandomToken::class, $this)
        ], $parentAttribute);
    }

    public function makeEntity(array $tokenState = [], TypedEntityInterface $parent = null): TypedEntityInterface
    {
        $tokenState['@type'] = $this;
        $tokenState['@parent'] = $parent;
        return VerifyToken::fromArray($tokenState);
    }
}
