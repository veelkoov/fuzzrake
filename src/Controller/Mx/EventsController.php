<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\EnvironmentsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mx/events")
 */
class EventsController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="mx_event_edit", methods={"GET", "POST"})
     * @Route("/new", name="mx_event_new", methods={"GET", "POST"})
     * @Cache(maxage=0, public=false)
     */
    public function edit(Request $request, ?Event $event, EnvironmentsService $environments): Response
    {
        $event ??= new Event();

        if (!$environments->isDevMachine() || !$event->isEditable()) {
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

            return $this->redirectToRoute('events');
        }

        return $this->render('mx/events/edit.html.twig', [
            'event'   => $event,
            'form'    => $form->createView(),
        ]);
    }
}
