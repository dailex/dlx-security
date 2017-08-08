<?php

namespace Dlx\Security\User\Domain\ValueObject;

use Assert\Assertion;
use Daikon\Entity\Entity\EntityListInterface;
use Daikon\Entity\Entity\EntityListTrait;
use Dlx\Security\User\Domain\Entity\AuthToken;
use Dlx\Security\User\Domain\Entity\VerifyToken;
use Ds\Vector;

final class TokenList implements EntityListInterface
{
    use EntityListTrait;

    private function __construct(array $tokens = [])
    {
        Assertion::allIsInstanceOf($tokens, [ AuthToken::class, VerifyToken::class ]);
        $this->compositeVector = new Vector($tokens);
    }
}
