<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Utils\CreatorByCreatorIdTrait;
use App\Repository\CreatorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class CreatorController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    public function __construct(
        private readonly CreatorRepository $creatorRepository,
    ) {
    }

    #[Route(path: '/c/{creatorId}', name: 'rt_creator')] // grep-code-creator-card-path
    #[Cache(maxage: 900, public: true)]
    public function creator(string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrThrow404($creatorId);

        return $this->render('main/creator.html.twig', [
            'creator' => $creator,
            'searched_creator_id' => '',
        ]);
    }
}
