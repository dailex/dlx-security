<?php

namespace Dlx\Security\EventListener;

use Dlx\Security\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

final class UserLogoutListener implements LogoutSuccessHandlerInterface
{
    private $httpUtils;

    private $tokenStorage;

    private $userManager;

    private $targetUrl;

    public function __construct(
        HttpUtils $httpUtils,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        $targetUrl = '/'
    ) {
        $this->httpUtils = $httpUtils;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->targetUrl = $targetUrl;
    }

    public function onLogoutSuccess(Request $request)
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            try {
                $this->userManager->logoutUser($user);
            } catch (\Exception $error) {
            }
        }

        return $this->httpUtils->createRedirectResponse($request, $this->targetUrl);
    }
}
