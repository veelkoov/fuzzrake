<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Utils\Arrays;
use App\Utils\DateTime\DateTimeException;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    /**
     * @throws DateTimeException
     */
    #[Route(path: '/events.html', name: RouteName::EVENTS)]
    #[Cache(maxage: 3600, public: true)]
    public function events(EventRepository $eventRepository): Response
    {
        return $this->render('events/events.html.twig', [
            'events' => $eventRepository->getRecent(),
            'feeds'  => [
                'All updates'              => $this->generateUrl(RouteName::EVENTS_ATOM),
                'Status updates'           => $this->generateUrl(RouteName::EVENTS_ATOM, ['types' => Event::TYPE_CS_UPDATED]),
                'Generic and data updates' => $this->generateUrl(RouteName::EVENTS_ATOM, ['types' => Event::TYPE_DATA_UPDATED.','.Event::TYPE_GENERIC]),
            ],
        ]);
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/events-atom.xml', name: RouteName::EVENTS_ATOM)]
    #[Cache(maxage: 3600, public: true)]
    public function events_atom(Request $request, EventRepository $eventRepository): Response
    {
        $types = Arrays::intersect([
            Event::TYPE_DATA_UPDATED,
            Event::TYPE_GENERIC,
            Event::TYPE_CS_UPDATED,
        ], explode(',', $request->get('types', '')));

        $result = new Response($this->renderView('events/events-atom.xml.twig', [
            'events' => $eventRepository->getRecent($types),
        ]));

        $result->headers->set('Content-Type', 'application/atom+xml; charset=UTF-8');

        return $result;
    }
}
