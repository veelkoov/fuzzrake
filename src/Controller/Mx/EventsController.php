<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Entity\Event;
use App\Form\EventType;
use App\Service\EnvironmentsService;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/events')]
class EventsController extends AbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly EnvironmentsService $environments,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    #[Route(path: '/{id}/edit', name: RouteName::MX_EVENT_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_EVENT_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?Event $event): Response
    {
        $event ??= new Event();

        if (!$this->environments->isDevOrTest() || !$event->isEditable()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $this->success($event, $form)) {
            $this->manager->flush();

            return $this->redirectToRoute(RouteName::EVENTS);
        }

        return $this->renderForm('mx/events/edit.html.twig', [
            'event'   => $event,
            'form'    => $form,
        ]);
    }

    private function success(Event $event, FormInterface $form): bool
    {
        if (null !== $event->getId() && self::clicked($form, EventType::BTN_DELETE)) {
            $this->manager->remove($event);

            return true;
        }

        if ($form->isValid()) {
            $this->manager->persist($event);

            return true;
        }

        return false;
    }
}
