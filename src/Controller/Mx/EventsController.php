<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\EnvironmentsService;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/events')]
class EventsController extends AbstractController
{
    #[Route(path: '/{id}/edit', name: RouteName::MX_EVENT_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_EVENT_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?Event $event, EnvironmentsService $environments): Response
    {
        $event ??= new Event();

        if (!$environments->isDevOrTest() || !$event->isEditable()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $event->getId() && $form->get(EventType::BTN_DELETE)->isClicked()) {
                $this->getDoctrine()->getManager()->remove($event);
            } else {
                $this->getDoctrine()->getManager()->persist($event);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(RouteName::EVENTS);
        }

        return $this->render('mx/events/edit.html.twig', [
            'event'   => $event,
            'form'    => $form->createView(),
        ]);
    }
}
