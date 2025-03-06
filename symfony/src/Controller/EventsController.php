<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Utils\Collections\StringList;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class EventsController extends AbstractController
{
    /**
     * @throws DateTimeException
     */
    #[Route(path: '/events', name: RouteName::EVENTS)]
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
        $types = $this->getChosenEventTypes($request);

        $fourDaysAgo = UtcClock::at('-4 days'); // Workaround for FSR bot; https://github.com/veelkoov/fuzzrake/issues/126

        $result = new Response($this->renderView('events/events-atom.xml.twig', [
            'events' => array_filter($eventRepository->getRecent($types), fn ($event) => $event->getTimestamp() > $fourDaysAgo),
        ]));

        $result->headers->set('Content-Type', 'application/atom+xml; charset=UTF-8');

        return $result;
    }

    private function getChosenEventTypes(Request $request): StringList
    {
        $requestedTypes = StringList::split(',', (string) $request->query->get('types', ''));

        return $requestedTypes->intersect([
            Event::TYPE_DATA_UPDATED,
            Event::TYPE_GENERIC,
            Event::TYPE_CS_UPDATED,
        ]);
    }
}
