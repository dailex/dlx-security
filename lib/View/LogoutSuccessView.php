<?php

namespace Dlx\Security\View;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LogoutSuccessView
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function renderHtml(Request $request, Application $app)
    {
        /*
         * User is not logged out unless session token is removed. Firewall logout listener
         * would normally take care of this if the firewall is configured to do so.
         */
        return $app->redirect($this->urlGenerator->generate('home'));
    }

    public function renderJson(Request $request, Application $app)
    {
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
