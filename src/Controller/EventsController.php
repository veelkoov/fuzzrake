<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Utils\DateTime\DateTimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    /**
     * @Route("/events.html", name="events")
     * @Cache(maxage=3600, public=true)
     *
     * @return Response
     *
     * @throws DateTimeException
     */
    public function events(EventRepository $eventRepository)
    {
        return $this->render('events/events.html.twig', [
            'events' => $eventRepository->getRecent(),
        ]);
    }
}
