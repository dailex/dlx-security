<?php

namespace Dlx\Security\Voter;

use Dlx\Security\User\Repository\DailexUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OwnershipVoter extends Voter
{
    const PERMISSION_VIEW = 'PERMISSION_VIEW';
    const PERMISSION_EDIT = 'PERMISSION_EDIT';

    public function supports($attribute, $subject)
    {
        return in_array($attribute, [self::PERMISSION_VIEW, self::PERMISSION_EDIT])
            && $subject instanceof DailexUserInterface;
    }

    protected function voteOnAttribute($attribute, $user, TokenInterface $token)
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof DailexUserInterface) {
            return false;
        }
        return $user->getAggregateId() === $currentUser->getAggregateId();
    }
}
