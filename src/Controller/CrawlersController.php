<?php

declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CrawlersController extends AbstractController
{
    /**
     * @Route("/sitemap.txt", name="sitemap")
     * @Cache(maxage=21600, public=true)
     */
    public function sitemap(): Response
    {
        $urls = array_map(function (string $route): string {
            return $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }, [
            'main',
            'data_updates',
            'info',
            'tracking',
            'maker_ids',
            'statistics',
            'events',
            'whoopsies',
            'donate',
        ]);

        return $this->render('crawlers/sitemap.txt.twig', [
            'urls' => $urls,
        ], self::getTextResponse());
    }

    private static function getTextResponse(): Response
    {
        return new Response('', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
