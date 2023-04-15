<?php

declare(strict_types=1);

namespace App\Controller;

use App\Filtering\DataRequests\FilteredDataProvider;
use App\Filtering\DataRequests\RequestParser;
use App\Service\Captcha;
use App\Service\DataService;
use App\ValueObject\Routing\RouteName;
use Psl\Type\Exception\CoercionException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

class RestApiController extends AbstractController
{
    public function __construct(
        private readonly Captcha $captcha,
        private readonly LoggerInterface $logger,
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
    public function contactEmailHtml(Request $request, string $contactEmail): Response
    {
        $ok = $this->captcha->isValid($request, 'info_emailHtml');

        if ($ok) {
            $contactEmail = htmlspecialchars($contactEmail);

            return new Response("<a href=\"mailto:$contactEmail\" class=\"btn btn-primary my-1 btn-sm\"><i class=\"fas fa-envelope\"></i> $contactEmail</a>");
        } else {
            return new Response('', Response::HTTP_FORBIDDEN, ['X-Fuzzrake-Debug' => 'reCAPTCHA validation failed']);
        }
    }

    #[Route(path: '/api/artisans.json', name: RouteName::API_ARTISANS)]
    #[Cache(maxage: 3600, public: true)]
    public function artisans(DataService $dataService): JsonResponse
    {
        return new JsonResponse($dataService->getAllArtisans());
    }

    #[Route(path: '/api/artisans-array.json', name: RouteName::API_ARTISANS_ARRAY)]
    #[Cache(maxage: 3600, public: true)]
    public function artisansArray(Request $request, FilteredDataProvider $Filtered, RequestParser $requestParser): JsonResponse
    {
        try {
            $choices = $requestParser->getChoices($request);
            $result = $Filtered->getPublicDataFor($choices);

            return new JsonResponse($result);
        } catch (CoercionException $exception) {
            $this->logger->info('Invalid API request received', ['exception' => $exception]);

            return throw new BadRequestException();
        }
    }
}
