<?php

namespace Dlx\Security\Controller;

use Dlx\Security\Service\UserManager;
use Dlx\Security\View\LoginInputView;
use Dlx\Security\View\LoginSuccessView;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class LoginController
{
    private $formFactory;

    private $userProvider;

    private $passwordEncoder;

    private $userManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserProviderInterface $userProvider,
        PasswordEncoderInterface $passwordEncoder,
        UserManager $userManager
    ) {
        $this->formFactory = $formFactory;
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
        $this->userManager = $userManager;
    }

    public function read(Request $request, Application $app)
    {
        $lastUsername = $request->getSession()->get(Security::LAST_USERNAME);
        $form = $this->buildForm($lastUsername);
        $request->attributes->set('form', $form);

        return [LoginInputView::class];
    }

    /*
     * Controller for API token based login only.
     * Standard login occurs via firewall guard authentication.
     */
    public function write(Request $request, Application $app)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = $this->userProvider->loadUserByUsername($username);
        if (!$this->passwordEncoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            throw new BadCredentialsException;
        }

        $this->userManager->loginUser($user);
        // get latest revision of user
        $user = $this->userProvider->loadUserByIdentifier($user->getAggregateId());
        $request->attributes->set('user', $user);

        return [LoginSuccessView::class];
    }

    private function buildForm($lastUsername)
    {
        return $this->formFactory->createNamedBuilder(
            null,
            FormType::CLASS,
            ['username' => $lastUsername],
            ['translation_domain' => 'form']
        )
            ->add('username', EmailType::CLASS, [
                // constrain to email because of potential Oauth related username duplication
                'constraints' => [new NotBlank],
                'label' => 'Email Address'
            ])
            ->add('password', PasswordType::CLASS, ['constraints' => new NotBlank])
            ->add('remember_me', CheckboxType::CLASS, ['data' => true, 'required' => false])
            ->getForm();
    }
}
