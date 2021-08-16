<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Utils\DateTime\DateTimeException;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        ]);
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/events-atom.xml', name: RouteName::EVENTS_ATOM)]
    #[Cache(maxage: 3600, public: true)]
    public function events_atom(EventRepository $eventRepository): Response
    {
        $result = new Response($this->renderView('events/events-atom.xml.twig', [
            'events' => $eventRepository->getRecent(),
        ]));

        $result->headers->set('Content-Type', 'application/atom+xml; charset=UTF-8');

        return $result;
    }
}
