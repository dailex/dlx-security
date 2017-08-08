<?php

namespace Dlx\Security\Service;

use Daikon\Config\ConfigProviderInterface;
use Daikon\Entity\ValueObject\Timestamp;
use Daikon\MessageBus\MessageBusInterface;
use Dlx\Security\User\Domain\Command\ActivateUser;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\Command\LogoutUser;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Dlx\Security\User\Domain\Entity\AuthToken;
use Dlx\Security\User\Repository\DailexUserInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Translation\TranslatorInterface;

final class UserManager
{
    private $messageBus;

    private $configProvider;

    private $passwordEncoder;

    private $translator;

    public function __construct(
        MessageBusInterface $messageBus,
        ConfigProviderInterface $configProvider,
        PasswordEncoderInterface $passwordEncoder,
        TranslatorInterface $translator
    ) {
        $this->messageBus = $messageBus;
        $this->configProvider = $configProvider;
        $this->passwordEncoder = $passwordEncoder;
        $this->translator = $translator;
    }

    public function registerUser(array $values, $role = null): void
    {
        $aggregateId = sprintf(
            'dlx.security.user-%s-%s-1',
            Uuid::uuid4()->toString(),
            $values['locale'] ?? $this->translator->getLocale()
        );

        $registerUser = RegisterUser::fromArray([
            'aggregateId' => $aggregateId,
            'username' => $values['username'],
            'email' => $values['email'],
            'role' => $values['role'] ?? $this->getDefaultRole(),
            'locale' => $values['locale'] ?? $this->translator->getLocale(),
            'passwordHash' => $this->passwordEncoder->encodePassword($values['password'], null),
            'authTokenExpiresAt' => gmdate(Timestamp::NATIVE_FORMAT, strtotime('+1 month'))
        ]);

        $this->messageBus->publish($registerUser, 'commands');
    }

    public function loginUser(DailexUserInterface $user): void
    {
        $this->guardUserStatus($user);

        $loginUser = LoginUser::fromArray([
            'aggregateId' => $user->getAggregateId(),
            'authTokenId' => $user->getToken(AuthToken::class)['id'],
            'authTokenExpiresAt' => gmdate(Timestamp::NATIVE_FORMAT, strtotime('+1 month'))
        ]);

        $this->messageBus->publish($loginUser, 'commands');
    }

    public function logoutUser(DailexUserInterface $user): void
    {
        $this->guardUserStatus($user);

        $logoutUser = LogoutUser::fromArray([
            'aggregateId' => $user->getAggregateId(),
            'authTokenId' => $user->getToken(AuthToken::class)['id']
        ]);

        $this->messageBus->publish($logoutUser, 'commands');
    }

    public function activateUser(DailexUserInterface $user): void
    {
        $this->guardUserStatus($user);

        $activateUser = ActivateUser::fromArray([
            'aggregateId' => $user->getAggregateId()
        ]);

        $this->messageBus->publish($activateUser, 'commands');
    }

    private function getAvailableRoles(): array
    {
        return (array)$this->configProvider->get(
            'dlx.security.roles.available_roles',
            ['user', 'administrator']
        );
    }

    private function getDefaultRole(): string
    {
        return $this->configProvider->get('dlx.security.roles.default_role', 'user');
    }

    private function guardUserStatus(DailexUserInterface $user): void
    {
        if (!$user->isAccountNonLocked()) {
            throw new LockedException;
        }

        if (!$user->isEnabled()) {
            throw new DisabledException;
        }
    }
}
