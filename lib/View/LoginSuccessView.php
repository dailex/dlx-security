<?php

namespace Dlx\Security\View;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class LoginSuccessView
{
    private $serializer;

    private $urlGenerator;

    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    public function renderHtml(Request $request, Application $app)
    {
        /*
         * User is not logged in unless session token is set. Firewall authenticator
         * would normally take care of this if the firewall is configured to do so.
         */
        return $app->redirect($this->urlGenerator->generate('home'));
    }

    public function renderJson(Request $request, Application $app)
    {
        $user = $request->attributes->get('user');

        return new JsonResponse(
            $this->serializer->serialize($user, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
