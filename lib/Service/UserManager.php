<?php

namespace Dlx\Security\Service;

use Daikon\Config\ConfigProviderInterface;
use Daikon\Entity\ValueObject\Timestamp;
use Daikon\MessageBus\MessageBusInterface;
use Dlx\Security\User\Domain\Command\LoginUser;
use Dlx\Security\User\Domain\Command\LogoutUser;
use Dlx\Security\User\Domain\Command\RegisterUser;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
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
            'firstname' => $values['firstname'],
            'lastname' => $values['lastname'],
            'locale' => $values['locale'] ?? $this->translator->getLocale(),
            'passwordHash' => $this->passwordEncoder->encodePassword($values['password'], null),
            'authTokenExpiresAt' => gmdate(Timestamp::NATIVE_FORMAT, strtotime('+1 month'))
        ]);

        $this->messageBus->publish($registerUser, 'commands');
    }

    public function loginUser(AdvancedUserInterface $user): void
    {
        $loginUser = LoginUser::fromArray([
            'aggregateId' => $user->getAggregateId(),
            'authTokenId' => $user->getToken('auth_token')['id'],
            'authTokenExpiresAt' => gmdate(Timestamp::NATIVE_FORMAT, strtotime('+1 month'))
        ]);

        $this->messageBus->publish($loginUser, 'commands');
    }

    public function logoutUser(AdvancedUserInterface $user): void
    {
        $logoutUser = LogoutUser::fromArray([
            'aggregateId' => $user->getAggregateId(),
            'authTokenId' => $user->getToken('auth_token')['id']
        ]);

        $this->messageBus->publish($logoutUser, 'commands');
    }

    public function getDefaultRole()
    {
        return $this->configProvider->get('dlx.security.roles.default_role', 'user');
    }

    public function getAvailableRoles()
    {
        return (array)$this->configProvider->get(
            'dlx.security.roles.available_roles',
            ['user', 'administrator']
        );
    }

    private function guardUserStatus(AdvancedUserInterface $user)
    {
        if (!$user->isAccountNonLocked()) {
            throw new LockedException;
        }

        if (!$user->isEnabled()) {
            throw new DisabledException;
        }
    }
}
