<?php

namespace Dlx\Security\Service;

use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
use Daikon\ReadModel\Repository\RepositoryMap;
use Dlx\Security\User\OauthUser;
use Dlx\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface, OAuthUserProviderInterface
{
    private $repostitoryMap;

    private $userManager;

    private $tokenStorage;

    public function __construct(
        RepositoryMap $repostitoryMap,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->repostitoryMap = $repostitoryMap;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function loadUserByIdentifier($identifier)
    {
        $user = $this->getUserRepository()->findById($identifier);

        if (!$user) {
            throw new UsernameNotFoundException;
        }

        return new User($user->toArray());
    }

    public function loadUserByUsername($username)
    {
    }

    public function loadUserByToken($token, $type)
    {
    }

    public function loadUserByEmail($email)
    {
    }

    public function loadUserByOAuthCredentials(OAuthTokenInterface $token)
    {
    }

    public function userExists($username, $email, array $ignoreIds = [])
    {
        $users = $this->getUserRepository()->search([
            //@query
        ], 0, 1);

        return $users->count() > 0;
    }

    public function refreshUser(UserInterface $user)
    {
    }

    public function supportsClass($class)
    {
        return User::CLASS === $class || is_subclass_of($class, User::CLASS);
    }

    private function getUserRepository()
    {
        return $this->repostitoryMap->get('dlx.security.user.standard');
    }
}
