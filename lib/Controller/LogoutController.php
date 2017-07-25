<?php

namespace Dlx\Security\Controller;

use Dlx\Security\Service\UserManager;
use Dlx\Security\View\LogoutSuccessView;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class LogoutController
{
    private $tokenStorage;

    private $userManager;

    public function __construct(TokenStorageInterface $tokenStorage, UserManager $userManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
    }

    /*
     * Controller for API token based logout only.
     * Standard logout occurs via firewall logout configuration.
     */
    public function write(Request $request, Application $app)
    {
        $token = $this->tokenStorage->getToken();

        $this->userManager->logoutUser($token->getUser());

        return [LogoutSuccessView::class];
    }
}
