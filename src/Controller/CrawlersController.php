<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CrawlersController extends AbstractController
{
    #[Route(path: '/sitemap.txt', name: 'rt_sitemap')]
    #[Cache(maxage: 3600, public: true)]
    public function sitemap(): Response
    {
        $urls = arr_map([
            'rt_contact',
            'rt_creator_ids',
            'rt_donate',
            'rt_events',
            'rt_guidelines',
            'rt_info',
            'rt_main',
            'rt_new_creators',
            'rt_should_know',
            'rt_statistics',
            'rt_tracking',
        ], fn (string $route): string => $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->render('crawlers/sitemap.txt.twig', [
            'urls' => $urls,
        ], self::getTextResponse());
    }

    private static function getTextResponse(): Response
    {
        return new Response('', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
