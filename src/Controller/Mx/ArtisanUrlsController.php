<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Repository\ArtisanUrlRepository;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\FieldsDefinitions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/artisan_urls")
 */
class ArtisanUrlsController extends AbstractController
{
    /**
     * @Route("/", name="mx_artisan_urls")
     * @Cache(maxage=0, public=false)
     */
    public function index(ArtisanUrlRepository $repository, EnvironmentsService $environments): Response
    {
        if (!$environments->isDevMachine()) {
            throw $this->createAccessDeniedException();
        }

        $urls = $repository->getOrderedBySuccessDate(FieldsDefinitions::NON_INSPECTED_URLS);

        return $this->render('mx/artisan_urls/index.html.twig', [
            'urls' => $urls,
        ]);
    }
}
