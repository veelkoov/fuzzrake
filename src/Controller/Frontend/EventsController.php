<?php

namespace App\Controller\Frontend;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    /**
     * @Route("/events.html", name="events")
     *
     * @param EventRepository $eventRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(EventRepository $eventRepository)
    {
        return $this->render('frontend/events/events.html.twig', [
            'events' => $eventRepository->findBy([], ['timestamp' => 'DESC']),
            // TODO: git log --format='%aI %s'
        ]);
    }
}
