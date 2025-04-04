<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Captcha;
use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class RestApiController extends AbstractController
{
    public function __construct(
        private readonly Captcha $captcha,
        private readonly DataService $dataService,
        #[Autowire(env: 'CONTACT_EMAIL')]
        private readonly string $contactEmail,
    ) {
    }

    #[Route(path: '/api/', name: RouteName::API)]
    #[Cache(maxage: 3600, public: true)]
    public function api(): Response
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/contact/email-part.html')]
    #[Cache(maxage: 0, public: false)]
    public function contactEmailHtml(Request $request): Response
    {
        $ok = $this->captcha->isValid($request, 'info_emailHtml');

        if ($ok) {
            $contactEmail = htmlspecialchars($this->contactEmail);

            return new Response("<a href=\"mailto:$contactEmail\" class=\"btn btn-primary my-1 btn-sm\"><i class=\"fas fa-envelope\"></i> $contactEmail</a>");
        } else {
            return new Response('', Response::HTTP_FORBIDDEN, ['X-Fuzzrake-Debug' => 'reCAPTCHA validation failed']);
        }
    }

    #[Route(path: '/api/artisans.json', name: RouteName::API_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function creators(): JsonResponse
    {
        $result = $this->dataService->getCreatorsPublicDataJsonString();

        return new JsonResponse($result, json: true);
    }
}
