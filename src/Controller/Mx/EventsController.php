<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Entity\Event;
use App\Form\Mx\AbstractTypeWithDelete;
use App\Form\Mx\EventType;
use App\Service\EnvironmentsService;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/events')]
class EventsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly EntityManagerInterface $manager,
        EnvironmentsService $environments,
    ) {
        parent::__construct($environments);
    }

    #[Route(path: '/{id}/edit', name: RouteName::MX_EVENT_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_EVENT_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?Event $event): Response
    {
        $event ??= new Event();

        $this->authorize($event->isEditable());

        $form = $this->createForm(EventType::class, $event, [
            AbstractTypeWithDelete::OPT_DELETABLE => null !== $event->getId(),
        ]);
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
