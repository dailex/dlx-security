<?php

namespace Dlx\Security\Service;

use Daikon\Config\ConfigProviderInterface;
use Daikon\MessageBus\MessageBusInterface;
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

    public function registerUser(array $values, $role = null)
    {
        if (isset($values['password'])) {
            $values['password_hash'] = $this->passwordEncoder->encodePassword($values['password'], null);
            unset($values['password']);
        }

        if (!isset($values['locale'])) {
            $values['locale'] = $this->translator->getLocale();
        }

        if (!isset($values['role'])) {
            $values['role'] = $this->getDefaultRole();
        }

        if (!isset($values['aggregateId'])) {
            $values['aggregateId'] = sprintf(
                'dlx.security.user-%s-%s-1',
                Uuid::uuid4()->toString(),
                $values['locale']
            );
        }

        $registerUser = RegisterUser::fromArray($values);
        $this->messageBus->publish($registerUser, 'commands');
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
