<?php

namespace Dlx\Security\Service;

use Daikon\EventSourcing\Aggregate\Event\DomainEventInterface;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\ReadModel\Repository\RepositoryMap;
use Dailex\Mailer\MailInterface;
use Dailex\Mailer\MailerServiceInterface;
use Dailex\Mailer\Message;
use Dailex\Renderer\TemplateRendererInterface;
use Dlx\Security\User\Domain\Event\UserWasRegistered;
use Dlx\Security\User\Repository\Standard\User;
use Symfony\Component\Translation\TranslatorInterface;

final class UserMailService implements MessageHandlerInterface
{
    private $mailerService;

    private $repositoryMap;

    private $templateRenderer;

    private $translator;

    private $settings;

    public function __construct(
        MailerServiceInterface $mailerService,
        RepositoryMap $repoistoryMap,
        TemplateRendererInterface $templateRenderer,
        TranslatorInterface $translator,
        array $settings
    ) {
        $this->mailerService = $mailerService;
        $this->repositoryMap = $repoistoryMap;
        $this->templateRenderer = $templateRenderer;
        $this->translator = $translator;
        $this->settings = $settings;
    }

    public function handle(EnvelopeInterface $envelope): bool
    {
        return $this->invokeEventHandler($envelope->getMessage());
    }

    private function whenUserWasRegistered(UserWasRegistered $userWasRegistered): bool
    {
        $user = $this->loadUser($userWasRegistered->getAggregateId());
        $message = $this->createMessageFromTemplate(
            'account_verification',
            $user,
            [
                'name' => $this->getName($user),
                'token' => $user->getToken('verify_token')['token']
            ]
        );

        $message->setSubject($this->trans('Activate your account', $user));
        $result = $this->send($message);

        return true;
    }

    private function send(MailInterface $message)
    {
        // Handling for persistent transport connections such as SES
        $result = $this->mailerService->send($message);
        $this->mailerService->getMailer()->getTransport()->stop();
        return $result;
    }

    private function getName(User $user)
    {
        $name = trim($user->getFirstname().' '.$user->getLastname());
        return $name ?: $user->getUsername();
    }

    private function createMessageFromTemplate($template, User $user, array $templateVars = [])
    {
        $message = new Message;

        $message->setFrom([$this->settings['from_email'] => $this->settings['from_name'] ?? '']);
        $message->setTo([$user->getEmail() => $this->getName($user)]);

        if ($senderEmail = $this->settings['sender_email'] ?? null) {
            $message->setSender([$senderEmail => $this->settings['sender_name'] ?? '']);
        }

        if ($replyEmail = $this->settings['reply_email']) {
            $message->setReplyTo([$replyEmail => $this->settings['reply_name'] ?? '']);
        }

        $bodyText = $this->templateRenderer->render(
            sprintf('@dlx.security/email/%s.%s.txt.twig', $template, $user->getLocale()),
            $templateVars
        );

        $message->setBodyText($bodyText);

        return $message;
    }

    private function trans($key, User $user, array $params = [])
    {
        return $this->translator->trans($key, $params, 'email', $user->getLocale());
    }

    private function loadUser(string $identifier): User
    {
        return $this->repositoryMap->get('dlx.security.user.standard')->findById($identifier);
    }

    private function invokeEventHandler(DomainEventInterface $event): bool
    {
        $handlerName = preg_replace('/Event$/', '', (new \ReflectionClass($event))->getShortName());
        $handlerMethod = 'when'.ucfirst($handlerName);
        $handler = [$this, $handlerMethod];
        if (!is_callable($handler)) {
            return true;
        }
        return call_user_func($handler, $event);
    }
}
