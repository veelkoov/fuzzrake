<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Repository\MakerIdRepository;
use App\Service\Captcha;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestApiController extends AbstractController
{
    public function __construct(
        private readonly Captcha $captcha,
    ) {
    }

    #[Route(path: '/api/', name: RouteName::API)]
    #[Cache(maxage: 3600, public: true)]
    public function api(): Response
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/info/email.part.html')]
    #[Cache(maxage: 0, public: false)]
    public function info_emailHtml(Request $request, string $contactEmail): Response
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
    public function artisans(ArtisanRepository $artisanRepository): JsonResponse
    {
        return new JsonResponse(Artisan::wrapAll($artisanRepository->getAll()));
    }

    #[Route(path: '/api/old_to_new_maker_ids_map.json', name: RouteName::API_OLD_TO_NEW_MAKER_IDS_MAP)]
    #[Cache(maxage: 3600, public: true)]
    public function oldToNewMakerIdsMap(MakerIdRepository $makerIdRepository): JsonResponse
    {
        return new JsonResponse($makerIdRepository->getOldToNewMakerIdsMap());
    }
}
