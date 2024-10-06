<?php

declare(strict_types=1);

namespace App\Controller;

use App\Filtering\DataRequests\Pagination;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Service\Cache as CacheService;
use App\Service\Captcha;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class RestApiController extends AbstractController
{
    public function __construct(
        private readonly Captcha $captcha,
        private readonly CacheService $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorRepository $creatorRepository,
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
    public function creators(): JsonResponse // TODO: First fetch takes ages. Force precompute, in any way.
    {
        $result = $this->cache->get($this->getCreatorsPublicDataJsonString(...), CacheTags::ARTISANS, __METHOD__);

        return new JsonResponse($result, json: true);
    }

    /**
     * @throws JsonException
     */
    private function getCreatorsPublicDataJsonString(): string
    {
        $first = 0;
        $total = 1; // Temporary false value to start the loop

        $result = '[';
        $empty = true;

        while ($first < $total) {
            $creatorsPage = $this->creatorRepository->getPaginated($first, Pagination::PAGE_SIZE);

            $total = $creatorsPage->count();
            $first += Pagination::PAGE_SIZE;

            foreach ($creatorsPage as $creator) {
                if ($empty) {
                    $empty = false;
                } else {
                    $result .= ',';
                }

                $result .= Json::encode(Creator::wrap($creator));
            }

            $this->entityManager->clear();
        }

        $result .= ']';

        return $result;
    }
}
