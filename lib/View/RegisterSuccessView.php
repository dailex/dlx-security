<?php

namespace Dlx\Security\View;

use Daikon\Config\ConfigProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RegisterSuccessView
{
    private $configProvider;

    private $urlGenerator;

    public function __construct(ConfigProviderInterface $configProvider, UrlGeneratorInterface $urlGenerator)
    {
        $this->configProvider = $configProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function renderHtml(Request $request, Application $app)
    {
        $targetPath = $this->configProvider->get('crates.dlx.security.auto_login.enabled') && $request->hasSession()
            ? $this->configProvider->get('crates.dlx.security.auto_login.target_path', 'home')
            : 'dlx.security.login';

        return $app->redirect($this->urlGenerator->generate($targetPath));
    }

    public function renderJson(Request $request, Application $app)
    {
        return new JsonResponse(null, JsonResponse::HTTP_CREATED);
    }
}
