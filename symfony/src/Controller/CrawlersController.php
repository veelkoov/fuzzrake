<?php

declare(strict_types=1);

namespace App\Controller;

use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CrawlersController extends AbstractController
{
    #[Route(path: '/sitemap.txt', name: RouteName::SITEMAP)]
    #[Cache(maxage: 21600, public: true)]
    public function sitemap(): Response
    {
        $urls = array_map(fn (string $route): string => $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL), [
            RouteName::CONTACT,
            RouteName::DONATE,
            RouteName::EVENTS,
            RouteName::INFO,
            RouteName::MAIN,
            RouteName::CREATOR_IDS,
            RouteName::NEW_CREATORS,
            RouteName::GUIDELINES,
            RouteName::SHOULD_KNOW,
            RouteName::STATISTICS,
            RouteName::TRACKING,
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
