<?php

namespace Dlx\Security\User\Controller;

use Dlx\Security\User\View\ResourceSuccessView;
use Dlx\Security\Voter\OwnershipVoter;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class ResourceController
{
    private $userProvider;

    private $authorizationChecker;

    public function __construct(
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->userProvider = $userProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function read(Request $request, Application $app)
    {
        $userId = $request->get('userId');
        $user = $this->userProvider->loadUserByIdentifier($userId);

//         if (!$this->authorizationChecker->isGranted([OwnershipVoter::PERMISSION_VIEW, 'ROLE_ADMIN'], $user)) {
//             throw new AccessDeniedException;
//         }

        $request->attributes->set('user', $user);

        return [ResourceSuccessView::CLASS];
    }
}
