<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/creator_urls')]
class CreatorUrlsController extends FuzzrakeAbstractController
{
    #[Route('/{creatorId}', name: RouteName::MX_CREATOR_URLS)]
    #[Cache(maxage: 0, public: false)]
    public function index(string $creatorId): Response
    {
        $this->authorize();

        $creator = $this->getCreatorOrThrow404($creatorId);

        return $this->render('mx/creator_urls.html.twig', [
            'creator' => $creator,
        ]);
    }
}
