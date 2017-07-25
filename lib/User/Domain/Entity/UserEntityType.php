<?php

namespace Dlx\Security\User\Domain\Entity;

use Daikon\Entity\EntityType\Attribute;
use Daikon\Entity\EntityType\EntityType;
use Daikon\Entity\EntityType\NestedEntityListAttribute;
use Daikon\Entity\Entity\TypedEntityInterface;
use Daikon\Entity\ValueObject\Email;
use Daikon\Entity\ValueObject\Text;
use Daikon\Entity\ValueObject\Uuid;
use Dlx\Security\User\Domain\Entity\AuthToken\AuthTokenType;
use Dlx\Security\User\Domain\ValueObject\UserRole;
use Dlx\Security\User\Domain\ValueObject\UserState;

final class UserEntityType extends EntityType
{
    public function __construct()
    {
        parent::__construct('User', [
            Attribute::define('identity', Uuid::class, $this),
            Attribute::define('username', Text::class, $this),
            Attribute::define('email', Email::class, $this),
            Attribute::define('role', UserRole::class, $this),
            Attribute::define('firstname', Text::class, $this),
            Attribute::define('lastname', Text::class, $this),
            Attribute::define('locale', Text::class, $this),
            Attribute::define('passwordHash', Text::class, $this),
            Attribute::define('state', UserState::class, $this),
            NestedEntityListAttribute::define('tokens', [
                AuthTokenType::class
            ], $this)
        ]);
    }

    public function makeEntity(array $userState = [], TypedEntityInterface $parent = null): TypedEntityInterface
    {
        $userState['@type'] = $this;
        $userState['@parent'] = $parent;
        return UserEntity::fromArray($userState);
    }
}
