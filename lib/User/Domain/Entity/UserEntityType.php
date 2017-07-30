<?php

namespace Dlx\Security\User\Domain\Entity;

use Daikon\Entity\Entity\EntityInterface;
use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\EntityType\NestedEntityListAttribute;
use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Uuid;
use Dlx\Security\User\Domain\Entity\AuthToken\AuthTokenType;
use Dlx\Security\User\Domain\Entity\VerifyToken\VerifyTokenType;
use Dlx\Security\User\Domain\ValueObject\UserRole;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class UserEntityType extends EntityType
{
    public static function getName(): string
    {
        return 'User';
    }

    public function __construct()
    {
        parent::__construct([
            Attribute::define('identity', Uuid::class, $this),
            Attribute::define('username', Text::class, $this),
            Attribute::define('email', Email::class, $this),
            Attribute::define('role', UserRole::class, $this),
            Attribute::define('locale', Text::class, $this),
            Attribute::define('passwordHash', Text::class, $this),
            Attribute::define('state', UserState::class, $this),
            NestedEntityListAttribute::define('tokens', [
                AuthTokenType::class,
                VerifyTokenType::class
            ], $this)
        ]);
    }

    public function makeEntity(array $userState = [], EntityInterface $parent = null): EntityInterface
    {
        $userState['@type'] = $this;
        $userState['@parent'] = $parent;
        return UserEntity::fromNative($userState);
    }
}
