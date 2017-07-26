<?php

namespace Dlx\Security\Controller;

use Daikon\Config\ConfigProviderInterface;
use Dlx\Security\Service\UserManager;
use Dlx\Security\View\RegistrationInputView;
use Dlx\Security\View\RegistrationSuccessView;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegistrationController
{
    private $formFactory;

    private $userManager;

    private $userProvider;

    private $tokenStorage;

    private $configProvider;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserManager $userManager,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        ConfigProviderInterface $configProvider
    ) {
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
        $this->configProvider = $configProvider;
    }

    public function read(Request $request, Application $app)
    {
        $form = $this->buildForm();
        $request->attributes->set('form', $form);

        return [RegistrationInputView::class];
    }

    public function write(Request $request, Application $app)
    {
        $form = $this->buildForm();
        $form->handleRequest($request);
        $request->attributes->set('form', $form);

        if (!$form->isValid()) {
            return [RegistrationInputView::class];
        }

        $formData = $form->getData();
        $username = $formData['username'];
        $email = $formData['email'];

        try {
            if (!$this->userProvider->userExists($username, $email)) {
                $this->userManager->registerUser($formData);
                // auto login handling - expects registration to be synchronous
                if ($this->configProvider->get('crates.dlx.security.auto_login.enabled') && $request->hasSession()) {
                    $firewall = $this->configProvider->get('crates.dlx.security.auto_login.firewall', 'default');
                    $user = $this->userProvider->loadUserByEmail($email);
                    $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
                    $this->tokenStorage->setToken($token);
                    $request->getSession()->set('_security_'.$firewall, serialize($token));
                }
                return [RegistrationSuccessView::class];
            }
        } catch (AuthenticationException $error) {
            $errors = (array)$error->getMessageKey();
        }

        $request->attributes->set('errors', $errors ?? ['User is already registered.']);
        return [RegistrationInputView::class];
    }

    private function buildForm()
    {
        return $this->formFactory->createNamedBuilder(
            null,
            FormType::class,
            [],
            // @todo remove allow_extra_fields when recaptcha can be created via form builder
            ['translation_domain' => 'form', 'allow_extra_fields' => true]
        )
            ->add('username', TextType::class, ['constraints' => [new NotBlank, new Length(['min' => 4])]])
            ->add('email', EmailType::class, ['constraints' => new NotBlank])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [new NotBlank, new Length(['min' => 5])],
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']
            ])
            ->add('firstname', TextType::class, ['required' => false])
            ->add('lastname', TextType::class, ['required' => false])
            ->getForm();
    }
}
