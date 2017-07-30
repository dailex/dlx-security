<?php

namespace Dlx\Security\User\Domain\Entity\AuthToken;

use Daikon\Entity\Entity\EntityInterface;
use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\AttributeInterface;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\ValueObject\Timestamp;
use Daikon\Entity\ValueObject\Uuid;
use Dlx\Security\User\Domain\ValueObject\RandomToken;

final class AuthTokenType extends EntityType
{
    public static function getName(): string
    {
        return 'AuthToken';
    }

    public function __construct(AttributeInterface $parentAttribute)
    {
        parent::__construct([
            Attribute::define('id', Uuid::class, $this),
            Attribute::define('token', RandomToken::class, $this),
            Attribute::define('expiresAt', Timestamp::class, $this)
        ], $parentAttribute);
    }

    public function makeEntity(array $tokenState = [], EntityInterface $parent = null): EntityInterface
    {
        $tokenState['@type'] = $this;
        $tokenState['@parent'] = $parent;
        return AuthToken::fromNative($tokenState);
    }
}
