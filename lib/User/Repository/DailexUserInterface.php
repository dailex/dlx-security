<?php

namespace Dlx\Security\User\Repository;

use Daikon\ReadModel\Projection\ProjectionInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface DailexUserInterface extends AdvancedUserInterface, ProjectionInterface
{
    public function getEmail(): string;

    public function getLocale(): string;

    public function getTokens(): array;

    public function getToken(string $type): ?array;

    public function isAuthTokenNonExpired(): bool;
}
