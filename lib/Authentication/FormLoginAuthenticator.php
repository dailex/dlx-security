<?php

namespace Dlx\Security\Authentication;

use Dlx\Security\Service\UserManager;
use Dlx\Security\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class FormLoginAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $userProvider;

    private $passwordEncoder;

    private $userManager;

    private $urlGenerator;

    public function __construct(
        UserProviderInterface $userProvider,
        PasswordEncoderInterface $passwordEncoder,
        UserManager $userManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function getCredentials(Request $request)
    {
        if ($request->getRequestUri() !== $this->urlGenerator->generate('dlx.security.authenticate')) {
            return;
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if ($request->hasSession()) {
            $request->getSession()->set(Security::LAST_USERNAME, $username);
        }

        return $username && $password ? ['username' => $username, 'password' => $password] : null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $userProvider->loadUserByUsername($credentials['username']);

        return new User($user->toArray());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!$this->passwordEncoder->isPasswordValid(
            $user->getPassword(),
            $credentials['password'],
            $user->getSalt()
        )) {
            throw new BadCredentialsException;
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        sleep(1);
        return parent::onAuthenticationFailure($request, $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->userManager->loginUser($token->getUser());

        // @todo configurable target path
        $targetPath = null;

        if ($request->hasSession()) {
            $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        }

        if (!$targetPath) {
            $targetPath = $this->urlGenerator->generate('home');
        }

        return new RedirectResponse($targetPath);
    }

    private function getLoginUrl()
    {
        return $this->urlGenerator->generate('dlx.security.login');
    }
}
