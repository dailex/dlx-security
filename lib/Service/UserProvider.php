<?php

namespace Dlx\Security\Service;

use Daikon\Elasticsearch5\Query\Elasticsearch5Query;
use Daikon\ReadModel\Repository\RepositoryInterface;
use Daikon\ReadModel\Repository\RepositoryMap;
use Dlx\Security\User\Repository\DailexUserInterface;
use Gigablah\Silex\OAuth\Security\Authentication\Token\OAuthTokenInterface;
use Gigablah\Silex\OAuth\Security\User\Provider\OAuthUserProviderInterface;
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

    public function loadUserByIdentifier(string $identifier): DailexUserInterface
    {
        $user = $this->getUserRepository()->findById($identifier);

        if (!$user) {
            throw new UsernameNotFoundException;
        }

        return $user;
    }

    public function loadUserByUsername($username): DailexUserInterface
    {
        $users = $this->getUserRepository()->search(new Elasticsearch5Query([
            'query' => [
                //@todo make sure this is filter context
                'bool' => [
                    'should' => [
                        ['term' => ['username' => $username]],
                        ['term' => ['email' => $username]]
                    ]
                ]
            ]
        ]), 0, 2);

        if ($users->count() !== 1) {
            throw new UsernameNotFoundException;
        }

        return $users->getIterator()->current();
    }

    public function loadUserByToken(string $token, string $type): DailexUserInterface
    {
        //@todo check token type
        $users = $this->getUserRepository()->search(new Elasticsearch5Query([
            'query' => [
                'term' => ['tokens.token' => $token]
            ]
        ]), 0, 2);

        if ($users->count() !== 1) {
            throw new UsernameNotFoundException;
        }

        return $users->getIterator()->current();
    }

    public function loadUserByEmail(string $email): DailexUserInterface
    {
        $users = $this->getUserRepository()->search(new Elasticsearch5Query([
            'query' => [
                'term' => ['email' => $email]
            ]
        ]), 0, 2);

        if ($users->count() !== 1) {
            throw new UsernameNotFoundException;
        }

        return $users->getIterator()->current();
    }

    public function loadUserByOAuthCredentials(OAuthTokenInterface $token): DailexUserInterface
    {
    }

    public function userExists(string $username, string $email, array $ignoreIds = []): bool
    {
        $users = $this->getUserRepository()->search(new Elasticsearch5Query, 0, 1);

        return $users->count() > 0;
    }

    public function refreshUser(UserInterface $user): DailexUserInterface
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException;
        }

        return $this->loadUserByIdentifier($user->getAggregateId());
    }

    public function supportsClass($class)
    {
        return is_a($class, DailexUserInterface::class, true);
    }

    private function getUserRepository(): RepositoryInterface
    {
        return $this->repostitoryMap->get('dlx.security.user.standard');
    }
}
