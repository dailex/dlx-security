<?php

namespace Dlx\Security\User\View;

use Dailex\Renderer\TemplateRendererInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final class CollectionSuccessView
{
    private $templateRenderer;

    private $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        SerializerInterface $serializer
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->serializer = $serializer;
    }

    public function renderHtml(Request $request, Application $app)
    {
        $users = $request->attributes->get('users');

        return $this->templateRenderer->render(
            '@dlx.security/user/collection.html.twig',
            ['q' => $request->query->get('q'), 'users' => $users]
        );
    }

    public function renderJson(Request $request, Application $app)
    {
        $users = $request->attributes->get('users');

        return new JsonResponse(
            $this->serializer->serialize($users, 'json'),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
