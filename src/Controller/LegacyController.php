<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegacyController extends AbstractController
{
    #[Route(path: '/iu_form/{creatorId}')]
    #[Route(path: '/iu_form/start/{creatorId}')]
    #[Route(path: '/iu_form/data/{creatorId}')]
    #[Route(path: '/iu_form/fill/{creatorId}')]
    public function iuForm(?string $creatorId): Response
    {
        return $this->render('legacy/iu_form.html.twig')
            ->setStatusCode(404);
    }
}
