<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    /**
     * @Route("/events.html", name="events")
     *
     * @param EventRepository $eventRepository
     *
     * @return Response
     */
    public function events(EventRepository $eventRepository)
    {
        return $this->render('events/events.html.twig', [
            'events' => $eventRepository->findBy([], ['timestamp' => 'DESC']),
        ]);
    }
}
